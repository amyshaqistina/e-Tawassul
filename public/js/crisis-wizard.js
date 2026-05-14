/* ==========================================================================
   Crisis Report Wizard — final version
   - 3 steps with toast notifications
   - Geolocation + Leaflet map preview
   - Contextual helpers + live disaster news
   - "Use this location" extraction from news
   - Suggested documents per sub-category
   - Image thumbnails in file list AND review summary
   - Lightbox image preview
   - Submit button only on step 3
   ========================================================================== */

(function () {
    'use strict';

    let currentStep = 1;
    const totalSteps = 3;
    const state = {
        crisis_type: '',
        sub_category: '',
        sub_category_label: '',
        files: []          // each file gets { file, dataUrl (if image), id }
    };

    const TYPE_LABELS = {
        medical: 'Medical Emergency',
        accident: 'Accident',
        natural_disaster: 'Natural Disaster',
        death: 'Death / Bereavement'
    };

    const HELPER_MAP = {
        'mental_health':      'helper-mental',
        'death_friend':       'helper-friend',
        'road_accident':      'helper-road',
        'flood':              'helper-disaster',
        'landslide':          'helper-disaster',
        'fire':               'helper-disaster',
        'storm':              'helper-disaster',
        'haze':               'helper-disaster',
        'earthquake':         'helper-disaster',
        'strong_wind':        'helper-disaster',
        'house_fire':         'helper-disaster'
    };

    const DISASTER_API_TYPE = {
        flood: 'flood', landslide: 'landslide', fire: 'fire',
        storm: 'storm', haze: 'haze', earthquake: 'earthquake',
        strong_wind: 'storm', house_fire: 'fire'
    };

    /* ===== Suggested documents per sub-category ===== */
    const SUGGESTED_DOCS = {
        sudden_illness:        'Medical Certificate (MC), hospital admission slip, doctor\'s letter',
        mental_health:         'Counsellor referral letter, psychiatric assessment (if available)',
        hospitalization:       'Hospital admission slip, discharge summary, bill receipts',
        surgery_required:      'Hospital admission slip, surgery confirmation letter',
        chronic_flare:         'MC, doctor\'s letter, prescription, recent hospital visit slip',
        family_critical_illness:'Hospital letter, doctor\'s note about family member',

        road_accident:         'Police report (laporan polis), photos of scene, insurance claim, hospital report',
        lab_workshop:          'Incident report from lab/faculty, photos, MC',
        sports_injury:         'MC, hospital/clinic report, photos of injury',
        fall_fracture:         'Hospital report, MC, X-ray report, photos',
        burn_electrical:       'Hospital report, photos of injury or damage',
        house_fire:            'Bomba (fire dept) report, photos of damage, police report',
        drowning:              'Hospital report, witness statement, police report',

        flood:                 'Photos of flooded area, JKM PPS registration, news article, video',
        landslide:             'Photos of affected area, JKM report, news article',
        fire:                  'Bomba report, photos of damage, news article',
        storm:                 'Photos of damage, MET warning screenshot, news article',
        haze:                  'API reading screenshot, hospital visit slip (if respiratory issue), news article',
        earthquake:            'Photos of damage, news article, MET report',
        strong_wind:           'Photos of damage, news article, MET warning',

        death_parent:          'Death certificate (Sijil Kematian JPN), funeral notice, family confirmation letter — can be added later',
        death_sibling:         'Death certificate or family confirmation letter — can be added later',
        death_grandparent:     'Death certificate or family confirmation letter — can be added later',
        death_relative:        'Death certificate or family confirmation letter — can be added later',
        death_guardian:        'Death certificate or guardian confirmation letter — can be added later',
        death_spouse:          'Death certificate, marriage certificate — can be added later',
        death_friend:          'Obituary, funeral notice, or coursemate/dean confirmation — can be added later'
    };

    /* ===== Helpers ===== */
    const $  = (sel, ctx) => (ctx || document).querySelector(sel);
    const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
    const escapeHtml = (s) => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const isImage = (file) => file && /^image\//i.test(file.type);

    /* ===== Toasts ===== */
    function toast(type, title, msg, duration = 4000) {
        const container = $('#toast-container');
        const el = document.createElement('div');
        el.className = `toast toast-${type}`;
        const iconMap = { warning: 'bi-exclamation-triangle-fill', error: 'bi-x-circle-fill', success: 'bi-check-circle-fill', info: 'bi-info-circle-fill' };
        el.innerHTML = `
            <i class="bi ${iconMap[type] || iconMap.info} toast-icon"></i>
            <div class="toast-body">
                <p class="toast-title">${escapeHtml(title)}</p>
                <p class="toast-msg">${escapeHtml(msg)}</p>
            </div>
            <button type="button" class="toast-close">&times;</button>
        `;
        container.appendChild(el);
        const remove = () => { el.classList.add('fade-out'); setTimeout(() => el.remove(), 300); };
        el.querySelector('.toast-close').addEventListener('click', remove);
        setTimeout(remove, duration);
    }

    /* ===== Lightbox for image preview ===== */
    const lightbox = $('#lightbox');
    const lightboxImg = $('#lightbox-img');
    function openLightbox(src) { lightboxImg.src = src; lightbox.classList.add('show'); }
    function closeLightbox() { lightbox.classList.remove('show'); lightboxImg.src = ''; }
    $('#lightbox-close').addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && lightbox.classList.contains('show')) closeLightbox(); });

    /* ===== Step navigation ===== */
    function showStep(step) {
        $$('.step-panel').forEach(p => p.classList.remove('active'));
        $(`.step-panel[data-panel="${step}"]`).classList.add('active');

        $$('.wizard-step').forEach(s => {
            const n = parseInt(s.dataset.step, 10);
            s.classList.remove('active', 'completed');
            if (n < step) s.classList.add('completed');
            else if (n === step) s.classList.add('active');
        });

        // Buttons — use both display style AND hidden attribute to defeat browser caching
        const isLast = step === totalSteps;
        $('#btn-prev').disabled = step === 1;
        const nextBtn = $('#btn-next');
        const submitBtn = $('#btn-submit');
        if (isLast) {
            nextBtn.style.display = 'none';
            nextBtn.setAttribute('hidden', 'hidden');
            submitBtn.style.display = 'inline-flex';
            submitBtn.removeAttribute('hidden');
        } else {
            nextBtn.style.display = 'inline-flex';
            nextBtn.removeAttribute('hidden');
            submitBtn.style.display = 'none';
            submitBtn.setAttribute('hidden', 'hidden');
        }
        $('#current-step').textContent = step;

        if (step === 2) updateSuggestedDocs();
        if (step === totalSteps) buildReviewSummary();

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStep(step) {
        if (step === 1) {
            if (!state.crisis_type) { toast('warning', 'Crisis type required', 'Please select a crisis type to continue.'); return false; }
            if (!state.sub_category) { toast('warning', 'Sub-category required', 'Please select what best describes your situation.'); return false; }
        }
        if (step === 2) {
            const loc = $('#location').value.trim();
            const desc = $('#crisis_description').value.trim();
            if (!loc) { toast('warning', 'Location required', 'Please enter where the incident happened.'); $('#location').focus(); return false; }
            if (!desc || desc.length < 10) { toast('warning', 'Description too short', 'Please provide at least 10 characters describing what happened.'); $('#crisis_description').focus(); return false; }
        }
        if (step === 3) {
            if (!$('#consent').checked) { toast('warning', 'Consent required', 'Please accept the consent to share information before submitting.'); return false; }
        }
        return true;
    }

    /* ===== Step 1: Type & sub-category ===== */
    $$('.type-card').forEach(card => {
        card.addEventListener('click', () => {
            $$('.type-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            state.crisis_type = card.dataset.type;
            $('#crisis_type').value = state.crisis_type;

            state.sub_category = '';
            state.sub_category_label = '';
            $('#sub_category').value = '';
            $$('.subcat-pill').forEach(p => p.classList.remove('selected'));
            hideAllHelpers();

            $('#subcat-section-wrapper').style.display = 'block';
            $$('.subcat-section').forEach(s => s.classList.toggle('active', s.dataset.parent === state.crisis_type));
        });
    });

    $$('.subcat-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            const section = pill.closest('.subcat-section');
            if (!section.classList.contains('active')) return;

            $$('.subcat-pill').forEach(p => p.classList.remove('selected'));
            pill.classList.add('selected');
            state.sub_category = pill.dataset.value;
            $('#sub_category').value = state.sub_category;

            const labelEl = pill.querySelector('.subcat-pill-label').cloneNode(true);
            const malay = labelEl.querySelector('.malay-name');
            if (malay) malay.remove();
            state.sub_category_label = labelEl.textContent.trim();

            showHelperFor(state.sub_category);
        });
    });

    function hideAllHelpers() { $$('.ctx-helper').forEach(h => h.classList.remove('show')); }
    function showHelperFor(subcat) {
        hideAllHelpers();
        const helperId = HELPER_MAP[subcat];
        if (!helperId) return;
        const helper = $('#' + helperId);
        if (helper) {
            helper.classList.add('show');
            if (helperId === 'helper-disaster') {
                const apiType = DISASTER_API_TYPE[subcat];
                if (apiType) fetchDisasterContext(apiType);
            }
        }
    }

    /* Wire contextual fields */
    const relatedInput = $('#related_person_input');
    if (relatedInput) relatedInput.addEventListener('input', e => $('#related_person').value = e.target.value);
    const policeInput = $('#police_report_input');
    if (policeInput) policeInput.addEventListener('input', e => $('#police_report').value = e.target.value);

    /* ===== Live disaster context fetch ===== */
    async function fetchDisasterContext(type) {
        const container = $('#live-data-container');
        container.innerHTML = '<div class="live-loading"><i class="bi bi-arrow-repeat"></i> Loading live information...</div>';

        try {
            const response = await fetch(`/student/crisis-helpers/disaster-context?type=${encodeURIComponent(type)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Failed to load: ' + response.status);
            const data = await response.json();
            renderDisasterContext(data);
        } catch (e) {
            console.warn('Disaster context fetch failed:', e);
            renderDisasterContext({ warnings: [], news: [], static_links: defaultStaticLinks(type), has_live: false });
        }
    }

    function defaultStaticLinks(type) {
        return [
            { label: 'NADMA Portal',          url: 'https://www.nadma.gov.my/' },
            { label: 'MET Malaysia Warnings', url: 'https://www.met.gov.my/' },
            { label: 'JKM Bantuan Banjir',    url: 'https://bencanaalam.jkm.gov.my/' },
        ];
    }

    function renderDisasterContext(data) {
        const container = $('#live-data-container');
        let html = '';

        if (data.warnings && data.warnings.length > 0) {
            html += '<div class="live-warnings">';
            html += '<div class="live-warnings-title"><i class="bi bi-exclamation-triangle-fill"></i> Active warnings (MET Malaysia)</div>';
            data.warnings.forEach(w => {
                html += `
                    <div class="live-warning-item">
                        <div class="w-title">${escapeHtml(w.title)}</div>
                        ${w.area ? `<div class="w-meta"><i class="bi bi-geo-alt"></i> ${escapeHtml(w.area)}</div>` : ''}
                        ${w.valid_to ? `<div class="w-meta"><i class="bi bi-clock"></i> Valid until ${escapeHtml(w.valid_to)}</div>` : ''}
                    </div>
                `;
            });
            html += '</div>';
        }

        if (data.news && data.news.length > 0) {
            html += '<div class="live-news">';
            html += '<div class="live-news-title"><i class="bi bi-newspaper"></i> Latest news (last 3 days)</div>';
            data.news.forEach(n => {
                const useLocationBtn = n.location
                    ? `<button type="button" class="use-location-btn" data-location="${escapeHtml(n.location)}"><i class="bi bi-geo-alt-fill"></i> Use ${escapeHtml(n.location)}</button>`
                    : '';
                html += `
                    <div class="live-news-item">
                        <i class="bi bi-newspaper" style="color:#1a56db; margin-top:2px;"></i>
                        <div class="news-content">
                            <a href="${escapeHtml(n.link)}" target="_blank" rel="noopener">${escapeHtml(n.title)}</a>
                            <div class="n-meta">
                                <span>${escapeHtml(n.source || 'News')}${n.published ? ' · ' + escapeHtml(n.published) : ''}</span>
                                ${useLocationBtn}
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }

        if (!data.warnings?.length && !data.news?.length) {
            html += '<div class="live-loading"><i class="bi bi-info-circle"></i> No active warnings or news right now. You can still proceed with your report.</div>';
        }

        // Always show static links
        const links = data.static_links || defaultStaticLinks();
        if (links.length) {
            html += '<div class="ctx-helper-action-row">';
            links.forEach(l => {
                html += `<a href="${escapeHtml(l.url)}" target="_blank" rel="noopener" class="ctx-btn"><i class="bi bi-box-arrow-up-right"></i> ${escapeHtml(l.label)}</a>`;
            });
            html += '</div>';
        }

        container.innerHTML = html;

        // Wire "Use this location" buttons
        container.querySelectorAll('.use-location-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const loc = btn.dataset.location;
                $('#location').value = `${loc}, Malaysia`;
                toast('info', 'Searching location...', `Looking up "${loc}" on the map.`);
                // Trigger geocoding so the map updates too
                await searchTypedLocation();
            });
        });
    }

    /* ===== Step 2: Suggested Docs ===== */
    function updateSuggestedDocs() {
        const box = $('#suggested-docs');
        const list = $('#suggested-docs-list');
        const docs = SUGGESTED_DOCS[state.sub_category];
        if (docs) {
            list.textContent = docs;
            box.style.display = 'flex';
        } else {
            box.style.display = 'none';
        }
    }

    /* ===== Step 2: Description char counter ===== */
    const descField = $('#crisis_description');
    const descCount = $('#desc-count');
    descField.addEventListener('input', () => descCount.textContent = descField.value.length);
    descCount.textContent = descField.value.length;

    /* ===== Step 2: Location + map ===== */
    let mapInstance = null, mapMarker = null;
    const mapEl = $('#map-preview');

    function showMap(lat, lng, label) {
        mapEl.classList.add('show');
        if (!mapInstance) {
            mapInstance = L.map('map-preview').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(mapInstance);
        } else {
            mapInstance.setView([lat, lng], 16);
        }
        if (mapMarker) mapMarker.remove();
        mapMarker = L.marker([lat, lng]).addTo(mapInstance);
        if (label) mapMarker.bindPopup(label).openPopup();

        mapInstance.on('click', async (e) => {
            const { lat: clat, lng: clng } = e.latlng;
            $('#latitude').value = clat;
            $('#longitude').value = clng;
            if (mapMarker) mapMarker.remove();
            mapMarker = L.marker([clat, clng]).addTo(mapInstance);
            try {
                const resp = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${clat}&lon=${clng}&zoom=18`, { headers: { 'Accept-Language': 'en' } });
                const data = await resp.json();
                if (data.display_name) {
                    $('#location').value = data.display_name;
                    mapMarker.bindPopup(data.display_name).openPopup();
                }
            } catch {}
        });
        setTimeout(() => mapInstance.invalidateSize(), 100);
    }

    function showLocationStatus(type, msg) {
        const status = $('#location-status');
        status.className = 'location-status show ' + type;
        $('#location-status-msg').textContent = msg;
    }

    $('#detect-location-btn').addEventListener('click', () => {
        if (!navigator.geolocation) { toast('error', 'Not supported', 'Your browser does not support geolocation.'); return; }
        const btn = $('#detect-location-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Detecting...';
        showLocationStatus('loading', 'Requesting your location...');

        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                const { latitude, longitude } = pos.coords;
                $('#latitude').value = latitude;
                $('#longitude').value = longitude;
                showLocationStatus('loading', 'Looking up address...');
                try {
                    const resp = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`, { headers: { 'Accept-Language': 'en' } });
                    const data = await resp.json();
                    const addr = data.display_name || `${latitude}, ${longitude}`;
                    $('#location').value = addr;
                    showLocationStatus('success', 'Current location detected. Click on the map to refine.');
                    showMap(latitude, longitude, addr);
                    toast('success', 'Location detected', 'Using your current GPS location.');
                } catch {
                    $('#location').value = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                    showLocationStatus('success', 'Coordinates captured.');
                    showMap(latitude, longitude);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Detect';
                }
            },
            (err) => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Detect';
                let msg = 'Could not detect your location.';
                if (err.code === 1) msg = 'Permission denied. Please type the location manually.';
                else if (err.code === 2) msg = 'Location unavailable. Try again or type manually.';
                else if (err.code === 3) msg = 'Request timed out. Please try again.';
                showLocationStatus('error', msg);
                toast('error', 'Location not detected', msg);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });

    /* ===== Search button: forward-geocode typed text ===== */
    $('#search-location-btn').addEventListener('click', () => searchTypedLocation());
    $('#location').addEventListener('keydown', (e) => {
        // Press Enter inside the location field to trigger search instead of submitting form
        if (e.key === 'Enter') {
            e.preventDefault();
            searchTypedLocation();
        }
    });

    async function searchTypedLocation() {
        const query = $('#location').value.trim();
        if (!query) {
            toast('warning', 'Type a location first', 'Enter a place name then click Search.');
            $('#location').focus();
            return;
        }

        const btn = $('#search-location-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Searching...';
        showLocationStatus('loading', `Searching for "${query}"...`);

        try {
            // Bias results toward Malaysia by appending if not already mentioned
            const q = /malaysia/i.test(query) ? query : `${query}, Malaysia`;
            const resp = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1&countrycodes=my&addressdetails=1`,
                { headers: { 'Accept-Language': 'en' } }
            );
            const results = await resp.json();
            if (!results || results.length === 0) {
                showLocationStatus('error', `No match found for "${query}". Try a more specific name.`);
                toast('warning', 'No match', `Could not find "${query}". Try adding a state or city.`);
                return;
            }
            const r = results[0];
            const lat = parseFloat(r.lat);
            const lon = parseFloat(r.lon);
            $('#latitude').value = lat;
            $('#longitude').value = lon;
            $('#location').value = r.display_name;
            showLocationStatus('success', 'Location found. Click on the map to refine.');
            showMap(lat, lon, r.display_name);
            toast('success', 'Location found', r.display_name.split(',').slice(0, 2).join(','));
        } catch (e) {
            showLocationStatus('error', 'Search failed. Please try again.');
            toast('error', 'Search failed', 'Could not reach the geocoding service.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search"></i> Search';
        }
    }

    /* ===== Step 2: File upload with thumbnails ===== */
    const fileInput = $('#file-input');
    const fileList = $('#file-list');
    const dropzone = $('#file-dropzone');
    let fileIdCounter = 0;

    function readImageAsDataURL(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    async function addFiles(filesList) {
        const arr = Array.from(filesList);
        for (const f of arr) {
            if (state.files.length >= 5) { toast('warning', 'File limit', 'You can upload a maximum of 5 files.'); break; }
            if (f.size > 5 * 1024 * 1024) { toast('warning', 'File too large', `"${f.name}" exceeds 5MB and was skipped.`); continue; }

            const entry = { id: ++fileIdCounter, file: f, dataUrl: null };
            if (isImage(f)) {
                try { entry.dataUrl = await readImageAsDataURL(f); } catch {}
            }
            state.files.push(entry);
        }
        renderFileList();
    }

    function renderFileList() {
        fileList.innerHTML = '';
        state.files.forEach(entry => {
            const f = entry.file;
            const sizeKB = (f.size / 1024).toFixed(0);
            const sizeStr = sizeKB > 1024 ? (sizeKB / 1024).toFixed(1) + ' MB' : sizeKB + ' KB';
            const div = document.createElement('div');
            div.className = 'file-item';
            const previewHtml = entry.dataUrl
                ? `<img src="${entry.dataUrl}" alt="${escapeHtml(f.name)}" data-fullsrc="${entry.dataUrl}">`
                : `<i class="bi bi-file-earmark-fill file-icon-fallback"></i>`;
            div.innerHTML = `
                <button type="button" class="file-remove" data-id="${entry.id}" aria-label="Remove">
                    <i class="bi bi-x"></i>
                </button>
                <div class="file-item-preview">${previewHtml}</div>
                <div class="file-item-info">
                    <span class="file-name" title="${escapeHtml(f.name)}">${escapeHtml(f.name)}</span>
                    <span class="file-size">${sizeStr}</span>
                </div>
            `;
            fileList.appendChild(div);
        });

        // Sync the file input
        const dt = new DataTransfer();
        state.files.forEach(e => dt.items.add(e.file));
        fileInput.files = dt.files;
    }

    fileInput.addEventListener('change', e => addFiles(e.target.files));
    fileList.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.file-remove');
        if (removeBtn) {
            const id = parseInt(removeBtn.dataset.id, 10);
            state.files = state.files.filter(entry => entry.id !== id);
            renderFileList();
            return;
        }
        const img = e.target.closest('.file-item-preview img');
        if (img && img.dataset.fullsrc) openLightbox(img.dataset.fullsrc);
    });

    ['dragover', 'dragenter'].forEach(evt => dropzone.addEventListener(evt, e => { e.preventDefault(); dropzone.classList.add('dragover'); }));
    ['dragleave', 'dragend', 'drop'].forEach(evt => dropzone.addEventListener(evt, e => { e.preventDefault(); dropzone.classList.remove('dragover'); }));
    dropzone.addEventListener('drop', e => { e.preventDefault(); addFiles(e.dataTransfer.files); });

    /* ===== Step 3: Review summary ===== */
    function buildReviewSummary() {
        $('#review-type').textContent = TYPE_LABELS[state.crisis_type] || '—';
        $('#review-type').classList.toggle('empty', !state.crisis_type);

        $('#review-subcat').textContent = state.sub_category_label || '—';
        $('#review-subcat').classList.toggle('empty', !state.sub_category_label);

        const loc = $('#location').value.trim();
        $('#review-location').textContent = loc || '—';
        $('#review-location').classList.toggle('empty', !loc);

        const d = $('#incident_date').value;
        const t = $('#incident_time').value;
        $('#review-datetime').textContent = d && t ? `${formatDate(d)}, ${t}` : '—';

        const desc = $('#crisis_description').value.trim();
        $('#review-description').textContent = desc || '—';
        $('#review-description').classList.toggle('empty', !desc);

        const actions = $('#immediate_actions').value.trim();
        $('#review-actions').textContent = actions || 'None specified';
        $('#review-actions').classList.toggle('empty', !actions);

        // Files + thumbnails
        const filesText = $('#review-files');
        const thumbsContainer = $('#review-thumbs');
        thumbsContainer.innerHTML = '';

        if (state.files.length === 0) {
            filesText.textContent = 'No files uploaded';
            filesText.classList.add('empty');
        } else {
            filesText.textContent = `${state.files.length} file(s) attached`;
            filesText.classList.remove('empty');

            state.files.forEach(entry => {
                const thumb = document.createElement('div');
                thumb.className = 'review-thumb';
                if (entry.dataUrl) {
                    thumb.innerHTML = `<img src="${entry.dataUrl}" alt="${escapeHtml(entry.file.name)}" data-fullsrc="${entry.dataUrl}">`;
                    thumb.style.cursor = 'pointer';
                    thumb.addEventListener('click', () => openLightbox(entry.dataUrl));
                } else {
                    const ext = (entry.file.name.split('.').pop() || '').toUpperCase();
                    thumb.innerHTML = `<div style="display:flex;flex-direction:column;align-items:center;gap:2px;">
                        <i class="bi bi-file-earmark-fill fb-icon"></i>
                        <span class="review-thumb-label">${escapeHtml(ext)}</span>
                    </div>`;
                }
                thumb.title = entry.file.name;
                thumbsContainer.appendChild(thumb);
            });
        }
    }

    function formatDate(yyyymmdd) {
        const [y, m, d] = yyyymmdd.split('-');
        return `${d}/${m}/${y}`;
    }

    /* ===== Navigation ===== */
    $('#btn-next').addEventListener('click', () => {
        if (!validateStep(currentStep)) return;
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    });
    $('#btn-prev').addEventListener('click', () => {
        if (currentStep > 1) { currentStep--; showStep(currentStep); }
    });
    $('#crisis-form').addEventListener('submit', (e) => {
        if (!validateStep(3)) { e.preventDefault(); }
        else { toast('success', 'Submitting...', 'Your report is being sent.', 2000); }
    });
    $$('.wizard-step').forEach(step => {
        step.addEventListener('click', () => {
            const target = parseInt(step.dataset.step, 10);
            if (target < currentStep) { currentStep = target; showStep(currentStep); }
        });
    });

    showStep(1);
})();
