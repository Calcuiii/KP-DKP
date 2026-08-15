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
    ArrowLeft,
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
    ChevronLeft,
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
    Download,
    Edit2,
    Home,
    HelpCircle,
    Compass,
    ArrowDown,
    ClipboardList,
    Images,
} from 'lucide';

createIcons({
    icons: {
        Fish, MessageSquare, Menu, X, Zap, ChevronRight, FileText, Send,
        Shield, ArrowLeft, ArrowRight, BookOpen, CheckCircle, Award, MessageCircle,
        Search, Database, Info, Layers, FileCheck, RefreshCw, TrendingUp,
        ChevronDown, ChevronUp, ChevronLeft, BarChart2, Inbox, ThumbsUp, Settings,
        Users, Activity, LogOut, Bell, Eye, EyeOff, Lock, AlertCircle,
        Hash, Clock, Star, Plus, RotateCcw, Trash2, Upload, XCircle,
        Download, Edit2, Home, HelpCircle, Compass, ArrowDown, ClipboardList, Images,
    },
});

document.addEventListener('DOMContentLoaded', () => {
    // ── Participant portal: guided navigation and reveal animations ───
    const participantContent = document.querySelector('.participant-dashboard-content');

    if (participantContent) {
        const sections = Array.from(participantContent.querySelectorAll(':scope > section[id]'));
        const navigationLinks = Array.from(document.querySelectorAll('[data-participant-nav]'));
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        Array.from(participantContent.children).forEach((element, index) => {
            element.classList.add('participant-reveal');
            element.style.transitionDelay = reducedMotion ? '0ms' : `${Math.min(index * 55, 220)}ms`;
        });

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });

        Array.from(participantContent.children).forEach((element) => revealObserver.observe(element));

        const setActiveNavigation = (id) => {
            navigationLinks.forEach((link) => {
                link.classList.toggle('is-active', link.getAttribute('href') === `#${id}`);
            });
        };

        const sectionObserver = new IntersectionObserver((entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (visible?.target.id) {
                setActiveNavigation(visible.target.id);
            }
        }, { rootMargin: '-18% 0px -62% 0px', threshold: [0, 0.2, 0.5] });

        sections.forEach((section) => sectionObserver.observe(section));
        setActiveNavigation(sections[0]?.id ?? 'kenali-si-molek');
    }

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

    // ── Landing: 3D infographic coverflow ─────────────────────────────
    document.querySelectorAll('[data-infographic-coverflow]').forEach((coverflow) => {
        const frame = coverflow.querySelector('[data-infographic-coverflow-frame]');
        const cards = Array.from(coverflow.querySelectorAll('[data-infographic-coverflow-card]'));
        const previousButton = coverflow.querySelector('[data-infographic-coverflow-previous]');
        const nextButton = coverflow.querySelector('[data-infographic-coverflow-next]');
        const pagination = coverflow.querySelector('[data-infographic-coverflow-pagination]');
        const caption = coverflow.querySelector('[data-infographic-coverflow-caption]');
        const detail = coverflow.querySelector('[data-infographic-coverflow-detail]');

        if (! frame || ! cards.length || ! pagination || ! caption || ! detail) {
            return;
        }

        const count = cards.length;
        let position = 0;
        let target = 0;
        let animationFrame = null;
        let cardWidth = 0;
        let drag = null;
        let didDrag = false;
        const paginationButtons = cards.map((card, index) => {
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'h-2 w-2 rounded-full bg-ocean/25 transition-all hover:bg-ocean/60 focus:outline-none focus:ring-2 focus:ring-ocean';
            button.setAttribute('aria-label', `Tampilkan infografis ${index + 1}`);
            button.addEventListener('click', () => goTo(index));
            pagination.append(button);

            return button;
        });

        const indexAt = (value) => ((Math.round(value) % count) + count) % count;
        const shortestOffset = (index, value) => {
            let offset = index - value;

            offset = ((offset % count) + count) % count;

            return offset > count / 2 ? offset - count : offset;
        };

        const render = () => {
            if (! cardWidth) {
                return;
            }

            const activeIndex = indexAt(position);
            const pitch = cardWidth * 1.05;

            cards.forEach((card, index) => {
                const offset = shortestOffset(index, position);
                const distance = Math.abs(offset);
                const ramp = Math.pow(distance, 0.58);
                const tilt = Math.min(44 * ramp, 78) * Math.sign(offset);
                const opacity = Math.max(0, 1 - (0.13 * distance));

                card.style.transform = `translateX(calc(-50% + ${offset * pitch}px)) translateZ(${-0.58 * cardWidth * ramp}px) rotateY(${-tilt}deg)`;
                card.style.opacity = String(opacity);
                card.style.zIndex = String(100 - Math.round(distance));
                card.tabIndex = index === activeIndex ? 0 : -1;
                card.setAttribute('aria-hidden', String(distance > 3));
            });

            const activeCard = cards[activeIndex];
            caption.textContent = activeCard.dataset.imageCaption ?? '';
            detail.textContent = activeCard.querySelector('span')?.textContent?.trim() ?? '';

            paginationButtons.forEach((button, index) => {
                const isActive = index === activeIndex;

                button.classList.toggle('w-5', isActive);
                button.classList.toggle('bg-ocean', isActive);
                button.classList.toggle('bg-ocean/25', ! isActive);
                button.setAttribute('aria-current', String(isActive));
            });
        };

        const settle = (nextTarget) => {
            target = nextTarget;

            if (animationFrame !== null) {
                window.cancelAnimationFrame(animationFrame);
            }

            const animate = () => {
                const remaining = target - position;

                if (Math.abs(remaining) < 0.001) {
                    position = target;
                    render();
                    animationFrame = null;

                    return;
                }

                position += remaining * 0.16;
                render();
                animationFrame = window.requestAnimationFrame(animate);
            };

            animationFrame = window.requestAnimationFrame(animate);
        };

        const goTo = (index) => {
            const nearestTarget = index + Math.round((target - index) / count) * count;

            settle(nearestTarget);
        };

        const nudge = (amount) => settle(Math.round(target) + amount);

        const measure = () => {
            cardWidth = cards[0].offsetWidth;
            render();
        };

        frame.addEventListener('pointerdown', (event) => {
            if (animationFrame !== null) {
                window.cancelAnimationFrame(animationFrame);
                animationFrame = null;
            }

            frame.setPointerCapture(event.pointerId);
            target = position;
            didDrag = false;
            drag = {
                id: event.pointerId,
                startX: event.clientX,
                startPosition: position,
                lastPosition: position,
                velocity: 0,
                time: performance.now(),
            };
        });

        frame.addEventListener('pointermove', (event) => {
            if (! drag || drag.id !== event.pointerId || ! cardWidth) {
                return;
            }

            const nextPosition = drag.startPosition - ((event.clientX - drag.startX) / (cardWidth * 1.05));
            const now = performance.now();

            didDrag = didDrag || Math.abs(event.clientX - drag.startX) > 6;
            drag.velocity = ((nextPosition - drag.lastPosition) / Math.max(now - drag.time, 1)) * 1000;
            drag.lastPosition = nextPosition;
            drag.time = now;
            position = nextPosition;
            target = nextPosition;
            render();
        });

        const endDrag = (event) => {
            if (! drag || drag.id !== event.pointerId) {
                return;
            }

            const carried = Math.max(-2, Math.min(2, drag.velocity * 0.18));
            drag = null;
            settle(Math.round(position + carried));
        };

        frame.addEventListener('pointerup', endDrag);
        frame.addEventListener('pointercancel', endDrag);
        frame.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                nudge(-1);
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                nudge(1);
            }
        });

        cards.forEach((card) => {
            card.addEventListener('click', (event) => {
                if (didDrag) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }
            }, true);
        });

        previousButton?.addEventListener('click', () => nudge(-1));
        nextButton?.addEventListener('click', () => nudge(1));
        window.addEventListener('resize', measure);
        measure();
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

    // ── Portal peserta: password visibility dan loading form ─────────
    document.querySelectorAll('[data-participant-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordTarget);

            if (! input) {
                return;
            }

            const isPassword = input.getAttribute('type') === 'password';

            input.setAttribute('type', isPassword ? 'text' : 'password');
            button.querySelector('[data-password-eye-open]')?.classList.toggle('hidden', isPassword);
            button.querySelector('[data-password-eye-closed]')?.classList.toggle('hidden', ! isPassword);
            button.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
    });

    document.querySelectorAll('[data-participant-auth-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const submitButton = form.querySelector('[data-participant-submit]');

            if (! submitButton) {
                return;
            }

            submitButton.disabled = true;
            submitButton.querySelector('[data-participant-submit-label]').textContent = 'Memproses...';
            submitButton.querySelector('[data-participant-submit-icon]')?.classList.add('hidden');
            submitButton.querySelector('[data-participant-submit-spinner]')?.classList.remove('hidden');
        });
    });
});
