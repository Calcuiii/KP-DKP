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