{{-- 
    e-Tawassul Chatbot Widget
    Drop into your shared layout: <x-chatbot-widget />
    Requires: Alpine.js (already in your stack), Bootstrap Icons (already in your stack)
--}}

<div
    x-data="chatbotWidget()"
    x-cloak
    class="etw-chatbot"
    :class="{ 'etw-chatbot--open': isOpen, 'etw-chatbot--rtl': language === 'ar' }"
>
    {{-- Floating launcher button --}}
    <button
        @click="toggle()"
        class="etw-chatbot__launcher"
        :aria-label="t('open_chat')"
        x-show="!isOpen"
        x-transition
    >
        <i class="bi bi-chat-dots-fill"></i>
        <span class="etw-chatbot__launcher-label" x-text="t('need_help')"></span>
    </button>

    {{-- Main chat window --}}
    <div class="etw-chatbot__window" x-show="isOpen" x-transition>
        {{-- Header --}}
        <div class="etw-chatbot__header">
            <div>
                <strong x-text="t('title')"></strong>
                <small class="d-block" x-text="t('subtitle')"></small>
            </div>
            <div class="etw-chatbot__header-actions">
                {{-- Language selector --}}
                <select
                    class="etw-chatbot__lang"
                    x-model="language"
                    @change="onLanguageChange()"
                    :aria-label="t('choose_language')"
                >
                    <option value="en">🇬🇧 EN</option>
                    <option value="ms">🇲🇾 MS</option>
                    <option value="ar">🇸🇦 AR</option>
                </select>
                <button @click="toggle()" class="etw-chatbot__close" :aria-label="t('close')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div class="etw-chatbot__messages" x-ref="messagesEl">
            <template x-for="(msg, i) in messages" :key="i">
                <div
                    class="etw-chatbot__msg"
                    :class="{
                        'etw-chatbot__msg--user': msg.role === 'user',
                        'etw-chatbot__msg--bot': msg.role === 'bot',
                        'etw-chatbot__msg--escalate': msg.escalate
                    }"
                >
                    <div class="etw-chatbot__msg-bubble" x-html="formatMessage(msg.text)"></div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="isLoading" class="etw-chatbot__msg etw-chatbot__msg--bot">
                <div class="etw-chatbot__msg-bubble etw-chatbot__typing">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        {{-- Quick-reply suggestion chips (role-aware) --}}
        <div class="etw-chatbot__suggestions" x-show="messages.length <= 1">
            <template x-for="suggestion in suggestions()" :key="suggestion">
                <button
                    class="etw-chatbot__chip"
                    @click="sendMessage(suggestion)"
                    x-text="suggestion"
                ></button>
            </template>
        </div>

        {{-- Input --}}
        <form @submit.prevent="sendMessage(input)" class="etw-chatbot__input-row">
            <input
                type="text"
                x-model="input"
                :placeholder="t('placeholder')"
                :disabled="isLoading"
                maxlength="1000"
                class="etw-chatbot__input"
            />
            <button
                type="submit"
                :disabled="!input.trim() || isLoading"
                class="etw-chatbot__send"
                :aria-label="t('send')"
            >
                <i class="bi bi-send-fill"></i>
            </button>
        </form>

        <div class="etw-chatbot__disclaimer" x-text="t('disclaimer')"></div>
    </div>
</div>

<script>
function chatbotWidget() {
    return {
        isOpen: false,
        isLoading: false,
        input: '',
        language: localStorage.getItem('etw_chatbot_lang') || 'en',
        messages: [],

        // Translations for UI chrome (not chat content — that's handled by Gemini)
        translations: {
            en: {
                title: 'e-Tawassul Assistant',
                subtitle: 'Here to help you navigate',
                need_help: 'Need help?',
                open_chat: 'Open chat',
                close: 'Close',
                choose_language: 'Choose language',
                placeholder: 'Type your question...',
                send: 'Send',
                disclaimer: 'AI assistant. For urgent matters, contact admin@iium.edu.my',
                greeting: "Hi! I'm the e-Tawassul assistant. How can I help you today?",
                suggestions: [
                    'How do I report a crisis?',
                    'How does donation work?',
                    'What is LDMS?',
                    'I forgot my password',
                ],
            },
            ms: {
                title: 'Pembantu e-Tawassul',
                subtitle: 'Di sini untuk membantu',
                need_help: 'Perlu bantuan?',
                open_chat: 'Buka chat',
                close: 'Tutup',
                choose_language: 'Pilih bahasa',
                placeholder: 'Taip soalan anda...',
                send: 'Hantar',
                disclaimer: 'Pembantu AI. Untuk hal mustahak, hubungi admin@iium.edu.my',
                greeting: 'Hai! Saya pembantu e-Tawassul. Bagaimana saya boleh membantu anda hari ini?',
                suggestions: [
                    'Bagaimana lapor krisis?',
                    'Cara membuat derma?',
                    'Apa itu LDMS?',
                    'Lupa kata laluan',
                ],
            },
            ar: {
                title: 'مساعد e-Tawassul',
                subtitle: 'هنا لمساعدتك',
                need_help: 'تحتاج مساعدة؟',
                open_chat: 'فتح المحادثة',
                close: 'إغلاق',
                choose_language: 'اختر اللغة',
                placeholder: 'اكتب سؤالك...',
                send: 'إرسال',
                disclaimer: 'مساعد ذكاء اصطناعي. للأمور العاجلة، راسل admin@iium.edu.my',
                greeting: 'مرحباً! أنا مساعد e-Tawassul. كيف يمكنني مساعدتك اليوم؟',
                suggestions: [
                    'كيف أبلغ عن أزمة؟',
                    'كيف يعمل التبرع؟',
                    'ما هو LDMS؟',
                    'نسيت كلمة المرور',
                ],
            },
        },

        t(key) {
            return this.translations[this.language][key] ?? key;
        },

        suggestions() {
            return this.translations[this.language].suggestions;
        },

        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen && this.messages.length === 0) {
                this.messages.push({ role: 'bot', text: this.t('greeting') });
            }
        },

        onLanguageChange() {
            localStorage.setItem('etw_chatbot_lang', this.language);
            // Reset greeting in the new language
            this.messages = [{ role: 'bot', text: this.t('greeting') }];
        },

        async sendMessage(text) {
            const trimmed = (text || '').trim();
            if (!trimmed || this.isLoading) return;

            this.messages.push({ role: 'user', text: trimmed });
            this.input = '';
            this.isLoading = true;
            this.scrollToBottom();

            try {
                const res = await fetch('/chatbot/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        message: trimmed,
                        language: this.language,
                    }),
                });

                const data = await res.json();
                this.messages.push({
                    role: 'bot',
                    text: data.reply,
                    escalate: data.escalate ?? false,
                });
            } catch (e) {
                this.messages.push({
                    role: 'bot',
                    text: this.t('disclaimer'),
                });
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messagesEl;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        // Lightweight markdown — bold + line breaks only, escape everything else
        formatMessage(text) {
            const escaped = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            return escaped
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
        },
    };
}
</script>
