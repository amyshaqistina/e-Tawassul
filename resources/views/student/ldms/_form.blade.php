{{-- LDMS create/edit form. Expects $action (POST url), $method (POST/PUT), $ldms (nullable) --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="ldmsForm()">
    @csrf
    @if(($method ?? 'POST') === 'PUT')
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Message Type</label>
        <select name="media_type" class="form-select" x-model="mediaType" required>
            <option value="text">Written letter</option>
            <option value="audio">Voice recording</option>
            <option value="image">Photo(s)</option>
            <option value="mixed">Mixed (text + media)</option>
        </select>
    </div>

    <div class="mb-3" x-show="['text','mixed'].includes(mediaType)">
        <label class="form-label">Your Message</label>
        <textarea name="message_content" rows="8" maxlength="20000" class="form-control" placeholder="Write your message here. This will be encrypted before being saved.">{{ old('message_content', $ldms?->message_content ?? '') }}</textarea>
        <small class="text-muted">Encrypted with AES-256 before storage.</small>
    </div>

    <div class="mb-3" x-show="['image','mixed'].includes(mediaType)">
        <label class="form-label">Attach Photos</label>
        <input type="file" name="media_files[]" multiple accept="image/*" class="form-control">
        <small class="text-muted">Up to 5 files, 20MB each.</small>
    </div>

    <div class="mb-3" x-show="['audio','mixed'].includes(mediaType)">
        <label class="form-label">Voice Recording</label>
        <div class="audio-recorder p-3 bg-light rounded">
            <div class="d-flex align-items-center gap-2 mb-2">
                <button type="button" class="btn btn-danger btn-sm" x-show="!recording" @click="startRecording()">
                    <i class="bi bi-mic-fill"></i> Start Recording
                </button>
                <button type="button" class="btn btn-secondary btn-sm" x-show="recording" @click="stopRecording()">
                    <i class="bi bi-stop-fill"></i> Stop
                </button>
                <span x-show="recording" class="text-danger small"><i class="bi bi-record-circle"></i> Recording… <span x-text="recordTime"></span>s</span>
                <span x-show="audioBlob && !recording" class="text-success small"><i class="bi bi-check-circle"></i> Captured</span>
            </div>
            <audio x-ref="player" controls x-show="audioUrl" class="w-100"></audio>
            <input type="file" name="media_files[]" accept="audio/*" class="form-control mt-2" x-ref="audioFileInput">
            <small class="text-muted d-block mt-1">You can either record above, or upload an audio file directly.</small>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-shield-lock"></i> {{ $submitLabel ?? 'Save Encrypted' }}</button>
        <a href="{{ route('student.ldms.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
