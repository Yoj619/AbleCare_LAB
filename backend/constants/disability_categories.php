<?php
// Disability categories used across provider specializations and patient records.
// Keys match the ENUM values used by clinic_specializations / patients tables.

declare(strict_types=1);

return [
    'physical'        => 'Physical',
    'sensory_visual'  => 'Sensory - Visual',
    'sensory_hearing' => 'Sensory - Hearing',
    'cognitive'       => 'Cognitive',
];
