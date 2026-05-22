{{-- LDMS create/edit form. Expects $action (POST url), $method (POST/PUT), $ldms (nullable) --}}

@push('styles')
<style>
    /* ===== Field styles — matched to crisis create page ===== */
    .ldms-field-group { margin-bottom: 18px; }
    .ldms-field-group > label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .ldms-field-group > label .opt {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
        margin-left: 4px;
    }
    .ldms-field-input,
    .ldms-field-textarea,
    .ldms-field-select {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        background: #f8faff;
        transition: all 0.2s;
        font-family: inherit;
        color: #0f172a;
    }
    .ldms-field-input:focus,
    .ldms-field-textarea:focus,
    .ldms-field-select:focus {
        outline: none;
        border-color: #1a56db;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(26,86,219,0.08);
    }
    .ldms-field-textarea {
        resize: vertical;
        min-height: 180px;
        line-height: 1.6;
    }
    .ldms-field-hint {
        display: block;
        margin-top: 6px;
        font-size: 11.5px;
        color: #64748b;
    }
    .ldms-field-hint i {
        color: #1a56db;
        margin-right: 4px;
    }

    /* ===== Upload buttons (camera / gallery) ===== */
    .ldms-upload-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }
    .ldms-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 14px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        color: #1a56db;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
    }
    .ldms-upload-btn:hover {
        background: #eff6ff;
        border-color: #93c5fd;
    }
    .ldms-upload-btn.capture {
        color: #059669;
    }
    .ldms-upload-btn.capture:hover {
        background: #ecfdf5;
        border-color: #6ee7b7;
    }

    /* ===== Preview grid (photos) ===== */
    .ldms-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }
    .ldms-preview-tile {
        position: relative;
        aspect-ratio: 1/1;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        background: #f1f5f9;
    }
    .ldms-preview-tile img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .ldms-preview-remove {
        position: absolute;
        top: 6px;
        right: 6px;
        background: rgba(220,38,38,0.95);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }
    .ldms-preview-remove:hover { background: #dc2626; }
    .ldms-preview-name {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        background: rgba(15,23,42,0.7);
        color: #fff;
        font-size: 10.5px;
        padding: 4px 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ===== Document preview ===== */
    .ldms-doc-preview {
        margin-top: 10px;
    }
    .ldms-doc-item {
        margin-bottom: 14px;
        background: #f8faff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
    }
    .ldms-doc-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 13px;
    }
    .ldms-doc-item-header .doc-name { font-weight: 600; color: #0f172a; }
    .ldms-doc-item-header .doc-size { color: #94a3b8; font-size: 11.5px; margin-left: 4px; }
    .ldms-doc-item-header i.bi-file-earmark-pdf { color: #dc2626; }
    .ldms-doc-item-header i.bi-file-earmark-word { color: #1a56db; }
    .ldms-doc-item iframe {
        width: 100%;
        height: 400px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    .ldms-doc-word-fallback {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .ldms-doc-word-fallback i {
        font-size: 28px;
        color: #1a56db;
        flex-shrink: 0;
    }
    .ldms-doc-word-fallback div { font-size: 12px; color: #475569; }
    .ldms-doc-word-fallback div .muted { color: #94a3b8; font-size: 11px; display: block; margin-top: 2px; }

    .ldms-btn-icon-remove {
        background: transparent;
        border: 1.5px solid #fecaca;
        color: #dc2626;
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .ldms-btn-icon-remove:hover {
        background: #fef2f2;
    }

    /* ===== Audio recorder ===== */
    .ldms-audio-box {
        background: #f8faff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
    }
    .ldms-audio-controls {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }
    .ldms-audio-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.15s;
    }
    .ldms-audio-btn.rec { background: #dc2626; color: #fff; }
    .ldms-audio-btn.rec:hover { background: #b91c1c; }
    .ldms-audio-btn.stop { background: #64748b; color: #fff; }
    .ldms-audio-btn.stop:hover { background: #475569; }
    .ldms-audio-btn.redo { background: #fff; color: #d97706; border: 1.5px solid #fcd34d; }
    .ldms-audio-btn.redo:hover { background: #fffbeb; }
    .ldms-audio-btn.discard { background: #fff; color: #dc2626; border: 1.5px solid #fecaca; }
    .ldms-audio-btn.discard:hover { background: #fef2f2; }
    .ldms-audio-status {
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .ldms-audio-status.recording { color: #dc2626; }
    .ldms-audio-status.captured { color: #059669; }
    .ldms-audio-box audio {
        width: 100%;
        margin-bottom: 10px;
    }
    .ldms-audio-fallback-label {
        display: block;
        font-size: 11.5px;
        color: #64748b;
        margin-bottom: 4px;
    }

    /* ===== Submit row ===== */
    .ldms-form-footer {
        display: flex;
        gap: 10px;
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid #f1f5f9;
    }
    .ldms-btn-submit {
        background: #059669;
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .ldms-btn-submit:hover { background: #047857; }
    .ldms-btn-cancel {
        background: #fff;
        color: #64748b;
        border: 1.5px solid #e2e8f0;
        padding: 10px 22px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .ldms-btn-cancel:hover { background: #f8fafc; color: #64748b; }
</style>
@endpush

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="ldmsForm()">
    @csrf
    @if(($method ?? 'POST') === 'PUT')
        @method('PUT')
    @endif

    {{-- ============ MESSAGE TYPE ============ --}}
    <div class="ldms-field-group">
        <label>Message type</label>
        <select name="media_type" class="ldms-field-select" x-model="mediaType" required>
            <option value="text">Written letter</option>
            <option value="audio">Voice recording</option>
            <option value="image">Photo(s)</option>
            <option value="document">Document(s) — PDF / Word</option>
            <option value="video">Video</option>
            <option value="mixed">Mixed (text + media)</option>
        </select>
        <small class="ldms-field-hint">Choose what kind of message you'd like to leave. You can change this later by editing.</small>
    </div>

    {{-- ============ WRITTEN MESSAGE ============ --}}
    <div class="ldms-field-group" x-show="['text','mixed'].includes(mediaType)" x-transition>
        <label>Your message</label>
        <textarea name="message_content" rows="8" maxlength="20000" class="ldms-field-textarea"
                  placeholder="Write your message here. This will be encrypted before being saved.">{{ old('message_content', $ldms?->message_content ?? '') }}</textarea>
        <small class="ldms-field-hint"><i class="bi bi-shield-lock-fill"></i>Encrypted with AES-256 before storage.</small>
    </div>

    {{-- ============ PHOTOS ============ --}}
    <div class="ldms-field-group" x-show="['image','mixed'].includes(mediaType)" x-transition>
        <label>Photos</label>

        <div class="ldms-upload-row">
            <label class="ldms-upload-btn">
                <i class="bi bi-images"></i> Choose from gallery
                <input type="file" name="media_files[]" multiple accept="image/jpeg,image/png,image/webp"
                       class="d-none" @change="previewImages($event)">
            </label>
            <label class="ldms-upload-btn capture">
                <i class="bi bi-camera-fill"></i> Take a photo
                <input type="file" name="media_files[]" accept="image/*" capture="environment"
                       class="d-none" @change="previewImages($event)">
            </label>
        </div>

        <div class="ldms-preview-grid" x-show="imagePreviews.length > 0">
            <template x-for="(img, idx) in imagePreviews" :key="idx">
                <div class="ldms-preview-tile">
                    <img :src="img.url" :alt="img.name">
                    <button type="button" class="ldms-preview-remove" @click="removeImage(idx)" aria-label="Remove">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="ldms-preview-name" x-text="img.name"></div>
                </div>
            </template>
        </div>
        <small class="ldms-field-hint">Up to 5 photos, 20MB each. Accepted: JPG, PNG, WEBP.</small>
    </div>

    {{-- ============ DOCUMENTS ============ --}}
    <div class="ldms-field-group" x-show="['document','mixed'].includes(mediaType)" x-transition>
        <label>Documents</label>
        <input type="file" name="media_files[]" multiple
               accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
               class="ldms-field-input" @change="previewDocuments($event)">
        <small class="ldms-field-hint">Accepted: PDF, DOC, DOCX. Up to 10MB each.</small>

        <div class="ldms-doc-preview" x-show="documentPreviews.length > 0">
            <template x-for="(doc, idx) in documentPreviews" :key="idx">
                <div class="ldms-doc-item">
                    <div class="ldms-doc-item-header">
                        <div>
                            <i class="bi" :class="doc.type === 'pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-word'"></i>
                            <span class="doc-name" x-text="doc.name"></span>
                            <span class="doc-size" x-text="'(' + doc.size + ')'"></span>
                        </div>
                        <button type="button" class="ldms-btn-icon-remove" @click="removeDocument(idx)">
                            <i class="bi bi-x-lg"></i> Remove
                        </button>
                    </div>
                    <template x-if="doc.type === 'pdf'">
                        <iframe :src="doc.url"></iframe>
                    </template>
                    <template x-if="doc.type !== 'pdf'">
                        <div class="ldms-doc-word-fallback">
                            <i class="bi bi-file-earmark-word"></i>
                            <div>
                                Word document preview is not available in-browser.
                                <span class="muted">It will be securely stored and downloadable after release.</span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- ============ VIDEO ============ --}}
    <div class="ldms-field-group" x-show="['video','mixed'].includes(mediaType)" x-transition>
        <label>Video</label>

        <div class="ldms-upload-row">
            <label class="ldms-upload-btn">
                <i class="bi bi-film"></i> Choose video file
                <input type="file" name="media_files[]" accept="video/mp4,video/webm"
                       class="d-none" @change="previewVideo($event)">
            </label>
            <label class="ldms-upload-btn capture">
                <i class="bi bi-camera-video-fill"></i> Record video
                <input type="file" name="media_files[]" accept="video/*" capture="user"
                       class="d-none" @change="previewVideo($event)">
            </label>
        </div>

        <div x-show="videoPreview" style="margin-top:10px;">
            <video :src="videoPreview" controls style="width:100%; max-height:400px; border-radius:8px; border:1px solid #e2e8f0;"></video>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px; font-size:12px;">
                <span style="color:#059669;"><i class="bi bi-check-circle-fill"></i> <span x-text="videoName"></span></span>
                <button type="button" class="ldms-btn-icon-remove" @click="clearVideo()">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        </div>
        <small class="ldms-field-hint">Accepted: MP4, WEBM. Maximum 100MB.</small>
    </div>

    {{-- ============ VOICE RECORDING ============ --}}
    <div class="ldms-field-group" x-show="['audio','mixed'].includes(mediaType)" x-transition>
        <label>Voice recording</label>
        <div class="ldms-audio-box">

            <div class="ldms-audio-controls">
                <button type="button" class="ldms-audio-btn rec"
                        x-show="!recording && !audioBlob" @click="startRecording()">
                    <i class="bi bi-mic-fill"></i> Start recording
                </button>

                <button type="button" class="ldms-audio-btn stop"
                        x-show="recording" @click="stopRecording()">
                    <i class="bi bi-stop-fill"></i> Stop
                </button>

                <button type="button" class="ldms-audio-btn redo"
                        x-show="audioBlob && !recording" @click="reRecord()">
                    <i class="bi bi-arrow-counterclockwise"></i> Re-record
                </button>

                <button type="button" class="ldms-audio-btn discard"
                        x-show="audioBlob && !recording" @click="discardRecording()">
                    <i class="bi bi-trash"></i> Discard
                </button>

                <span x-show="recording" class="ldms-audio-status recording">
                    <i class="bi bi-record-circle"></i> Recording… <span x-text="recordTime"></span>s
                </span>
                <span x-show="audioBlob && !recording" class="ldms-audio-status captured">
                    <i class="bi bi-check-circle-fill"></i> Captured (<span x-text="recordTime"></span>s)
                </span>
            </div>

            <audio x-ref="player" controls x-show="audioUrl"></audio>

            <div x-show="!recording">
                <span class="ldms-audio-fallback-label">Or upload an audio file directly:</span>
                <input type="file" name="media_files[]" accept="audio/mpeg,audio/wav,audio/webm,audio/ogg"
                       class="ldms-field-input" style="padding: 8px 12px; font-size: 13px;" x-ref="audioFileInput"
                       @change="audioFilename = $event.target.files[0]?.name || ''">
                <small class="ldms-field-hint" x-show="audioFilename">
                    <i class="bi bi-paperclip"></i><span x-text="audioFilename"></span>
                </small>
            </div>
            <small class="ldms-field-hint">Accepted: MP3, WAV, WEBM, OGG.</small>
        </div>
    </div>

    {{-- ============ SUBMIT ============ --}}
    <div class="ldms-form-footer">
        <button type="submit" class="ldms-btn-submit">
            <i class="bi bi-shield-lock-fill"></i> {{ $submitLabel ?? 'Save Encrypted' }}
        </button>
        <a href="{{ route('student.ldms.index') }}" class="ldms-btn-cancel">Cancel</a>
    </div>
</form>

{{-- ============ ALPINE COMPONENT (unchanged) ============ --}}
<script>
function ldmsForm() {
    return {
        mediaType: @json(old('media_type', $ldms?->media_type ?? 'text')),

        imagePreviews: [],
        previewImages(e) {
            for (const file of e.target.files) {
                this.imagePreviews.push({
                    name: file.name,
                    url: URL.createObjectURL(file),
                });
            }
        },
        removeImage(idx) {
            URL.revokeObjectURL(this.imagePreviews[idx].url);
            this.imagePreviews.splice(idx, 1);
        },

        documentPreviews: [],
        previewDocuments(e) {
            for (const file of e.target.files) {
                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                this.documentPreviews.push({
                    name: file.name,
                    type: isPdf ? 'pdf' : 'word',
                    size: this.formatSize(file.size),
                    url: isPdf ? URL.createObjectURL(file) : null,
                });
            }
        },
        removeDocument(idx) {
            const d = this.documentPreviews[idx];
            if (d.url) URL.revokeObjectURL(d.url);
            this.documentPreviews.splice(idx, 1);
        },

        videoPreview: null,
        videoName: '',
        previewVideo(e) {
            const file = e.target.files[0];
            if (!file) return;
            if (this.videoPreview) URL.revokeObjectURL(this.videoPreview);
            this.videoPreview = URL.createObjectURL(file);
            this.videoName = file.name;
        },
        clearVideo() {
            if (this.videoPreview) URL.revokeObjectURL(this.videoPreview);
            this.videoPreview = null;
            this.videoName = '';
            const inputs = document.querySelectorAll('input[accept^="video"]');
            inputs.forEach(i => i.value = '');
        },

        recording: false,
        mediaRecorder: null,
        audioChunks: [],
        audioBlob: null,
        audioUrl: null,
        recordTime: 0,
        recordTimer: null,
        audioFilename: '',

        async startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.audioChunks = [];
                this.mediaRecorder = new MediaRecorder(stream);
                this.mediaRecorder.ondataavailable = e => this.audioChunks.push(e.data);
                this.mediaRecorder.onstop = () => {
                    this.audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                    this.audioUrl = URL.createObjectURL(this.audioBlob);
                    this.$refs.player.src = this.audioUrl;
                    const filename = `recording-${Date.now()}.webm`;
                    const file = new File([this.audioBlob], filename, { type: 'audio/webm' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs.audioFileInput.files = dt.files;
                    this.audioFilename = filename;
                    stream.getTracks().forEach(t => t.stop());
                };
                this.mediaRecorder.start();
                this.recording = true;
                this.recordTime = 0;
                this.recordTimer = setInterval(() => this.recordTime++, 1000);
            } catch (err) {
                alert('Could not access microphone: ' + err.message);
            }
        },
        stopRecording() {
            if (this.mediaRecorder && this.recording) {
                this.mediaRecorder.stop();
                clearInterval(this.recordTimer);
                this.recording = false;
            }
        },
        reRecord() {
            this.discardRecording();
            this.startRecording();
        },
        discardRecording() {
            if (this.audioUrl) URL.revokeObjectURL(this.audioUrl);
            this.audioBlob = null;
            this.audioUrl = null;
            this.recordTime = 0;
            this.audioFilename = '';
            if (this.$refs.player) this.$refs.player.src = '';
            if (this.$refs.audioFileInput) this.$refs.audioFileInput.value = '';
        },

        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },
    };
}
</script>
