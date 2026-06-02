<div
    x-data="chatbotWidget()"
    x-cloak
    class="etw-chatbot"
    :class="{ 'etw-chatbot--open': isOpen, 'etw-chatbot--rtl': language === 'ar' }"
    :style="positionStyle"
>
    {{-- Floating launcher button (DRAGGABLE) --}}
    <button
        @click="onLauncherClick($event)"
        @mousedown="startDrag($event)"
        @touchstart="startDrag($event)"
        class="etw-chatbot__launcher"
        :class="{ 'etw-chatbot__launcher--dragging': isDragging }"
        :aria-label="t('open_chat')"
        x-show="!isOpen"
        x-transition
    >
        <i class="bi bi-chat-dots-fill"></i>
        <span class="etw-chatbot__launcher-label" x-text="t('need_help')"></span>
    </button>

    {{-- Main chat window --}}
    <div class="etw-chatbot__window" x-show="isOpen" x-transition>
        {{-- Header (also draggable) --}}
        <div
            class="etw-chatbot__header"
            :class="{ 'etw-chatbot__header--dragging': isDragging }"
            @mousedown="startDrag($event)"
            @touchstart="startDrag($event)"
            :title="t('drag_hint')"
        >
            <div class="etw-chatbot__header-info">
                <i class="bi bi-grip-vertical etw-chatbot__drag-handle" aria-hidden="true"></i>
                <div>
                    <strong x-text="t('title')"></strong>
                    <small class="d-block" x-text="t('subtitle')"></small>
                </div>
            </div>
            <div class="etw-chatbot__header-actions">
                <select
                    class="etw-chatbot__lang"
                    x-model="language"
                    @change="onLanguageChange()"
                    @mousedown.stop
                    @touchstart.stop
                    :aria-label="t('choose_language')"
                >
                    <option value="en">🇬🇧 EN</option>
                    <option value="ms">🇲🇾 MS</option>
                    <option value="ar">🇸🇦 AR</option>
                </select>
                <button
                    @click="close()"
                    @mousedown.stop
                    @touchstart.stop
                    class="etw-chatbot__close"
                    :aria-label="t('close')"
                >
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

            <div x-show="isLoading" class="etw-chatbot__msg etw-chatbot__msg--bot">
                <div class="etw-chatbot__msg-bubble etw-chatbot__typing">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        {{-- Quick-reply suggestion chips --}}
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

        // Position state (in-memory only — resets on close)
        position: null,
        isDragging: false,
        dragMoved: false,         // Did the user actually move, or just click?
        dragOffsetX: 0,
        dragOffsetY: 0,
        dragStartX: 0,
        dragStartY: 0,

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
                drag_hint: 'Drag to move',
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
                drag_hint: 'Seret untuk gerak',
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
                drag_hint: 'اسحب للتحريك',
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

        // ============ POSITION / DRAG LOGIC ============

        get positionStyle() {
            // No position set OR mobile screen → use CSS default (bottom-right)
            if (!this.position || window.innerWidth <= 480) {
                return '';
            }
            return `left: ${this.position.x}px; top: ${this.position.y}px; right: auto; bottom: auto;`;
        },

        startDrag(event) {
            // Disable drag on mobile
            if (window.innerWidth <= 480) return;

            // Don't drag if click was on an interactive element (lang select, X button)
            const tag = event.target.tagName;
            if (['SELECT', 'OPTION', 'INPUT'].includes(tag)) return;
            // BUTTON check is only for header buttons, not the launcher itself
            if (tag === 'BUTTON' && !event.target.classList.contains('etw-chatbot__launcher')) return;

            const widget = document.querySelector('.etw-chatbot');
            const rect = widget.getBoundingClientRect();
            const point = event.touches ? event.touches[0] : event;

            this.isDragging = true;
            this.dragMoved = false;
            this.dragStartX = point.clientX;
            this.dragStartY = point.clientY;
            this.dragOffsetX = point.clientX - rect.left;
            this.dragOffsetY = point.clientY - rect.top;

            const onMove = (e) => this.onDrag(e);
            const onEnd = (e) => {
                this.endDrag(e);
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onEnd);
                document.removeEventListener('touchmove', onMove);
                document.removeEventListener('touchend', onEnd);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onEnd);
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('touchend', onEnd);
        },

        onDrag(event) {
            if (!this.isDragging) return;

            const point = event.touches ? event.touches[0] : event;

            // Detect actual movement (3px threshold so a normal click doesn't count)
            const dx = Math.abs(point.clientX - this.dragStartX);
            const dy = Math.abs(point.clientY - this.dragStartY);
            if (!this.dragMoved && (dx > 3 || dy > 3)) {
                this.dragMoved = true;
            }
            if (!this.dragMoved) return;

            const widget = document.querySelector('.etw-chatbot');
            const rect = widget.getBoundingClientRect();

            let newX = point.clientX - this.dragOffsetX;
            let newY = point.clientY - this.dragOffsetY;

            // Constrain to viewport — keep at least 60px visible on every edge
            const minX = -rect.width + 60;
            const maxX = window.innerWidth - 60;
            const minY = 0;
            const maxY = window.innerHeight - 60;

            newX = Math.max(minX, Math.min(newX, maxX));
            newY = Math.max(minY, Math.min(newY, maxY));

            this.position = { x: newX, y: newY };
            event.preventDefault();
        },

        endDrag(event) {
            this.isDragging = false;
            // Don't save to localStorage — position resets on close (per user requirement)
        },

        onLauncherClick(event) {
            // If user actually dragged, don't open the chat
            if (this.dragMoved) {
                this.dragMoved = false;
                event.preventDefault();
                event.stopPropagation();
                return;
            }
            this.toggle();
        },

        // ============ CHAT LOGIC ============

        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen && this.messages.length === 0) {
                this.messages.push({ role: 'bot', text: this.t('greeting') });
            }
        },

        close() {
            this.isOpen = false;
            // RETURN TO ORIGINAL CORNER on close
            this.position = null;
        },

        onLanguageChange() {
            localStorage.setItem('etw_chatbot_lang', this.language);
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
