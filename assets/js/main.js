/**
 * Hostel Management System - Main JavaScript
 * Handles common UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const nav = document.querySelector('nav');
    if (nav) {
        // Add responsive behavior
    }

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.success, .error, .warning, .info');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.style.display !== 'none') {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            }
        }, 5000);
    });

    // Confirm delete actions
    const deleteLinks = document.querySelectorAll('[data-confirm]');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Toggle password visibility
    const passwordToggles = document.querySelectorAll('[data-toggle-password]');
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = this.closest('.password-wrapper').querySelector('input');
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.setAttribute('aria-label', type === 'password' ? 'Show password' : 'Hide password');
                const icon = this.querySelector('i');
                if (icon) icon.className = type === 'password' ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
            }
        });
    });

    const themeToggle = document.querySelector('[data-theme-toggle]');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const dark = document.body.classList.toggle('dark-theme');
            this.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
            this.title = this.getAttribute('aria-label');
            this.querySelector('i').className = dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        });
    }

    // Login scene: time-aware SVG, independent GSAP layers, and gentle pointer parallax.
    const loginScene = document.querySelector('.login-showcase');
    if (loginScene) {
        const hour = new Date().getHours();
        const period = hour < 6 || hour >= 20 ? 'night' : hour < 11 ? 'morning' : hour < 17 ? 'afternoon' : 'evening';
        document.body.dataset.timeTheme = period;
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (window.gsap && !reducedMotion) {
            const art = loginScene.querySelector('.hostel-illustration');
            const scene = window.gsap;
            scene.fromTo('.showcase-badge, .showcase-copy, .login-stats', { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: .7, stagger: .11, ease: 'power3.out' });
            scene.fromTo(art, { opacity: 0, y: 24 }, { opacity: 1, y: 0, duration: .9, ease: 'power3.out' });
            scene.to('.cloud-one', { x: 82, duration: 19, repeat: -1, yoyo: true, ease: 'sine.inOut' });
            scene.to('.cloud-two', { x: -66, duration: 24, repeat: -1, yoyo: true, ease: 'sine.inOut' });
            scene.to('.cloud-three', { x: 48, duration: 16, repeat: -1, yoyo: true, ease: 'sine.inOut' });
            scene.to('.tree-left', { rotation: 2, transformOrigin: '90px 358px', duration: 3.2, repeat: -1, yoyo: true, ease: 'sine.inOut' });
            scene.to('.tree-right', { rotation: -2, transformOrigin: '657px 358px', duration: 3.7, repeat: -1, yoyo: true, ease: 'sine.inOut' });
            scene.to('.svg-flag', { skewY: 7, scaleX: 1.06, transformOrigin: '488px 25px', duration: 1.25, repeat: -1, yoyo: true, ease: 'sine.inOut' });
            scene.to('.student-one', { x: 28, duration: 7, repeat: -1, yoyo: true, ease: 'none' });
            scene.to('.student-two', { x: -25, duration: 8, repeat: -1, yoyo: true, ease: 'none', delay: .6 });
            scene.to('.students', { y: -2, duration: .45, repeat: -1, yoyo: true, ease: 'sine.inOut' });
            scene.to('.layer-birds', { x: -110, y: -34, duration: 3.1, repeat: -1, repeatDelay: 10, ease: 'power1.inOut' });
            if (period === 'night') scene.to('.svg-stars circle', { opacity: .25, duration: .85, stagger: .17, repeat: -1, yoyo: true, ease: 'sine.inOut' });
        }
        loginScene.addEventListener('pointermove', function(event) {
            const box = this.getBoundingClientRect();
            const x = (event.clientX - box.left) / box.width - .5;
            const y = (event.clientY - box.top) / box.height - .5;
            const move = function(selector, amount) {
                const layer = loginScene.querySelector(selector);
                if (layer && window.gsap && !reducedMotion) window.gsap.to(layer, { x: x * amount, y: y * amount, duration: .55, overwrite: 'auto' });
            };
            move('.layer-building', 4); move('.layer-trees', 5); move('.layer-clouds', 2); move('.layer-sky', 1);
        });
        loginScene.addEventListener('pointerleave', function() { if (window.gsap) window.gsap.to('.layer-building, .layer-trees, .layer-clouds, .layer-sky', { x: 0, y: 0, duration: .7, overwrite: 'auto' }); });
        if (window.countUp && window.countUp.CountUp && !reducedMotion) document.querySelectorAll('.countup').forEach(function(el) {
            const count = new window.countUp.CountUp(el, Number(el.dataset.count), { duration: 1.8, separator: ',', suffix: el.dataset.count === '24' ? '/7' : '+' });
            if (!count.error) count.start();
        });
        const loginForm = document.querySelector('.login-card form');
        if (loginForm) loginForm.addEventListener('submit', function() {
            const button = this.querySelector('.login-submit');
            if (!button || !this.checkValidity()) return;
            button.classList.add('is-loading'); button.setAttribute('aria-busy', 'true');
            button.innerHTML = '<i class="fa-solid fa-shield-halved"></i><span>Signing in securely…</span>';
        });
    }

    // Enhanced login interactions layered over the existing authentication form.
    if (loginScene) {
        const motionOff = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const refreshLoginTheme = function() {
            const hour = new Date().getHours();
            const nextTheme = hour < 6 || hour >= 20 ? 'night' : hour < 12 ? 'morning' : hour < 17 ? 'afternoon' : 'evening';
            if (document.body.dataset.timeTheme === nextTheme) return;
            document.body.dataset.timeTheme = nextTheme;
            if (window.gsap && !motionOff) { const night = nextTheme === 'night'; window.gsap.to('.svg-sun', { opacity: night ? 0 : 1, duration: 1 }); window.gsap.to('.svg-moon, .svg-stars', { opacity: night ? 1 : 0, duration: 1 }); window.gsap.to('.lamp-glow', { opacity: night ? .34 : 0, duration: 1 }); }
        };
        refreshLoginTheme(); window.setInterval(refreshLoginTheme, 60000);
        if (window.gsap && !motionOff) {
            const entrance = window.gsap.timeline({ defaults: { ease: 'power3.out' } });
            entrance.fromTo('.hostel-illustration', { opacity: 0, y: 24 }, { opacity: 1, y: 0, duration: .8 })
                .fromTo('.login-stat, .announcement-panel', { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: .45, stagger: .07 }, '>-0.05')
                .fromTo('.login-card', { opacity: 0, y: 34 }, { opacity: 1, y: 0, duration: .72 }, '<.08')
                .to('.login-submit', { boxShadow: '0 0 0 6px rgba(6,182,212,.10), 0 16px 30px rgba(6,182,212,.35)', duration: .5, yoyo: true, repeat: 1 });
        }
        document.querySelectorAll('.login-stat').forEach(function(card) { card.classList.add('is-floating'); });
        const messages = ['Welcome to HostelHub — your portal is ready.', 'Room allocations are updated in real time.', 'Need help? Your warden is just one click away.'];
        const announcement = document.querySelector('.announcement-message'); const dots = document.querySelectorAll('.announcement-dots b'); let messageIndex = 0;
        if (dots.length) dots[0].classList.add('is-active');
        if (announcement) window.setInterval(function() { messageIndex = (messageIndex + 1) % messages.length; const swap = function() { announcement.textContent = messages[messageIndex]; dots.forEach(function(dot, index) { dot.classList.toggle('is-active', index === messageIndex); }); }; if (window.gsap && !motionOff) window.gsap.to(announcement, { opacity: 0, y: -5, duration: .25, onComplete: function() { swap(); window.gsap.to(announcement, { opacity: 1, y: 0, duration: .35 }); } }); else swap(); }, 6000);
        document.querySelectorAll('.login-card input, .login-card select').forEach(function(input) { const group = input.closest('.form-group'); input.addEventListener('focus', function() { if (group) group.classList.add('is-focused'); }); input.addEventListener('blur', function() { if (group) group.classList.toggle('is-focused', Boolean(input.value)); }); });
        document.querySelector('[data-toggle-password]')?.addEventListener('click', function() { if (window.gsap && !motionOff) window.gsap.fromTo(this.querySelector('i'), { rotate: -25, scale: .8 }, { rotate: 0, scale: 1, duration: .3, ease: 'back.out(2)' }); });
        const enhancedForm = document.querySelector('.login-card form');
        if (enhancedForm) enhancedForm.addEventListener('submit', function(event) { const button = this.querySelector('.login-submit'); if (!button || !this.checkValidity() || button.classList.contains('is-loading')) return; event.preventDefault(); button.classList.add('is-loading'); button.setAttribute('aria-busy', 'true'); button.innerHTML = '<i class="fa-solid fa-shield-halved fa-beat"></i><span>Signing in securely...</span>'; window.setTimeout(function() { button.classList.remove('is-loading'); button.classList.add('is-success'); button.innerHTML = '<i class="fa-solid fa-circle-check"></i><span>Verified — continuing</span>'; window.setTimeout(function() { enhancedForm.submit(); }, 420); }, 520); });
    }

    // Workspace controls: one source of truth for theme selection and the AI assistant.
    const appBody = document.querySelector('.app-page');
    const themeMenu = document.querySelector('[data-theme-panel]');
    const allowedThemes = ['ocean', 'emerald', 'lavender', 'sunset', 'midnight', 'slate'];
    const applyWorkspaceTheme = function(theme) {
        if (!appBody) return;
        const selectedTheme = allowedThemes.includes(theme) ? theme : 'ocean';
        appBody.dataset.appTheme = selectedTheme;
        localStorage.setItem('hostelhub-theme', selectedTheme);
        document.querySelectorAll('[data-app-theme]').forEach(function(button) { button.setAttribute('aria-pressed', String(button.dataset.appTheme === selectedTheme)); });
    };
    const toggleThemeMenu = function() {
        if (!themeMenu) return;
        themeMenu.hidden = !themeMenu.hidden;
        if (!themeMenu.hidden) themeMenu.querySelector('[aria-pressed="true"]')?.focus();
    };
    if (appBody) {
        applyWorkspaceTheme(localStorage.getItem('hostelhub-theme') || 'ocean');
        document.querySelectorAll('[data-theme-menu]').forEach(function(button) { button.addEventListener('click', toggleThemeMenu); });
        document.querySelectorAll('[data-app-theme]').forEach(function(button) { button.addEventListener('click', function() { applyWorkspaceTheme(this.dataset.appTheme); themeMenu.hidden = true; }); });
    }
    document.querySelectorAll('.app-sidebar a:not(.sidebar-logout)').forEach(function(link) { link.addEventListener('click', function(event) { if (!window.gsap || event.ctrlKey || event.metaKey) return; event.preventDefault(); const destination = this.href; window.gsap.to('main', { opacity: 0, y: -7, duration: .2, onComplete: function() { window.location.href = destination; } }); }); });
    document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', function() { if (!appBody) return; const collapsed = appBody.classList.toggle('sidebar-collapsed'); this.setAttribute('aria-expanded', String(!collapsed)); if (window.gsap) window.gsap.fromTo('[data-sidebar] a i', { rotate: collapsed ? 0 : -12 }, { rotate: 0, stagger: .025, duration: .25 }); });
    document.querySelectorAll('.app-page button, .app-page .button').forEach(function(button) { button.addEventListener('click', function(event) { if (event.target.closest('[data-ai-close], [data-ai-minimize], [data-ai-maximize]')) return; const ripple = document.createElement('i'); ripple.className = 'ripple'; const rect = button.getBoundingClientRect(); ripple.style.width = ripple.style.height = Math.max(rect.width, rect.height) + 'px'; ripple.style.left = (event.clientX - rect.left - rect.width / 2) + 'px'; ripple.style.top = (event.clientY - rect.top - rect.height / 2) + 'px'; button.appendChild(ripple); ripple.addEventListener('animationend', function() { ripple.remove(); }); }); });
    if (appBody) { const reveal = new IntersectionObserver(function(entries) { entries.forEach(function(entry) { if (entry.isIntersecting) { if (window.gsap) window.gsap.to(entry.target, { opacity: 1, y: 0, duration: .48, ease: 'power3.out' }); else entry.target.classList.add('is-visible'); reveal.unobserve(entry.target); } }); }, { threshold: .12 }); document.querySelectorAll('.app-page .card, .app-page .dashboard-hero, .app-page .chart-grid > *').forEach(function(item) { item.classList.add('scroll-reveal'); reveal.observe(item); }); document.querySelector('main')?.classList.add('page-enter'); }
    const aiPanel = document.querySelector('[data-ai-panel]');
    const aiLauncher = document.querySelector('[data-ai-launcher]');
    if (aiPanel && aiLauncher && !document.querySelector('.dashboard-shell')) {
        const chatMessages = aiPanel.querySelector('[data-ai-messages]'), chatForm = aiPanel.querySelector('[data-ai-form]'), quickActions = aiPanel.querySelector('[data-ai-quick-actions]'), question = aiPanel.querySelector('[data-ai-question]'), chatRole = document.body.dataset.role || 'student';
        const actionMap = { student: ['Fees', 'Complaints', 'Leave', 'Room', 'Mess Menu', 'Notices'], warden: ['Student management', 'Leave approvals', 'Visitors', 'Complaints', 'Reports'], admin: ['Analytics', 'Users', 'Reports', 'Role management', 'System settings'] };
        quickActions.innerHTML = (actionMap[chatRole] || actionMap.student).map(function(label) { return '<button type="button">' + label + '</button>'; }).join('');
        const assistantReply = function(topic) { const replies = { 'Fees':'Open Fees to review payment status, due dates, and downloadable receipts.', 'Complaints':'Complaints shows priority, status, and follow-up updates in one place.', 'Leave':'Use Leave requests to submit a request or check its approval status.', 'Room':'Rooms shows your allocation, availability, and maintenance information.', 'Mess Menu':'The current mess menu and meal timings are available from Notices.', 'Notices':'Open Notices for the latest hostel announcements and circulars.', 'Student management':'Students lets you review profiles, allocations, and records.', 'Leave approvals':'Open Leave requests to review and approve pending requests.', 'Visitors':'Visitors is where you record and approve entries.', 'Reports':'Reports provides live occupancy, finance, visitor, and complaint insights.', 'Analytics':'Reports provides operational analytics and trends.', 'Users':'Use Students and Profile to manage user access and records.', 'Role management':'Role access is managed through the appropriate user profile workflow.', 'System settings':'System settings are available to administrators through the management workspace.' }; return replies[topic] || 'I can help you navigate this workspace.'; };
        const historyKey = 'hostelhub-ai-history-' + chatRole;
        const saveHistory = function() { sessionStorage.setItem(historyKey, JSON.stringify(Array.from(chatMessages.querySelectorAll('.ai-message')).filter(function(node) { return !node.classList.contains('ai-thinking'); }).map(function(node) { return { text: node.querySelector('p')?.textContent || node.textContent, kind: node.classList.contains('user-message') ? 'user-message' : 'ai-message' }; }))); };
        try { const saved = JSON.parse(sessionStorage.getItem(historyKey) || '[]'); if (saved.length) { chatMessages.innerHTML = ''; saved.forEach(function(item) { const node = document.createElement('div'); node.className = 'ai-message ' + item.kind; node.innerHTML = '<p></p><time>This session</time>'; node.querySelector('p').textContent = item.text; chatMessages.appendChild(node); }); } } catch (ignore) {}
        const ask = function(topic) { if (!topic.trim()) return; const request = document.createElement('div'); request.className = 'ai-message user-message'; request.innerHTML = '<p></p><time>Just now</time>'; request.querySelector('p').textContent = topic; chatMessages.appendChild(request); question.value = ''; const thinking = document.createElement('div'); thinking.className = 'ai-message ai-thinking'; thinking.textContent = 'Thinking'; chatMessages.appendChild(thinking); chatMessages.scrollTop = chatMessages.scrollHeight; window.setTimeout(function() { thinking.remove(); const answer = document.createElement('div'); answer.className = 'ai-message'; answer.innerHTML = '<p></p><time>Just now</time>'; answer.querySelector('p').textContent = assistantReply(topic); chatMessages.appendChild(answer); chatMessages.scrollTop = chatMessages.scrollHeight; saveHistory(); }, 520); };
        const openAssistant = function() { aiPanel.hidden = false; aiPanel.classList.remove('is-minimized'); aiLauncher.setAttribute('aria-expanded', 'true'); question.focus(); if (window.gsap) window.gsap.fromTo(aiPanel, { opacity: 0, y: 18, scale: .97 }, { opacity: 1, y: 0, scale: 1, duration: .32, ease: 'power3.out' }); };
        aiLauncher.addEventListener('click', openAssistant); document.querySelectorAll('[data-ai-open]').forEach(function(button) { button.addEventListener('click', openAssistant); });
        chatForm.addEventListener('submit', function(event) { event.preventDefault(); ask(question.value); }); quickActions.addEventListener('click', function(event) { const button = event.target.closest('button'); if (button) ask(button.textContent); });
        aiPanel.querySelector('[data-ai-minimize]')?.addEventListener('click', function() { aiPanel.classList.toggle('is-minimized'); }); aiPanel.querySelector('[data-ai-maximize]')?.addEventListener('click', function() { aiPanel.classList.toggle('is-maximized'); });
        aiPanel.querySelector('[data-ai-close]')?.addEventListener('click', function() { saveHistory(); aiPanel.hidden = true; aiLauncher.setAttribute('aria-expanded', 'false'); });
    }

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(function(el) {
        el.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = text;
            document.body.appendChild(tooltip);
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.top - 30) + 'px';
        });
        el.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) tooltip.remove();
        });
    });
});
