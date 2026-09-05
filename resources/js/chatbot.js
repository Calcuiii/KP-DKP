import {
    ArrowRight,
    Check,
    ChevronDown,
    ChevronLeft,
    ChevronUp,
    Copy,
    FileText,
    Fish,
    HelpCircle,
    Home,
    Info,
    Menu,
    MessageSquare,
    Plus,
    RefreshCw,
    Send,
    X,
    createIcons,
} from 'lucide';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

marked.setOptions({ gfm: true, breaks: true });

const icons = {
    ArrowRight,
    Check,
    ChevronDown,
    ChevronLeft,
    ChevronUp,
    Copy,
    FileText,
    Fish,
    HelpCircle,
    Home,
    Info,
    Menu,
    MessageSquare,
    Plus,
    RefreshCw,
    Send,
    X,
};

const refreshIcons = (root = document) => {
    createIcons({
        icons,
        attrs: {
            'aria-hidden': 'true',
        },
        nameAttr: 'data-lucide',
        root,
    });
};

const generateUuid = () => {
    if (typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
        const random = Math.floor(Math.random() * 16);
        const value = character === 'x' ? random : (random & 0x3) | 0x8;

        return value.toString(16);
    });
};

const getSessionKey = () => {
    const storageKey = 'dkp_assistant_session_key';
    let sessionKey = window.localStorage.getItem(storageKey);

    if (!sessionKey) {
        sessionKey = generateUuid();
        window.localStorage.setItem(storageKey, sessionKey);
    }

    return sessionKey;
};

const formatTime = (isoDate) => {
    if (!isoDate) {
        return '';
    }

    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(isoDate));
};

const formatHistoryGroup = (isoDate) => {
    const date = new Date(isoDate);
    const now = new Date();
    const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const startOfDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const dayDifference = Math.floor((startOfToday - startOfDate) / 86_400_000);

    if (dayDifference <= 0) {
        return 'Hari Ini';
    }

    if (dayDifference === 1) {
        return 'Kemarin';
    }

    if (dayDifference <= 7) {
        return '7 Hari Terakhir';
    }

    return 'Lebih Lama';
};

