import './chatbot';

import {
    createIcons,
    Fish,
    MessageSquare,
    Menu,
    X,
    Zap,
    ChevronRight,
    FileText,
    Send,
    Shield,
    ArrowRight,
    BookOpen,
    CheckCircle,
    Award,
    MessageCircle,
    Search,
    Database,
    Info,
    Layers,
    FileCheck,
    RefreshCw,
    TrendingUp,
    ChevronDown,
    ChevronUp,
    BarChart2,
    Inbox,
    ThumbsUp,
    Settings,
    Users,
    Activity,
    LogOut,
    Bell,
    Eye,
    EyeOff,
    Lock,
    AlertCircle,
    Hash,
    Clock,
    Star,
    Plus,
    RotateCcw,
    Trash2,
    Upload,
    XCircle,
} from 'lucide';

createIcons({
    icons: {
        Fish, MessageSquare, Menu, X, Zap, ChevronRight, FileText, Send,
        Shield, ArrowRight, BookOpen, CheckCircle, Award, MessageCircle,
        Search, Database, Info, Layers, FileCheck, RefreshCw, TrendingUp,
        ChevronDown, ChevronUp, BarChart2, Inbox, ThumbsUp, Settings,
        Users, Activity, LogOut, Bell, Eye, EyeOff, Lock, AlertCircle,
        Hash, Clock, Star, Plus, RotateCcw, Trash2, Upload, XCircle,
    },
});

document.addEventListener('DOMContentLoaded', () => {
    // ── Landing: mobile menu ──────────────────────────────────────────
    const mobileMenuButton = document.querySelector('[data-mobile-menu-button]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const menuIcon = document.querySelector('[data-menu-icon]');
    const closeIcon = document.querySelector('[data-close-icon]');

    if (mobileMenuButton && mobileMenu && menuIcon && closeIcon) {
        mobileMenuButton.addEventListener('click', () => {
            const isOpen = !mobileMenu.classList.contains('hidden');

            mobileMenu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden', !isOpen);
            closeIcon.classList.toggle('hidden', isOpen);

            mobileMenuButton.setAttribute('aria-expanded', String(!isOpen));
        });
    }

    // ── Landing: infographic carousel ─────────────────────────────────
    document.querySelectorAll('[data-infographic-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-infographic-carousel-track]');
        const previousButton = carousel.querySelector('[data-infographic-carousel-previous]');
        const nextButton = carousel.querySelector('[data-infographic-carousel-next]');

        const scroll = (direction) => {
            track?.scrollBy({
                left: direction * track.clientWidth,
                behavior: 'smooth',
            });
        };

        previousButton?.addEventListener('click', () => scroll(-1));
        nextButton?.addEventListener('click', () => scroll(1));
    });

    // ── Infographics: full-size lightbox ───────────────────────────────
    const lightbox = document.querySelector('[data-infographic-lightbox]');
    const lightboxImage = lightbox?.querySelector('[data-infographic-lightbox-image]');
    const lightboxCaption = lightbox?.querySelector('[data-infographic-lightbox-caption]');
    const lightboxCloseButtons = lightbox?.querySelectorAll('[data-infographic-lightbox-close]');
    const lightboxPreviousButton = lightbox?.querySelector('[data-infographic-lightbox-previous]');
    const lightboxNextButton = lightbox?.querySelector('[data-infographic-lightbox-next]');
    const infographicTriggers = Array.from(document.querySelectorAll('[data-infographic-lightbox-trigger]'));
    let activeInfographicIndex = 0;
    let lightboxTrigger = null;

    const displayInfographic = (index) => {
        const item = infographicTriggers[index];

        if (! item || ! lightboxImage || ! lightboxCaption) {
            return;
        }

        activeInfographicIndex = index;
        lightboxImage.src = item.dataset.imageSrc;
        lightboxImage.alt = item.dataset.imageAlt;
        lightboxImage.width = Number(item.dataset.imageWidth);
        lightboxImage.height = Number(item.dataset.imageHeight);
        lightboxCaption.textContent = item.dataset.imageCaption;
    };

    const closeLightbox = () => {
        if (! lightbox) {
            return;
        }

        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        lightboxTrigger?.focus();
    };

    const openLightbox = (trigger) => {
        if (! lightbox) {
            return;
        }

        lightboxTrigger = trigger;
        displayInfographic(infographicTriggers.indexOf(trigger));
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        lightbox?.querySelector('[data-infographic-lightbox-close]')?.focus();
    };

    infographicTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => openLightbox(trigger));
    });

    lightboxCloseButtons?.forEach((button) => {
        button.addEventListener('click', closeLightbox);
    });

    lightboxPreviousButton?.addEventListener('click', () => {
        displayInfographic((activeInfographicIndex - 1 + infographicTriggers.length) % infographicTriggers.length);
    });

    lightboxNextButton?.addEventListener('click', () => {
        displayInfographic((activeInfographicIndex + 1) % infographicTriggers.length);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && lightbox?.getAttribute('aria-hidden') === 'false') {
            closeLightbox();
        }
    });

    // ── Landing: FAQ accordion ────────────────────────────────────────
    const faqItems = document.querySelectorAll('[data-faq-item]');

    faqItems.forEach((item) => {
        const button = item.querySelector('[data-faq-button]');
        const answer = item.querySelector('[data-faq-answer]');
        const icon = item.querySelector('[data-faq-icon]');

        button?.addEventListener('click', () => {
            const willOpen = button.getAttribute('aria-expanded') !== 'true';

            faqItems.forEach((otherItem) => {
                otherItem.querySelector('[data-faq-button]')?.setAttribute('aria-expanded', 'false');
                otherItem.querySelector('[data-faq-answer]')?.classList.add('hidden');
                otherItem.querySelector('[data-faq-icon]')?.setAttribute('data-lucide', 'chevron-down');
            });

            if (willOpen) {
                button.setAttribute('aria-expanded', 'true');
                answer?.classList.remove('hidden');
                icon?.setAttribute('data-lucide', 'chevron-up');
            }

            createIcons({ icons: { ChevronDown, ChevronUp } });
        });
    });

    // ── Admin: toggle sidebar ─────────────────────────────────────────
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-admin-sidebar]');

    sidebarToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('w-60');
        sidebar?.classList.toggle('w-0');
    });

    // ── Admin login: toggle show/hide password ────────────────────────
    const pwToggle = document.querySelector('[data-toggle-password]');
    const pwInput = document.querySelector('[data-password-input]');
    const pwEyeIcon = document.querySelector('[data-password-eye-icon]');

    pwToggle?.addEventListener('click', () => {
        const isPassword = pwInput?.getAttribute('type') === 'password';

        pwInput?.setAttribute('type', isPassword ? 'text' : 'password');
        pwEyeIcon?.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');

        createIcons({ icons: { Eye, EyeOff } });
    });

    // ── Admin: modal Knowledge Base (buka/tutup) ──────────────────────
    document.querySelectorAll('[data-open-modal]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const modal = document.querySelector(`[data-modal="${btn.dataset.openModal}"]`);
            modal?.classList.remove('hidden');
            modal?.classList.add('flex');
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const modal = document.querySelector(`[data-modal="${btn.dataset.closeModal}"]`);
            modal?.classList.add('hidden');
            modal?.classList.remove('flex');
        });
    });

    // ── Admin: tampilkan nama file yang dipilih ───────────────────────
    document.querySelectorAll('[data-file-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const label = input.closest('label')?.querySelector('[data-file-label]');
            if (label && input.files[0]) {
                label.textContent = input.files[0].name;
            }
        });
    });
});
