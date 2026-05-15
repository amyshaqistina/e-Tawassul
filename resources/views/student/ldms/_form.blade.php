{{-- LDMS create/edit form. Expects $action (POST url), $method (POST/PUT), $ldms (nullable) --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="ldmsForm()">
    @csrf
    @if(($method ?? 'POST') === 'PUT')
        @method('PUT')
    @endif

    {{-- ============ MESSAGE TYPE ============ --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Message Type</label>
        <select name="media_type" class="form-select" x-model="mediaType" required>
            <option value="text">Written letter</option>
            <option value="audio">Voice recording</option>
            <option value="image">Photo(s)</option>
            <option value="document">Document(s) — PDF / Word</option>
            <option value="video">Video</option>
            <option value="mixed">Mixed (text + media)</option>
        </select>
        <small class="text-muted">Choose what kind of message you'd like to leave. You can change this later by editing.</small>
    </div>

    {{-- ============ WRITTEN MESSAGE ============ --}}
    <div class="mb-3" x-show="['text','mixed'].includes(mediaType)" x-transition>
        <label class="form-label fw-semibold">Your Message</label>
        <textarea name="message_content" rows="8" maxlength="20000" class="form-control"
                  placeholder="Write your message here. This will be encrypted before being saved.">{{ old('message_content', $ldms?->message_content ?? '') }}</textarea>
        <small class="text-muted"><i class="bi bi-shield-lock"></i> Encrypted with AES-256 before storage.</small>
    </div>

    {{-- ============ PHOTOS (with camera capture + preview) ============ --}}
    <div class="mb-3" x-show="['image','mixed'].includes(mediaType)" x-transition>
        <label class="form-label fw-semibold">Photos</label>

        <div class="d-flex flex-wrap gap-2 mb-2">
            <label class="btn btn-outline-primary btn-sm mb-0">
                <i class="bi bi-images"></i> Choose from Gallery
                <input type="file" name="media_files[]" multiple accept="image/jpeg,image/png,image/webp"
                       class="d-none" @change="previewImages($event)">
            </label>
            <label class="btn btn-outline-success btn-sm mb-0">
                <i class="bi bi-camera-fill"></i> Take a Photo
                <input type="file" name="media_files[]" accept="image/*" capture="environment"
                       class="d-none" @change="previewImages($event)">
            </label>
        </div>

        {{-- Live previews of selected images --}}
        <div class="row g-2" x-show="imagePreviews.length > 0">
            <template x-for="(img, idx) in imagePreviews" :key="idx">
                <div class="col-6 col-md-3">
                    <div class="position-relative border rounded overflow-hidden" style="aspect-ratio: 1/1;">
                        <img :src="img.url" :alt="img.name" class="w-100 h-100" style="object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                style="padding: 2px 6px;" @click="removeImage(idx)">
                            <i class="bi bi-x"></i>
                        </button>
                        <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-50 text-white small px-2 py-1 text-truncate" x-text="img.name"></div>
                    </div>
                </div>
            </template>
        </div>
        <small class="text-muted d-block mt-1">Up to 5 photos, 20MB each. Accepted: JPG, PNG, WEBP.</small>
    </div>

    {{-- ============ DOCUMENTS (PDF preview + Word card) ============ --}}
    <div class="mb-3" x-show="['document','mixed'].includes(mediaType)" x-transition>
        <label class="form-label fw-semibold">Documents</label>
        <input type="file" name="media_files[]" multiple
               accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
               class="form-control" @change="previewDocuments($event)">
        <small class="text-muted d-block mt-1">Accepted: PDF, DOC, DOCX. Up to 10MB each.</small>

        {{-- PDF inline preview + Word doc cards --}}
        <div class="mt-3" x-show="documentPreviews.length > 0">
            <template x-for="(doc, idx) in documentPreviews" :key="idx">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="small">
                            <i class="bi" :class="doc.type === 'pdf' ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-word text-primary'"></i>
                            <span x-text="doc.name" class="fw-semibold"></span>
                            <span class="text-muted" x-text="'(' + doc.size + ')'"></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeDocument(idx)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    {{-- PDF: inline preview --}}
                    <template x-if="doc.type === 'pdf'">
                        <iframe :src="doc.url" class="w-100 border rounded" style="height: 400px;"></iframe>
                    </template>
                    {{-- Word: download card --}}
                    <template x-if="doc.type !== 'pdf'">
                        <div class="alert alert-light border d-flex align-items-center gap-2 mb-0">
                            <i class="bi bi-file-earmark-word fs-3 text-primary"></i>
                            <div class="small">
                                <div>Word document preview is not available in-browser.</div>
                                <div class="text-muted">It will be securely stored and downloadable after release.</div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- ============ VIDEO ============ --}}
    <div class="mb-3" x-show="['video','mixed'].includes(mediaType)" x-transition>
        <label class="form-label fw-semibold">Video</label>

        <div class="d-flex flex-wrap gap-2 mb-2">
            <label class="btn btn-outline-primary btn-sm mb-0">
                <i class="bi bi-film"></i> Choose Video File
                <input type="file" name="media_files[]" accept="video/mp4,video/webm"
                       class="d-none" @change="previewVideo($event)">
            </label>
            <label class="btn btn-outline-success btn-sm mb-0">
                <i class="bi bi-camera-video-fill"></i> Record Video
                <input type="file" name="media_files[]" accept="video/*" capture="user"
                       class="d-none" @change="previewVideo($event)">
            </label>
        </div>

        <div x-show="videoPreview">
            <video :src="videoPreview" controls class="w-100 rounded border" style="max-height: 400px;"></video>
            <div class="d-flex justify-content-between align-items-center mt-1 small">
                <span class="text-muted"><i class="bi bi-check-circle text-success"></i> <span x-text="videoName"></span></span>
                <button type="button" class="btn btn-sm btn-outline-danger" @click="clearVideo()">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        </div>
        <small class="text-muted d-block mt-1">Accepted: MP4, WEBM. Maximum 100MB.</small>
    </div>

    {{-- ============ VOICE RECORDING (richer controls) ============ --}}
    <div class="mb-3" x-show="['audio','mixed'].includes(mediaType)" x-transition>
        <label class="form-label fw-semibold">Voice Recording</label>
        <div class="audio-recorder p-3 bg-light rounded border">

            {{-- Recorder controls --}}
            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                {{-- Start --}}
                <button type="button" class="btn btn-danger btn-sm"
                        x-show="!recording && !audioBlob" @click="startRecording()">
                    <i class="bi bi-mic-fill"></i> Start Recording
                </button>

                {{-- Stop --}}
                <button type="button" class="btn btn-secondary btn-sm"
                        x-show="recording" @click="stopRecording()">
                    <i class="bi bi-stop-fill"></i> Stop
                </button>

                {{-- Re-record (after captured) --}}
                <button type="button" class="btn btn-warning btn-sm"
                        x-show="audioBlob && !recording" @click="reRecord()">
                    <i class="bi bi-arrow-counterclockwise"></i> Re-record
                </button>

                {{-- Discard (after captured) --}}
                <button type="button" class="btn btn-outline-danger btn-sm"
                        x-show="audioBlob && !recording" @click="discardRecording()">
                    <i class="bi bi-trash"></i> Discard
                </button>

                {{-- Status --}}
                <span x-show="recording" class="text-danger small">
                    <i class="bi bi-record-circle"></i> Recording… <span x-text="recordTime"></span>s
                </span>
                <span x-show="audioBlob && !recording" class="text-success small">
                    <i class="bi bi-check-circle"></i> Captured (<span x-text="recordTime"></span>s)
                </span>
            </div>

            {{-- Playback --}}
            <audio x-ref="player" controls x-show="audioUrl" class="w-100 mb-2"></audio>

            {{-- Upload-instead fallback --}}
            <div x-show="!recording">
                <label class="form-label small text-muted mb-1">Or upload an audio file directly:</label>
                <input type="file" name="media_files[]" accept="audio/mpeg,audio/wav,audio/webm,audio/ogg"
                       class="form-control form-control-sm" x-ref="audioFileInput"
                       @change="audioFilename = $event.target.files[0]?.name || ''">
                <small class="text-muted d-block mt-1" x-show="audioFilename">
                    <i class="bi bi-paperclip"></i> <span x-text="audioFilename"></span>
                </small>
            </div>
            <small class="text-muted d-block mt-1">Accepted: MP3, WAV, WEBM, OGG.</small>
        </div>
    </div>

    {{-- ============ SUBMIT ============ --}}
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-shield-lock"></i> {{ $submitLabel ?? 'Save Encrypted' }}
        </button>
        <a href="{{ route('student.ldms.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

{{-- ============ ALPINE COMPONENT ============ --}}
<script>
function ldmsForm() {
    return {
        // media type state
        mediaType: @json(old('media_type', $ldms?->media_type ?? 'text')),

        // image previews
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
            // Note: cannot truly remove a single file from the <input>; user can re-pick
        },

        // document previews
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

        // video preview
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
            // clear the input
            const inputs = document.querySelectorAll('input[accept^="video"]');
            inputs.forEach(i => i.value = '');
        },

        // audio recorder
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
                    // attach recorded blob into the file input so it submits with the form
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

        // helper
        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },
    };
}
</script>
