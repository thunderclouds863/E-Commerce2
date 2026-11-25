/* Guspart Autoshop — Shared UI Interactions */

function toggleMenu() {
    const nav = document.querySelector('.site-nav, nav');
    const hamburger = document.querySelector('.hamburger');
    if (!nav) return;
    const isOpen = nav.classList.toggle('show');
    if (hamburger) {
        hamburger.classList.toggle('is-open', isOpen);
        hamburger.setAttribute('aria-expanded', String(isOpen));
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.site-header');
    if (header) {
        const onScroll = () => {
            header.classList.toggle('scrolled', window.scrollY > 12);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // Touch / click support for user dropdown menu
    document.querySelectorAll('.user-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const menu = trigger.closest('.user-menu');
            if (menu) menu.classList.toggle('open');
        });
    });

    // Close menus when clicking outside
    document.addEventListener('click', function (e) {
        document.querySelectorAll('.user-menu.open').forEach(function (menu) {
            if (!menu.contains(e.target)) menu.classList.remove('open');
        });

        const nav = document.querySelector('.site-nav.show');
        const hamburger = document.querySelector('.hamburger');
        if (nav && !nav.contains(e.target) && hamburger && !hamburger.contains(e.target)) {
            nav.classList.remove('show');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
    });

    // Safe smooth scrolling for valid in-page anchors
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (!href || href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
                const nav = document.querySelector('.site-nav.show');
                const hamburger = document.querySelector('.hamburger');
                if (nav) nav.classList.remove('show');
                if (hamburger) {
                    hamburger.classList.remove('is-open');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            }
        });
    });
});
