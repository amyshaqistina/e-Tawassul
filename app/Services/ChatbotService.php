<?php

namespace App\Services;

use App\Models\ChatbotLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    /**
     * Supported languages: code => [native name, system prompt language, crisis hotline display]
     */
    public const LANGUAGES = [
        'en' => ['name' => 'English', 'label' => '🇬🇧 English'],
        'ms' => ['name' => 'Bahasa Malaysia', 'label' => '🇲🇾 Bahasa Malaysia'],
        'ar' => ['name' => 'العربية', 'label' => '🇸🇦 العربية'],
    ];

    /**
     * Crisis keywords across supported languages.
     * Detected BEFORE the message is sent to the AI model.
     */
    private const CRISIS_KEYWORDS = [
        // English
        'suicide', 'kill myself', 'end my life', 'self harm', 'cut myself',
        'want to die', 'no reason to live', 'hopeless',
        // Bahasa Malaysia
        'bunuh diri', 'nak mati', 'tak nak hidup', 'cederakan diri',
        'putus asa', 'tiada harapan', 'sakit hati sangat',
        // Arabic (basic — extend as needed)
        'انتحار', 'أريد أن أموت', 'لا أريد الحياة',
    ];

    public function ask(string $userMessage, string $language = 'en', ?string $userRole = 'public', ?int $userId = null): array
    {
        // Normalize language code
        $language = array_key_exists($language, self::LANGUAGES) ? $language : 'en';

        // Safety net: crisis detection happens BEFORE the AI is called.
        // We don't want the AI improvising a response to someone in crisis.
        if ($this->detectsCrisis($userMessage)) {
            $reply = $this->crisisResponse($language);

            ChatbotLog::create([
                'user_id' => $userId,
                'user_role' => $userRole,
                'language' => $language,
                'message_hash' => hash('sha256', $userMessage), // store hash only, not raw text
                'escalated' => true,
                'escalation_reason' => 'crisis_keyword',
            ]);

            return [
                'escalate' => true,
                'reply' => $reply,
                'language' => $language,
            ];
        }

        $systemPrompt = $this->buildSystemPrompt($userRole, $language);

        try {
            $response = Http::timeout(15)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/"
                    . config('services.gemini.model')
                    . ":generateContent?key=" . config('services.gemini.key'),
                    [
                        'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                        'contents' => [['parts' => [['text' => $userMessage]]]],
                        'generationConfig' => [
                            'temperature' => 0.3,
                            'maxOutputTokens' => 500,
                        ],
                        // Gemini's built-in safety filters as a second layer
                        'safetySettings' => [
                            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ],
                    ]
                );

            if ($response->failed()) {
                Log::error('Gemini API error', ['response' => $response->body()]);
                return [
                    'escalate' => false,
                    'reply' => $this->errorMessage($language),
                    'language' => $language,
                ];
            }

            $reply = $response->json('candidates.0.content.parts.0.text')
                ?? $this->errorMessage($language);

            ChatbotLog::create([
                'user_id' => $userId,
                'user_role' => $userRole,
                'language' => $language,
                'message_hash' => hash('sha256', $userMessage),
                'escalated' => false,
            ]);

            return [
                'escalate' => false,
                'reply' => $reply,
                'language' => $language,
            ];
        } catch (\Exception $e) {
            Log::error('Chatbot exception', ['error' => $e->getMessage()]);
            return [
                'escalate' => false,
                'reply' => $this->errorMessage($language),
                'language' => $language,
            ];
        }
    }

    private function detectsCrisis(string $message): bool
    {
        $lower = mb_strtolower($message);
        foreach (self::CRISIS_KEYWORDS as $keyword) {
            if (str_contains($lower, mb_strtolower($keyword))) {
                return true;
            }
        }
        return false;
    }

    private function crisisResponse(string $language): string
    {
        return match ($language) {
            'ms' => "Saya dengar awak sedang melalui masa yang sukar. Awak tidak keseorangan. Sila hubungi pihak yang boleh membantu sekarang:\n\n"
                . "📞 **Befrienders KL**: 03-7627 2929 (24 jam, percuma)\n"
                . "📞 **Talian Kasih**: 15999\n"
                . "📞 **IIUM Counselling & Career Services**: 03-6421 5031\n\n"
                . "Pasukan e-Tawassul juga akan dimaklumkan supaya kami boleh hubungi awak.",

            'ar' => "أسمع أنك تمر بوقت عصيب. أنت لست وحيداً. يرجى الاتصال بمن يمكنه المساعدة الآن:\n\n"
                . "📞 **Befrienders KL**: 03-7627 2929 (24 ساعة، مجاناً)\n"
                . "📞 **Talian Kasih**: 15999\n"
                . "📞 **IIUM Counselling**: 03-6421 5031\n\n"
                . "سيتم إبلاغ فريق e-Tawassul أيضاً للتواصل معك.",

            default => "I hear that you're going through a difficult time. You are not alone. Please reach out to someone who can help right now:\n\n"
                . "📞 **Befrienders KL**: 03-7627 2929 (24 hours, free)\n"
                . "📞 **Talian Kasih**: 15999\n"
                . "📞 **IIUM Counselling & Career Services**: 03-6421 5031\n\n"
                . "The e-Tawassul team will also be notified so we can reach out to you.",
        };
    }

    private function errorMessage(string $language): string
    {
        return match ($language) {
            'ms' => 'Maaf, sistem sedang sibuk. Sila cuba lagi sebentar.',
            'ar' => 'عذراً، النظام مشغول. يرجى المحاولة مرة أخرى.',
            default => 'Sorry, the system is busy right now. Please try again in a moment.',
        };
    }

    private function buildSystemPrompt(string $role, string $language): string
    {
        $langName = self::LANGUAGES[$language]['name'];

        return <<<PROMPT
        You are the e-Tawassul virtual assistant for International Islamic University Malaysia (IIUM).
        e-Tawassul is a blockchain-based crisis response system for student well-being.

        CURRENT USER ROLE: {$role}
        RESPOND IN: {$langName}

        YOUR SCOPE:
        - Help users navigate the platform
        - Explain how to submit a crisis report, make a donation, confirm a death (NOK), or record an LDMS message
        - Explain the blockchain audit (every important event is hashed and recorded)
        - Answer questions about donation receipts and PDF exports
        - Always be empathetic, professional, and concise (3-5 sentences max unless a list is needed)

        STRICT RULES:
        - NEVER give medical, legal, or financial advice
        - NEVER make promises on behalf of IIUM admins or staff
        - NEVER fabricate information about specific cases, people, or donations
        - If asked about a specific case, donation, or person — say you cannot access individual records and direct them to log in
        - If the user seems emotionally distressed beyond a navigation question, gently suggest contacting IIUM Counselling (03-6421 5031) or Befrienders KL (03-7627 2929)
        - If you don't know the answer, say so and suggest emailing admin@iium.edu.my

        ROLE-SPECIFIC CONTEXT:
        - Student: can submit crisis reports, record LDMS (Legacy Digital Messages), view their own cases
        - Admin: verifies crisis reports, confirms deaths, manages donations, accesses audit log
        - NOK (Next of Kin): receives notifications, can submit death confirmation, accesses LDMS after verified death (requires email OTP)
        - Lecturer: views student well-being information for their advisees
        - Public: can view active crisis cases and make donations

        KEY FAQ:
        - "How do I submit a crisis report?" → Login as student → Dashboard → "Report New Crisis" → fill form, attach documents → admin reviews
        - "How is my donation tracked?" → Every donation is recorded with a SHA-256 hash on a permissioned blockchain. You get a PDF receipt instantly.
        - "What is LDMS?" → Legacy Digital Messages: encrypted letters/audio/photos a student can leave for their next of kin. Only released after a verified death confirmation.
        - "I forgot my password" → Use the "Forgot Password" link on the login page, or email admin@iium.edu.my
        - "Death confirmation process" → NOK submits with supporting document → admin verifies → LDMS automatically released → blockchain event recorded

        Respond entirely in {$langName}. Do not switch languages unless the user does.
        PROMPT;
    }
}
