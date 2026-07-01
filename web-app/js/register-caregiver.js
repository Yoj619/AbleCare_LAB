"use strict";
// ============================================================
//  AbleCare – Caregiver Registration Wizard
//  Compiled to ../js/register-caregiver.js
// ============================================================
(function init() {
    const TOTAL_STEPS = 4;
    const NASUGBU_LAT = 14.0667;
    const NASUGBU_LNG = 120.6333;
    const DEFAULT_ZOOM = 14;
    let currentStep = 1;
    let pinnedLat = null;
    let pinnedLng = null;
    let leafletMap = null;
    let leafletMarker = null;
    let mapInitialized = false;
    // ── DOM refs ──────────────────────────────────────────────────────────────
    const backBtn = document.getElementById('backBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressFill = document.getElementById('stepProgressFill');
    const errorBanner = document.getElementById('formErrorBanner');
    const coordsDisplay = document.getElementById('coordsDisplay');
    const loginRow = document.getElementById('loginRow');
    const form = document.getElementById('caregiverForm');
    function inputById(id) {
        return document.getElementById(id);
    }
    function selectById(id) {
        return document.getElementById(id);
    }
    // ── Error helpers ─────────────────────────────────────────────────────────
    function setError(field, msg) {
        const el = document.querySelector(`[data-error-for="${field}"]`);
        if (el)
            el.textContent = msg;
    }
    function clearErrors(step) {
        const section = document.querySelector(`.wizard-step[data-step="${step}"]`);
        if (!section)
            return;
        section.querySelectorAll('[data-error-for]').forEach(el => { el.textContent = ''; });
    }
    function escapeHtml(v) {
        const d = document.createElement('div');
        d.textContent = v;
        return d.innerHTML;
    }
    function showBannerErrors(errors) {
        errorBanner.innerHTML = '<ul>' + errors.map(e => `<li>${escapeHtml(e)}</li>`).join('') + '</ul>';
        errorBanner.style.display = '';
    }
    function clearBanner() {
        errorBanner.style.display = 'none';
        errorBanner.innerHTML = '';
    }
    // ── Map ───────────────────────────────────────────────────────────────────
    function updateCoordsDisplay() {
        if (pinnedLat !== null && pinnedLng !== null) {
            coordsDisplay.textContent =
                `\u{1F4CD} Selected Location: ${pinnedLat.toFixed(4)}, ${pinnedLng.toFixed(4)}`;
            coordsDisplay.style.display = '';
        }
    }
    function placePin(lat, lng) {
        pinnedLat = lat;
        pinnedLng = lng;
        if (leafletMarker === null) {
            leafletMarker = L.marker([lat, lng], { draggable: true }).addTo(leafletMap);
            leafletMarker.on('dragend', () => {
                const pos = leafletMarker.getLatLng();
                pinnedLat = pos.lat;
                pinnedLng = pos.lng;
                updateCoordsDisplay();
            });
        }
        else {
            leafletMarker.setLatLng([lat, lng]);
        }
        updateCoordsDisplay();
    }
    function initMap() {
        if (mapInitialized) {
            leafletMap.invalidateSize();
            return;
        }
        mapInitialized = true;
        leafletMap = L.map('leafletMap').setView([NASUGBU_LAT, NASUGBU_LNG], DEFAULT_ZOOM);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(leafletMap);
        leafletMap.on('click', (e) => {
            placePin(e.latlng.lat, e.latlng.lng);
        });
    }
    // ── Step navigation ───────────────────────────────────────────────────────
    function showStep(step) {
        document.querySelectorAll('.wizard-step').forEach(s => {
            s.hidden = s.dataset.step !== String(step);
        });
        const nav = document.getElementById('wizardNav');
        if (step === 'success') {
            nav.style.display = 'none';
            loginRow.style.display = 'none';
            return;
        }
        document.querySelectorAll('[data-step-dot]').forEach(dot => {
            const d = dot;
            const n = Number(d.dataset.stepDot);
            d.classList.remove('active', 'done');
            if (n < step)
                d.classList.add('done');
            if (n === step)
                d.classList.add('active');
        });
        progressFill.style.width = `${((step - 1) / (TOTAL_STEPS - 1)) * 100}%`;
        backBtn.disabled = step === 1;
        nextBtn.hidden = step === TOTAL_STEPS;
        submitBtn.hidden = step !== TOTAL_STEPS;
        if (step === 3) {
            // defer so the browser paints the visible container before Leaflet reads its size
            requestAnimationFrame(initMap);
        }
        if (step === TOTAL_STEPS)
            renderReview();
    }
    // ── Review (step 4) ───────────────────────────────────────────────────────
    function renderReview() {
        const rev = document.getElementById('reviewSummary');
        function row(label, value) {
            return `<div class="review-row">` +
                `<span class="review-label">${escapeHtml(label)}</span>` +
                `<span class="review-value">${escapeHtml(value || '—')}</span>` +
                `</div>`;
        }
        const loc = (pinnedLat !== null && pinnedLng !== null)
            ? `${pinnedLat.toFixed(4)}, ${pinnedLng.toFixed(4)}`
            : '—';
        rev.innerHTML = `
            <div class="review-section">
                <h3>Account Information</h3>
                ${row('Full Name', inputById('fullName').value.trim())}
                ${row('Email', inputById('email').value.trim())}
                ${row('Phone', inputById('phone').value.trim())}
            </div>
            <div class="review-section">
                <h3>Address</h3>
                ${row('Address', inputById('address').value.trim())}
                ${row('Barangay', selectById('barangay').value)}
            </div>
            <div class="review-section">
                <h3>Pinned Location</h3>
                ${row('Coordinates', loc)}
            </div>`;
    }
    // ── Validation ────────────────────────────────────────────────────────────
    function validateStep(step) {
        clearErrors(step);
        let valid = true;
        function fail(field, msg) {
            setError(field, msg);
            valid = false;
        }
        if (step === 1) {
            const name = inputById('fullName').value.trim();
            const email = inputById('email').value.trim();
            const pass = inputById('password').value;
            const conf = inputById('confirmPassword').value;
            if (name === '')
                fail('fullName', 'Full name is required.');
            if (email === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
                fail('email', 'A valid email address is required.');
            if (pass.length < 8)
                fail('password', 'Password must be at least 8 characters.');
            if (pass !== conf)
                fail('confirmPassword', 'Passwords do not match.');
        }
        if (step === 2) {
            if (inputById('address').value.trim() === '')
                fail('address', 'Address is required.');
            if (selectById('barangay').value === '')
                fail('barangay', 'Please select a barangay.');
        }
        if (step === 3) {
            if (pinnedLat === null || pinnedLng === null)
                fail('mapPin', 'Please pin your exact location on the map to continue.');
        }
        if (step === 4) {
            if (!inputById('confirmAccurate').checked)
                fail('confirmAccurate', 'Please confirm that all information is accurate.');
        }
        return valid;
    }
    // ── Submit ────────────────────────────────────────────────────────────────
    async function submitForm() {
        clearBanner();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting…';
        const payload = {
            name: inputById('fullName').value.trim(),
            email: inputById('email').value.trim(),
            password: inputById('password').value,
            phone: inputById('phone').value.trim(),
            address: inputById('address').value.trim(),
            barangay: selectById('barangay').value,
            latitude: pinnedLat,
            longitude: pinnedLng,
        };
        try {
            const res = await fetch('../backend/api/auth/register-caregiver.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (json.error !== null) {
                showBannerErrors([json.error]);
            }
            else {
                showStep('success');
            }
        }
        catch {
            showBannerErrors([
                'Unable to reach the server. Please check your connection and try again.',
            ]);
        }
        finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';
        }
    }
    // ── Wire up ───────────────────────────────────────────────────────────────
    nextBtn.addEventListener('click', () => {
        if (!validateStep(currentStep))
            return;
        currentStep = Math.min(currentStep + 1, TOTAL_STEPS);
        showStep(currentStep);
    });
    backBtn.addEventListener('click', () => {
        currentStep = Math.max(currentStep - 1, 1);
        showStep(currentStep);
    });
    submitBtn.addEventListener('click', () => {
        if (!validateStep(TOTAL_STEPS))
            return;
        void submitForm();
    });
    form.addEventListener('submit', (e) => e.preventDefault());
    showStep(1);
})();
