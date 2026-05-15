<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLDMSRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('student')->check();
    }

    public function rules(): array
    {
        return [
            'media_type' => ['required', Rule::in(['text', 'image', 'audio', 'document', 'video', 'mixed'])],

            // text body — required when the type involves writing
            'message_content' => [
                'nullable',
                'string',
                'max:20000',
                Rule::requiredIf(fn () => in_array($this->input('media_type'), ['text', 'mixed'], true)),
            ],

            // up to 10 files in one go (images can be 5, docs can be a few — keep it generous)
            'media_files'   => ['nullable', 'array', 'max:10'],
            'media_files.*' => [
                'file',
                // Allowed MIME types across all categories the form supports.
                // Sizes are in KB. Video gets the largest budget.
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx,mp3,wav,webm,ogg,m4a,mp4',
                'max:102400', // 100 MB hard ceiling for videos; smaller types just won't hit it
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'media_files.*.mimes' => 'Only photos (jpg/png/webp), PDF, Word, audio (mp3/wav/webm/ogg/m4a), and MP4 video are allowed.',
            'media_files.*.max'   => 'Each file must be under 100MB.',
            'message_content.required_if' => 'Please write your message before saving.',
        ];
    }
}
