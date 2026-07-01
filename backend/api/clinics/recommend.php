<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

// ============================================================
//  Clinic / Provider recommendation scoring
//
//  Score = (Specialization Match × 50%)
//        + (Distance Score        × 30%)
//        + (Accessibility Score   × 15%)
//        + (Availability Score    ×  5%)
//
//  NOTE: there is no real-time scheduling/availability data model
//  in this schema today, so Availability Score is a flat 100 for
//  every provider. Distance uses the Haversine formula against the
//  provider's clinic lat/lng — providers whose clinic has no
//  geocoded coordinates yet receive a Distance Score of 0.
// ============================================================

function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $earthRadiusKm = 6371.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadiusKm * $c;
}

$db        = get_db();
$caregiver = require_caregiver($db);

$body = get_json_body();
require_fields($body, ['patient_id', 'latitude', 'longitude']);

$patientId    = (int) $body['patient_id'];
$caregiverLat = (float) $body['latitude'];
$caregiverLng = (float) $body['longitude'];

// ── Verify patient ownership and load matching criteria ──────────────────────
$stmt = $db->prepare(
    'SELECT disability_category, specific_condition FROM patients
     WHERE id = ? AND caregiver_id = ? AND deleted_at IS NULL LIMIT 1'
);
$stmt->bind_param('ii', $patientId, $caregiver['caregiver_id']);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    json_error(404, 'Patient not found.');
}

$patientCategory  = $patient['disability_category'];
$patientCondition = $patient['specific_condition'] !== null ? strtolower((string) $patient['specific_condition']) : null;

// ── Load approved, clinic-linked providers ────────────────────────────────────
$stmt = $db->prepare(
    "SELECT hp.id AS provider_id, hp.license_number, hp.clinic_id,
            u.first_name, u.last_name,
            c.id AS clinic_table_id, c.name AS clinic_name, c.address, c.barangay,
            c.latitude, c.longitude, c.operating_hours,
            c.accepts_walk_ins, c.has_wheelchair_access, c.has_ground_floor_access
     FROM healthcare_providers hp
     JOIN users u   ON u.id = hp.user_id AND u.role = 'healthcare_provider' AND u.status = 'approved'
     LEFT JOIN clinics c ON c.id = hp.clinic_id"
);
$stmt->execute();
$result = $stmt->get_result();

$providers = [];
while ($row = $result->fetch_assoc()) {
    $providers[] = $row;
}
$stmt->close();

if (empty($providers)) {
    json_success([]);
}

// ── Load specializations for the involved clinics in one query ───────────────
$clinicIds = array_values(array_unique(array_filter(array_map(
    fn ($p) => $p['clinic_table_id'] !== null ? (int) $p['clinic_table_id'] : null,
    $providers
))));

$specializationsByClinic = [];
if (!empty($clinicIds)) {
    $placeholders = implode(',', array_fill(0, count($clinicIds), '?'));
    $types        = str_repeat('i', count($clinicIds));
    $stmt = $db->prepare(
        "SELECT clinic_id, disability_category, specific_condition FROM clinic_specializations
         WHERE clinic_id IN ($placeholders)"
    );
    $stmt->bind_param($types, ...$clinicIds);
    $stmt->execute();
    $specResult = $stmt->get_result();
    while ($row = $specResult->fetch_assoc()) {
        $specializationsByClinic[(int) $row['clinic_id']][] = $row;
    }
    $stmt->close();
}

// ── Score each provider ───────────────────────────────────────────────────────
$scored = [];
foreach ($providers as $p) {
    $clinicId = $p['clinic_table_id'] !== null ? (int) $p['clinic_table_id'] : null;
    $specs    = $clinicId !== null ? ($specializationsByClinic[$clinicId] ?? []) : [];

    // Specialization match (0-100)
    $specializationScore = 0.0;
    foreach ($specs as $spec) {
        if ($patientCategory !== null && $spec['disability_category'] === $patientCategory) {
            $conditionMatches = $patientCondition !== null
                && $spec['specific_condition'] !== null
                && strtolower((string) $spec['specific_condition']) === $patientCondition;
            $specializationScore = max($specializationScore, $conditionMatches ? 100.0 : 60.0);
        }
    }

    // Distance score (0-100) — Haversine, decays linearly, 0 past 50km or unknown coords
    $distanceKm = null;
    $distanceScore = 0.0;
    if ($p['latitude'] !== null && $p['longitude'] !== null) {
        $distanceKm = haversineKm($caregiverLat, $caregiverLng, (float) $p['latitude'], (float) $p['longitude']);
        $distanceScore = max(0.0, 100.0 - ($distanceKm * 2.0));
    }

    // Accessibility score (0-100) — share of accessibility features present
    $accessibilityFlags = [
        (bool) ($p['accepts_walk_ins'] ?? false),
        (bool) ($p['has_wheelchair_access'] ?? false),
        (bool) ($p['has_ground_floor_access'] ?? false),
    ];
    $accessibilityScore = (array_sum($accessibilityFlags) / count($accessibilityFlags)) * 100.0;

    // Availability score — flat placeholder (no live scheduling data source)
    $availabilityScore = 100.0;

    $totalScore =
        ($specializationScore * 0.50) +
        ($distanceScore * 0.30) +
        ($accessibilityScore * 0.15) +
        ($availabilityScore * 0.05);

    $scored[] = [
        'providerId'    => (int) $p['provider_id'],
        'providerName'  => trim($p['first_name'] . ' ' . $p['last_name']),
        'licenseNumber' => $p['license_number'],
        'clinicId'      => $clinicId,
        'clinicName'    => $p['clinic_name'],
        'address'       => $p['address'],
        'barangay'      => $p['barangay'],
        'operatingHours' => $p['operating_hours'],
        'acceptsWalkIns' => (bool) ($p['accepts_walk_ins'] ?? false),
        'wheelchairAccessible' => (bool) ($p['has_wheelchair_access'] ?? false),
        'groundFloorAccess'    => (bool) ($p['has_ground_floor_access'] ?? false),
        'distanceKm'    => clean_float($distanceKm),
        'score'         => clean_float($totalScore),
        'scoreBreakdown' => [
            'specialization' => clean_float($specializationScore),
            'distance'       => clean_float($distanceScore),
            'accessibility'  => clean_float($accessibilityScore),
            'availability'   => clean_float($availabilityScore),
        ],
    ];
}

usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
$top5 = array_slice($scored, 0, 5);

// ── Log recommendations for audit trail (clinic_recommendations table) ───────
$logStmt = $db->prepare(
    'INSERT INTO clinic_recommendations (patient_id, clinic_id, score) VALUES (?, ?, ?)'
);
foreach ($top5 as $entry) {
    if ($entry['clinicId'] === null) continue;
    $logStmt->bind_param('iid', $patientId, $entry['clinicId'], $entry['score']);
    $logStmt->execute();
}
$logStmt->close();

json_success($top5);