const initializeChatbot = () => {
    const app = document.querySelector('[data-chatbot-app]');

    if (!app || app.dataset.chatbotInitialized === 'true') {
        return;
    }

    app.dataset.chatbotInitialized = 'true';

    const form = app.querySelector('[data-chat-form]');
    const input = app.querySelector('[data-chat-input]');
    const sendButton = app.querySelector('[data-chat-send]');
    const characterCount = app.querySelector('[data-chat-character-count]');
    const errorBox = app.querySelector('[data-chat-error]');
    const emptyState = app.querySelector('[data-chat-empty]');
    const messagesSection = app.querySelector('[data-chat-messages]');
    const messageList = app.querySelector('[data-chat-message-list]');
    const historyLoading = app.querySelector('[data-chat-history-loading]');
    const historyEmpty = app.querySelector('[data-chat-history-empty]');
    const historyList = app.querySelector('[data-chat-history-list]');
    const newChatButton = app.querySelector('[data-chat-new]');
    const topicOptions = app.querySelector('[data-chat-topic-options]');
    const topicPanels = app.querySelectorAll('[data-chat-topic-panel]');
    const sidebar = app.querySelector('[data-chat-sidebar]');
    const sidebarOpenButton = app.querySelector('[data-chat-sidebar-open]');
    const sidebarCloseButton = app.querySelector('[data-chat-sidebar-close]');
    const sidebarOverlay = app.querySelector('[data-chat-sidebar-overlay]');
    const csrfToken = form?.querySelector('input[name="_token"]')?.value ?? '';

    if (
        !form
        || !input
        || !sendButton
        || !characterCount
        || !errorBox
        || !emptyState
        || !messagesSection
        || !messageList
        || !historyList
    ) {
        return;
    }

    const state = {
        sessionKey: getSessionKey(),
        conversationId: null,
        submitting: false,
        lastQuestion: null,
    };

    const urls = {
        history: app.dataset.historyUrl,
        send: app.dataset.sendUrl,
        escalateTemplate: app.dataset.escalateUrlTemplate,
        conversationTemplate: app.dataset.conversationUrlTemplate,
        infographics: app.dataset.infographicsUrl,
        contact: app.dataset.contactUrl,
    };

    const request = async (url, options = {}) => {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.headers ?? {}),
            },
            ...options,
        });

        let payload = {};

        try {
            payload = await response.json();
        } catch {
            payload = {};
        }

        if (!response.ok) {
            const error = new Error(payload.message ?? 'Permintaan tidak dapat diproses.');
            error.status = response.status;
            error.payload = payload;
            throw error;
        }

        return payload;
    };

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    const clearError = () => {
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    };

    const updateInputState = () => {
        const length = input.value.length;
        characterCount.textContent = `${length}/500`;
        sendButton.disabled = state.submitting || input.value.trim() === '';
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 128)}px`;
    };

    const scrollMessagesToBottom = () => {
        window.requestAnimationFrame(() => {
            messagesSection.scrollTo({
                top: messagesSection.scrollHeight,
                behavior: 'smooth',
            });
        });
    };

    const openSidebar = () => {
        sidebar?.classList.remove('-translate-x-full');
        sidebarOverlay?.classList.remove('hidden');
        sidebarOpenButton?.setAttribute('aria-expanded', 'true');
    };

    const closeSidebar = () => {
        if (window.innerWidth >= 1024) {
            return;
        }

        sidebar?.classList.add('-translate-x-full');
        sidebarOverlay?.classList.add('hidden');
        sidebarOpenButton?.setAttribute('aria-expanded', 'false');
    };

    const setConversationVisible = (visible) => {
        emptyState.classList.toggle('hidden', visible);
        messagesSection.classList.toggle('hidden', !visible);
    };

    const selectTopic = (topic) => {
        topicOptions?.classList.toggle('hidden', topic !== null);

        topicPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.chatTopicPanel !== topic);
        });

        if (topic !== null) {
            input.placeholder = topic === 'wopps'
                ? 'Tanyakan informasi tentang WOPPS...'
                : 'Tanyakan informasi tentang Magang / PKL...';
        } else {
            input.placeholder = 'Tanyakan informasi tentang layanan DKP...';
        }
    };

    const createUserMessage = (content, createdAt = new Date().toISOString()) => {
        const wrapper = document.createElement('article');
        wrapper.className = 'flex justify-end';

        const contentWrapper = document.createElement('div');
        contentWrapper.className = 'max-w-[85%] sm:max-w-[70%]';

        const bubble = document.createElement('div');
        bubble.className = 'whitespace-pre-wrap rounded-2xl rounded-tr-sm bg-ocean px-4 py-3 text-sm leading-6 text-white shadow-sm';
        bubble.textContent = content;

        const timestamp = document.createElement('p');
        timestamp.className = 'mt-1 text-right text-[10px] text-muted-foreground';
        timestamp.textContent = formatTime(createdAt);

        contentWrapper.append(bubble, timestamp);
        wrapper.append(contentWrapper);

        return wrapper;
    };

    const createSourceCard = (source) => {
        const card = document.createElement('div');
        card.className = 'rounded-xl border border-border bg-secondary/50 px-3 py-2.5';

        const top = document.createElement('div');
        top.className = 'flex items-start gap-2';

        const icon = document.createElement('i');
        icon.dataset.lucide = 'file-text';
        icon.className = 'mt-0.5 h-4 w-4 shrink-0 text-teal';

        const text = document.createElement('div');
        text.className = 'min-w-0 flex-1';

        const title = document.createElement('p');
        title.className = 'truncate text-xs font-semibold text-navy';
        title.textContent = source.document_title;

        const section = document.createElement('p');
        section.className = 'mt-0.5 text-[10px] leading-4 text-muted-foreground';
        section.textContent = `${source.document_id} · ${source.section_title}`;

        text.append(title, section);
        top.append(icon, text);
        card.append(top);

        return card;
    };

    const createAssistantMessage = (message) => {
        const wrapper = document.createElement('article');
        wrapper.className = 'flex items-start gap-3';

        const avatar = document.createElement('div');
        avatar.className = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-ocean text-white';
        avatar.innerHTML = '<i data-lucide="fish" class="h-4 w-4"></i>';

        const body = document.createElement('div');
        body.className = 'min-w-0 max-w-[calc(100%-2.75rem)] flex-1';

        const bubble = document.createElement('div');
        bubble.className = 'rounded-2xl rounded-tl-sm border border-border bg-white px-4 py-3 text-sm leading-7 text-navy shadow-sm';

        if (message.status === 'insufficient_information') {
            bubble.classList.add('border-amber-200', 'bg-amber-50');
        }

        if (message.status === 'admin_answer') {
            bubble.classList.add('border-teal/30', 'bg-teal/10');

            const adminLabel = document.createElement('div');
            adminLabel.className = 'mb-2 inline-flex items-center gap-1.5 rounded-full bg-teal px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white';
            adminLabel.innerHTML = '<i data-lucide="check" class="h-3 w-3"></i><span>Jawaban Petugas</span>';
            bubble.append(adminLabel);

            if (message.admin_answer?.ticket_code) {
                const ticketLabel = document.createElement('p');
                ticketLabel.className = 'mb-2 text-[10px] font-semibold text-teal';
                ticketLabel.textContent = `Tiket ${message.admin_answer.ticket_code}`;
                bubble.append(ticketLabel);
            }
        }

        const answerText = document.createElement('div');
        answerText.className = 'chat-markdown text-sm leading-7';
        answerText.innerHTML = DOMPurify.sanitize(marked.parse(message.content ?? ''));
        bubble.append(answerText);

        if (message.status === 'insufficient_information') {
            const fallbackPanel = document.createElement('div');
            fallbackPanel.className = 'mt-4 rounded-2xl border border-amber-200 bg-white p-4';

            const fallbackTitle = document.createElement('p');
            fallbackTitle.className = 'text-xs font-bold text-navy';
            fallbackTitle.textContent = 'Masih membutuhkan jawaban?';

            const fallbackText = document.createElement('p');
            fallbackText.className = 'mt-1 text-xs leading-5 text-muted-foreground';
            fallbackText.textContent = 'Teruskan pertanyaan kepada petugas atau lihat panduan resmi yang berkaitan.';

            const fallbackActions = document.createElement('div');
            fallbackActions.className = 'mt-3 flex flex-wrap gap-2';

            const escalationResult = document.createElement('p');
            escalationResult.className = 'mt-3 hidden rounded-xl bg-teal/10 px-3 py-2 text-xs font-semibold text-teal';

            const escalateButton = document.createElement('button');
            escalateButton.type = 'button';
            escalateButton.className = 'inline-flex items-center gap-1.5 rounded-xl bg-navy px-3 py-2 text-xs font-bold text-white transition hover:bg-ocean disabled:cursor-not-allowed disabled:opacity-60';
            escalateButton.innerHTML = '<i data-lucide="send" class="h-3.5 w-3.5"></i><span>Teruskan ke Petugas</span>';

            const showEscalated = (ticketCode, status = 'new') => {
                const answered = status === 'resolved';

                escalateButton.disabled = true;
                escalateButton.innerHTML = answered
                    ? '<i data-lucide="check" class="h-3.5 w-3.5"></i><span>Sudah dijawab admin</span>'
                    : '<i data-lucide="clock" class="h-3.5 w-3.5"></i><span>Menunggu jawaban admin</span>';
                escalationResult.textContent = answered
                    ? `Jawaban admin sudah tersedia di percakapan ini. Kode tiket: ${ticketCode}`
                    : `Pertanyaan sedang menunggu jawaban dari admin. Jawaban akan tersedia di percakapan ini setelah admin menjawab. Kode tiket: ${ticketCode}`;
                escalationResult.classList.remove('hidden');
                refreshIcons(escalateButton);
            };

            if (message.escalation?.ticket_code) {
                showEscalated(message.escalation.ticket_code, message.escalation.status);
            } else {
                escalateButton.addEventListener('click', async () => {
                    escalateButton.disabled = true;
                    escalateButton.querySelector('span').textContent = 'Mengirim...';

                    try {
                        const url = urls.escalateTemplate.replace('__MESSAGE__', String(message.id));
                        const payload = await request(url, {
                            method: 'POST',
                            body: JSON.stringify({ session_key: state.sessionKey }),
                        });
                        showEscalated(payload.data.ticket_code, payload.data.status);
                    } catch (error) {
                        escalateButton.disabled = false;
                        escalateButton.querySelector('span').textContent = 'Coba kirim lagi';
                        showError(error.message);
                    }
                });
            }

            const infographicLink = document.createElement('a');
            infographicLink.href = urls.infographics;
            infographicLink.className = 'inline-flex items-center gap-1.5 rounded-xl border border-border px-3 py-2 text-xs font-bold text-ocean transition hover:bg-secondary';
            infographicLink.innerHTML = '<i data-lucide="file-text" class="h-3.5 w-3.5"></i><span>Lihat Panduan</span>';

            fallbackActions.append(escalateButton, infographicLink);

            if (urls.contact) {
                const contactLink = document.createElement('a');
                contactLink.href = urls.contact;
                contactLink.target = '_blank';
                contactLink.rel = 'noopener noreferrer';
                contactLink.className = 'inline-flex items-center gap-1.5 rounded-xl border border-border px-3 py-2 text-xs font-bold text-teal transition hover:bg-secondary';
                contactLink.innerHTML = '<i data-lucide="message-square" class="h-3.5 w-3.5"></i><span>Hubungi Layanan</span>';
                fallbackActions.append(contactLink);
            }

            fallbackPanel.append(fallbackTitle, fallbackText, fallbackActions, escalationResult);
            bubble.append(fallbackPanel);
        }

        if (Array.isArray(message.sources) && message.sources.length > 0) {
            const sourceSection = document.createElement('div');
            sourceSection.className = 'mt-4 border-t border-border pt-3';

            const sourceToggle = document.createElement('button');
            sourceToggle.type = 'button';
            sourceToggle.className = 'inline-flex items-center gap-1.5 text-xs font-semibold text-ocean hover:underline';
            sourceToggle.innerHTML = '<i data-lucide="chevron-down" class="h-3.5 w-3.5"></i><span>Lihat sumber</span>';

            const sourceList = document.createElement('div');
            sourceList.className = 'mt-2 hidden space-y-2';
            message.sources.forEach((source) => sourceList.append(createSourceCard(source)));

            sourceToggle.addEventListener('click', () => {
                const isHidden = sourceList.classList.toggle('hidden');
                sourceToggle.innerHTML = isHidden
                    ? '<i data-lucide="chevron-down" class="h-3.5 w-3.5"></i><span>Lihat sumber</span>'
                    : '<i data-lucide="chevron-up" class="h-3.5 w-3.5"></i><span>Tutup sumber</span>';
                refreshIcons(sourceToggle);
            });

            sourceSection.append(sourceToggle, sourceList);
            bubble.append(sourceSection);
        }

        const actions = document.createElement('div');
        actions.className = 'mt-2 flex flex-wrap items-center gap-1';

        const copyButton = document.createElement('button');
        copyButton.type = 'button';
        copyButton.className = 'inline-flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs text-muted-foreground transition hover:bg-secondary hover:text-navy';
        copyButton.innerHTML = '<i data-lucide="copy" class="h-3.5 w-3.5"></i><span>Salin</span>';
        copyButton.addEventListener('click', async () => {
            await navigator.clipboard.writeText(message.content);
            copyButton.innerHTML = '<i data-lucide="check" class="h-3.5 w-3.5"></i><span>Tersalin</span>';
            copyButton.classList.add('text-teal');
            refreshIcons(copyButton);

            window.setTimeout(() => {
                copyButton.innerHTML = '<i data-lucide="copy" class="h-3.5 w-3.5"></i><span>Salin</span>';
                copyButton.classList.remove('text-teal');
                refreshIcons(copyButton);
            }, 1500);
        });

        const regenerateButton = document.createElement('button');
        regenerateButton.type = 'button';
        regenerateButton.className = 'inline-flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs text-muted-foreground transition hover:bg-secondary hover:text-navy';
        regenerateButton.innerHTML = '<i data-lucide="refresh-cw" class="h-3.5 w-3.5"></i><span>Ulangi</span>';
        regenerateButton.addEventListener('click', () => {
            if (state.lastQuestion) {
                sendQuestion(state.lastQuestion);
            }
        });

        const timestamp = document.createElement('span');
        timestamp.className = 'ml-1 text-[10px] text-muted-foreground';
        timestamp.textContent = formatTime(message.created_at);

        actions.append(copyButton, regenerateButton, timestamp);

        body.append(bubble, actions);
        wrapper.append(avatar, body);

        return wrapper;
    };

    const createLoadingMessage = () => {
        const wrapper = document.createElement('div');
        wrapper.dataset.chatLoading = 'true';
        wrapper.className = 'flex items-start gap-3';
        wrapper.innerHTML = `
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-ocean text-white">
                <i data-lucide="fish" class="h-4 w-4"></i>
            </div>
            <div class="flex items-center gap-1.5 rounded-2xl rounded-tl-sm border border-border bg-white px-4 py-3 shadow-sm">
                <span class="mr-2 text-xs text-muted-foreground">Mencari informasi relevan...</span>
                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-ocean"></span>
                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-ocean [animation-delay:150ms]"></span>
                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-ocean [animation-delay:300ms]"></span>
            </div>
        `;

        return wrapper;
    };

    const appendMessage = (message) => {
        const element = message.role === 'user'
            ? createUserMessage(message.content, message.created_at)
            : createAssistantMessage(message);

        messageList.append(element);
        refreshIcons(element);
    };

    const resetConversation = () => {
        state.conversationId = null;
        state.lastQuestion = null;
        messageList.innerHTML = '';
        setConversationVisible(false);
        input.value = '';
        selectTopic(null);
        clearError();
        updateInputState();
        input.focus();
        closeSidebar();
    };

    const renderHistory = (conversations) => {
        historyLoading?.classList.add('hidden');
        historyList.innerHTML = '';

        if (conversations.length === 0) {
            historyEmpty?.classList.remove('hidden');
            return;
        }

        historyEmpty?.classList.add('hidden');
        const groups = new Map();

        conversations.forEach((conversation) => {
            const groupName = formatHistoryGroup(conversation.last_message_at ?? conversation.created_at);
            const group = groups.get(groupName) ?? [];
            group.push(conversation);
            groups.set(groupName, group);
        });

        groups.forEach((items, groupName) => {
            const group = document.createElement('section');

            const heading = document.createElement('p');
            heading.className = 'mb-1 px-2 text-[10px] font-semibold uppercase tracking-wider text-blue-400';
            heading.textContent = groupName;

            const list = document.createElement('div');
            list.className = 'space-y-1';

            items.forEach((conversation) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'w-full truncate rounded-lg px-3 py-2 text-left text-xs text-blue-200 transition hover:bg-white/10 hover:text-white';
                button.textContent = conversation.title;
                button.addEventListener('click', () => loadConversation(conversation.id));
                list.append(button);
            });

            group.append(heading, list);
            historyList.append(group);
        });
    };

    const loadHistory = async () => {
        try {
            const url = new URL(urls.history, window.location.origin);
            url.searchParams.set('session_key', state.sessionKey);
            const payload = await request(url.toString(), { method: 'GET' });
            renderHistory(payload.data ?? []);
        } catch (error) {
            historyLoading?.classList.add('hidden');
            historyEmpty?.classList.remove('hidden');
            historyEmpty.textContent = 'Riwayat belum dapat dimuat.';
        }
    };

    const loadConversation = async (conversationId) => {
        clearError();

        try {
            const conversationUrl = urls.conversationTemplate.replace('__CONVERSATION__', String(conversationId));
            const url = new URL(conversationUrl, window.location.origin);
            url.searchParams.set('session_key', state.sessionKey);
            const payload = await request(url.toString(), { method: 'GET' });
            const conversation = payload.data;

            state.conversationId = conversation.id;
            state.lastQuestion = null;
            messageList.innerHTML = '';

            conversation.messages.forEach((message) => {
                appendMessage(message);
                if (message.role === 'user') {
                    state.lastQuestion = message.content;
                }
            });

            setConversationVisible(conversation.messages.length > 0);
            scrollMessagesToBottom();
            closeSidebar();
        } catch (error) {
            showError(error.message);
        }
    };

    const sendQuestion = async (rawQuestion) => {
        const question = rawQuestion.trim();

        if (!question || state.submitting) {
            return;
        }

        clearError();
        state.submitting = true;
        state.lastQuestion = question;
        setConversationVisible(true);
        appendMessage({
            role: 'user',
            content: question,
            created_at: new Date().toISOString(),
        });

        const loadingMessage = createLoadingMessage();
        messageList.append(loadingMessage);
        refreshIcons(loadingMessage);
        scrollMessagesToBottom();

        input.value = '';
        updateInputState();

        try {
            const payload = await request(urls.send, {
                method: 'POST',
                body: JSON.stringify({
                    session_key: state.sessionKey,
                    conversation_id: state.conversationId,
                    message: question,
                }),
            });

            state.conversationId = payload.data.conversation.id;
            loadingMessage.remove();
            appendMessage(payload.data.message);
            await loadHistory();
        } catch (error) {
            loadingMessage.remove();

            if (error.status === 429) {
                showError('Batas permintaan sementara tercapai. Silakan tunggu sebentar lalu coba kembali.');
            } else {
                showError(error.message);
            }
        } finally {
            state.submitting = false;
            updateInputState();
            input.focus();
            scrollMessagesToBottom();
        }
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        sendQuestion(input.value);
    });

    input.addEventListener('input', updateInputState);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    app.querySelectorAll('[data-chat-suggested]').forEach((button) => {
        button.addEventListener('click', () => sendQuestion(button.dataset.chatSuggested ?? ''));
    });

    app.querySelectorAll('[data-chat-topic]').forEach((button) => {
        button.addEventListener('click', () => {
            selectTopic(button.dataset.chatTopic ?? null);
            refreshIcons(emptyState);
        });
    });

    app.querySelectorAll('[data-chat-topic-reset]').forEach((button) => {
        button.addEventListener('click', () => selectTopic(null));
    });

    newChatButton?.addEventListener('click', resetConversation);
    sidebarOpenButton?.addEventListener('click', openSidebar);
    sidebarCloseButton?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);

    updateInputState();
    selectTopic(null);
    loadHistory();
    refreshIcons(app);

    const initialQuestion = new URLSearchParams(window.location.search).get('q');

    if (initialQuestion && initialQuestion.trim() !== '') {
        window.setTimeout(() => sendQuestion(initialQuestion), 150);
    }
};

document.addEventListener('DOMContentLoaded', initializeChatbot);
