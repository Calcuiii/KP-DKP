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
} from 'lucide';

createIcons({
    icons: {
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
    },
});

document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuButton = document.querySelector('[data-mobile-menu-button]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const menuIcon = document.querySelector('[data-menu-icon]');
    const closeIcon = document.querySelector('[data-close-icon]');

    if (!mobileMenuButton || !mobileMenu || !menuIcon || !closeIcon) {
        return;
    }

    mobileMenuButton.addEventListener('click', () => {
        const isOpen = !mobileMenu.classList.contains('hidden');

        mobileMenu.classList.toggle('hidden');

        menuIcon.classList.toggle('hidden', !isOpen);
        closeIcon.classList.toggle('hidden', isOpen);

        mobileMenuButton.setAttribute('aria-expanded', String(!isOpen));
    });

    const faqItems = document.querySelectorAll('[data-faq-item]');

    faqItems.forEach((item) => {
        const button = item.querySelector('[data-faq-button]');
        const answer = item.querySelector('[data-faq-answer]');
        const icon = item.querySelector('[data-faq-icon]');

        button?.addEventListener('click', () => {
            const willOpen = button.getAttribute('aria-expanded') !== 'true';

            faqItems.forEach((otherItem) => {
                const otherButton = otherItem.querySelector('[data-faq-button]');
                const otherAnswer = otherItem.querySelector('[data-faq-answer]');
                const otherIcon = otherItem.querySelector('[data-faq-icon]');

                otherButton?.setAttribute('aria-expanded', 'false');
                otherAnswer?.classList.add('hidden');

                if (otherIcon) {
                    otherIcon.setAttribute('data-lucide', 'chevron-down');
                }
            });

            if (willOpen) {
                button.setAttribute('aria-expanded', 'true');
                answer?.classList.remove('hidden');

                if (icon) {
                    icon.setAttribute('data-lucide', 'chevron-up');
                }
            }

            createIcons({
                icons: {
                    ChevronDown,
                    ChevronUp,
                },
            });
        });
    });
});
