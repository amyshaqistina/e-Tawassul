<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLDMSRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('student')->check();
    }

    public function rules(): array
    {
        return [
            'media_type' => ['required', Rule::in(['text', 'image', 'audio', 'document', 'video', 'mixed'])],

            'message_content' => [
                'nullable',
                'string',
                'max:20000',
                Rule::requiredIf(fn () => in_array($this->input('media_type'), ['text', 'mixed'], true)),
            ],

            'media_files'   => ['nullable', 'array', 'max:10'],
            'media_files.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx,mp3,wav,webm,ogg,m4a,mp4',
                'max:102400',
            ],

            // Paths the user ticked "Delete" on in the edit form.
            // We validate they're strings; the controller checks they
            // actually belong to this LDMS before deleting.
            'remove_files'   => ['nullable', 'array'],
            'remove_files.*' => ['string'],
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
