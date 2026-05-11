/* =================================================================
   e-Tawassul - Application JavaScript
   - Alpine.js components (registered on alpine:init)
   - OTP auto-advance widget
   - Copy-to-clipboard helper
   ================================================================= */

(function () {
    'use strict';

    // CSRF helper for fetch requests
    function csrfToken() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    // -----------------------------------------------------------------
    // Alpine component: donation progress poller
    // -----------------------------------------------------------------
    document.addEventListener('alpine:init', () => {
        Alpine.data('donationProgress', (config) => ({
            crisisId: config.crisisId,
            raised: config.raised || 0,
            target: config.target || 0,
            percent: config.percent || 0,
            url: config.url,
            poll: config.poll !== false,
            lastUpdate: null,
            _interval: null,

            init() {
                if (this.poll && this.url) {
                    // Poll every 5 seconds
                    this._interval = setInterval(() => this.fetchProgress(), 5000);
                }
            },

            destroy() {
                if (this._interval) clearInterval(this._interval);
            },

            async fetchProgress() {
                try {
                    const res = await fetch(this.url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.raised = parseFloat(data.raised || 0);
                    this.target = parseFloat(data.target || 0);
                    this.percent = parseInt(data.percent || 0);
                    this.lastUpdate = new Date();
                } catch (e) {
                    // network error — silently ignore
                }
            },

            formatMoney(v) {
                return Number(v).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
        }));

        // -------------------------------------------------------------
        // Alpine component: notification bell with 30s poll
        // -------------------------------------------------------------
        Alpine.data('notificationBell', (config) => ({
            url: config.url,
            listUrl: config.listUrl,
            readUrlBase: config.readUrlBase,
            count: 0,
            recent: [],
            open: false,
            _interval: null,

            init() {
                this.fetchCount();
                this._interval = setInterval(() => this.fetchCount(), 30000);
            },

            destroy() {
                if (this._interval) clearInterval(this._interval);
            },

            async fetchCount() {
                try {
                    const res = await fetch(this.url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.count = data.count || 0;
                    this.recent = (data.recent || []).map(n => ({
                        notification_id: n.notification_id,
                        subject: n.subject,
                        notification_message: n.notification_message,
                        link: n.link,
                        timestamp: n.timestamp,
                    }));
                } catch (e) { /* ignore */ }
            },

            async markRead(n) {
                try {
                    await fetch(`${this.readUrlBase}/${n.notification_id}/read`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });
                } catch (e) { /* ignore */ }
                // Then navigate
                if (n.link) {
                    window.location.href = n.link;
                } else {
                    window.location.href = this.listUrl;
                }
            },

            formatTime(t) {
                if (!t) return '';
                try {
                    const d = new Date(t);
                    const now = new Date();
                    const diff = (now - d) / 1000;
                    if (diff < 60) return 'just now';
                    if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
                    if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
                    return d.toLocaleDateString();
                } catch (e) { return ''; }
            },
        }));

        // -------------------------------------------------------------
        // Alpine component: LDMS form with MediaRecorder
        // -------------------------------------------------------------
        Alpine.data('ldmsForm', () => ({
            mediaType: 'text',
            recording: false,
            audioBlob: null,
            audioUrl: null,
            recordTime: 0,
            _mediaRecorder: null,
            _chunks: [],
            _timer: null,

            async startRecording() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Your browser does not support audio recording. Please upload an audio file instead.');
                    return;
                }
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this._chunks = [];
                    this._mediaRecorder = new MediaRecorder(stream);
                    this._mediaRecorder.ondataavailable = e => {
                        if (e.data && e.data.size > 0) this._chunks.push(e.data);
                    };
                    this._mediaRecorder.onstop = () => {
                        this.audioBlob = new Blob(this._chunks, { type: 'audio/webm' });
                        this.audioUrl = URL.createObjectURL(this.audioBlob);
                        this.$refs.player.src = this.audioUrl;
                        // Attach blob into hidden file input via DataTransfer
                        const dt = new DataTransfer();
                        const file = new File([this.audioBlob], `recording-${Date.now()}.webm`, { type: 'audio/webm' });
                        dt.items.add(file);
                        this.$refs.audioFileInput.files = dt.files;
                        stream.getTracks().forEach(t => t.stop());
                    };
                    this._mediaRecorder.start();
                    this.recording = true;
                    this.recordTime = 0;
                    this._timer = setInterval(() => this.recordTime++, 1000);
                } catch (err) {
                    console.error(err);
                    alert('Could not access microphone: ' + err.message);
                }
            },

            stopRecording() {
                if (this._mediaRecorder && this.recording) {
                    this._mediaRecorder.stop();
                    this.recording = false;
                    if (this._timer) clearInterval(this._timer);
                }
            },
        }));
    });

    // -----------------------------------------------------------------
    // OTP digit auto-advance widget (vanilla, runs on DOMContentLoaded)
    // -----------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        const otpGroups = document.querySelectorAll('[data-otp-input]');
        otpGroups.forEach(group => {
            const inputs = Array.from(group.querySelectorAll('.otp-digit'));
            const finalEl = document.getElementById('otp-final');

            const sync = () => {
                if (finalEl) finalEl.value = inputs.map(i => i.value).join('');
            };

            inputs.forEach((input, idx) => {
                input.addEventListener('input', (e) => {
                    const v = e.target.value.replace(/[^0-9]/g, '').slice(0, 1);
                    e.target.value = v;
                    if (v && idx < inputs.length - 1) inputs[idx + 1].focus();
                    sync();
                });
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !input.value && idx > 0) {
                        inputs[idx - 1].focus();
                    }
                });
                input.addEventListener('paste', (e) => {
                    const pasted = (e.clipboardData || window.clipboardData).getData('text');
                    if (!pasted) return;
                    e.preventDefault();
                    const digits = pasted.replace(/\D/g, '').slice(0, inputs.length).split('');
                    digits.forEach((d, i) => { if (inputs[i]) inputs[i].value = d; });
                    const lastIndex = Math.min(digits.length, inputs.length) - 1;
                    if (lastIndex >= 0) inputs[Math.min(digits.length, inputs.length - 1)].focus();
                    sync();
                });
            });
        });

        // ---------------------------------------------------------------
        // Generic copy-to-clipboard for elements with [data-copy]
        // ---------------------------------------------------------------
        document.querySelectorAll('[data-copy]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const text = btn.getAttribute('data-copy');
                try {
                    await navigator.clipboard.writeText(text);
                    const originalLabel = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                    setTimeout(() => { btn.innerHTML = originalLabel; }, 1400);
                } catch (e) { /* ignore */ }
            });
        });
    });
})();
