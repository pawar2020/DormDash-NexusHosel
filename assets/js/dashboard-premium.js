/**
 * HostelHub — Premium Admin Dashboard
 * Interactive components: top-bar, stats, charts, actions, activity,
 * announcements, theme studio, AI assistant enhancements.
 */
(function () {
    'use strict';

    /* ============================================================
       UTILITIES
       ============================================================ */
    var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
    var $$ = function (sel, ctx) { return (ctx || document).querySelectorAll(sel); };

    function on(el, evt, fn) {
        if (!el) return;
        el.addEventListener(evt, fn);
    }

    function formatGreeting(hour) {
        if (hour < 5) return 'Good Night';
        if (hour < 12) return 'Good Morning';
        if (hour < 17) return 'Good Afternoon';
        if (hour < 21) return 'Good Evening';
        return 'Good Night';
    }

    /* ============================================================
       TOP BAR — Clock, Date, Greeting, Weather
       ============================================================ */
    function initTopBar() {
        var nameEl = $('.greeting .name');
        var greetingEl = $('.greeting .name');
        var dateEl = $('.datetime .date');
        var timeEl = $('.datetime .time');

        if (!dateEl || !timeEl) return;

        function updateClock() {
            var now = new Date();
            var hour = now.getHours();

            // Greeting
            var greeting = formatGreeting(hour);
            if (greetingEl) greetingEl.textContent = greeting + ', Admin 👋';

            // Date
            if (dateEl) {
                var options = { weekday: 'short', month: 'short', day: 'numeric' };
                dateEl.textContent = now.toLocaleDateString('en-US', options);
            }

            // Time (12h)
            if (timeEl) {
                var h = hour % 12 || 12;
                var m = String(now.getMinutes()).padStart(2, '0');
                var ampm = hour >= 12 ? 'PM' : 'AM';
                timeEl.textContent = h + ':' + m + ' ' + ampm;
            }
        }

        updateClock();
        setInterval(updateClock, 1000);
    }

    /* ============================================================
       WEATHER WIDGET (simulated based on time of day)
       ============================================================ */
    function initWeather() {
        var weatherEl = $('.weather');
        if (!weatherEl) return;

        var hour = new Date().getHours();
        var configs = [
            { from: 5, to: 11, icon: 'fa-sun', temp: '24°', label: 'Sunny' },
            { from: 11, to: 16, icon: 'fa-sun', temp: '31°', label: 'Sunny' },
            { from: 16, to: 20, icon: 'fa-cloud-sun', temp: '27°', label: 'Partly Cloudy' },
            { from: 20, to: 5, icon: 'fa-moon', temp: '21°', label: 'Clear' }
        ];

        var config = configs.find(function (c) {
            if (c.from < c.to) return hour >= c.from && hour < c.to;
            return hour >= c.from || hour < c.to;
        }) || configs[0];

        var icon = weatherEl.querySelector('i');
        if (icon) icon.className = 'fa-solid ' + config.icon;
        weatherEl.innerHTML = '<i class="fa-solid ' + config.icon + '"></i><span>' + config.temp + ' · ' + config.label + '</span>';
    }

    /* ============================================================
       SEARCH BAR
       ============================================================ */
    function initSearch() {
        var input = $('.dashboard-topbar .search-wrapper input');
        if (!input) return;

        on(input, 'input', function () {
            var val = this.value.trim().toLowerCase();
            // Simple client-side filter on visible content
            $$('[data-searchable]').forEach(function (item) {
                var text = item.textContent.toLowerCase();
                item.style.display = text.indexOf(val) === -1 ? 'none' : '';
            });
        });
    }

    /* ============================================================
       PROFILE MENU
       ============================================================ */
    function initProfileMenu() {
        var menu = $('.profile-menu');
        if (!menu) return;

        var trigger = $('.profile-trigger');
        on(trigger, 'click', function (e) {
            e.stopPropagation();
            menu.classList.toggle('open');
        });

        on(document, 'click', function () {
            menu.classList.remove('open');
        });
    }

    /* ============================================================
       DARK MODE TOGGLE
       ============================================================ */
    function initDarkMode() {
        var toggle = $('.dashboard-topbar .theme-toggle');
        if (!toggle) return;

        var isDark = localStorage.getItem('hostelhub-dashboard-dark') === 'true';
        if (isDark) toggle.classList.add('active');

        on(toggle, 'click', function () {
            isDark = !isDark;
            toggle.classList.toggle('active', isDark);
            localStorage.setItem('hostelhub-dashboard-dark', isDark);
        });
    }

    /* ============================================================
       NOTIFICATIONS
       ============================================================ */
    function initNotifications() {
        var btn = $('.icon-btn.notification');
        if (!btn) return;

        on(btn, 'click', function () {
            // In a real app this would open a notifications panel
            btn.querySelector('.badge').style.display = 'none';
            alert('No new notifications');
        });
    }

    /* ============================================================
       STATISTICS — CountUp + Sparkline
       ============================================================ */
    function initStats() {
        if (!window.CountUp) {
            // Fallback: just set the text
            $$('[data-count]').forEach(function (el) {
                el.textContent = el.dataset.count;
            });
            return;
        }

        $$('[data-count]').forEach(function (el) {
            var target = parseFloat(el.dataset.count) || 0;
            var isCurrency = el.dataset.currency === 'true';
            var options = {
                duration: 2,
                separator: ',',
                decimalPlaces: isCurrency ? 0 : 0,
                prefix: isCurrency ? '₹ ' : ''
            };
            var count = new window.CountUp.CountUp(el, target, options);
            if (!count.error) {
                // Delay slightly for stagger effect
                var delay = parseInt(el.dataset.delay || '0', 10);
                setTimeout(function () { count.start(); }, delay);
            }
        });

        // Draw sparkline SVGs inside .mini-graph containers
        $$('.stat-card').forEach(function (card) {
            var graph = card.querySelector('.mini-graph');
            if (!graph) return;
            var data = parseSparklineData(card);
            graph.innerHTML = buildSparklineSVG(data);
        });
    }

    function parseSparklineData(card) {
        // Generate pseudo-random data based on card type for visual variety
        var seed = card.classList.contains('students') ? 1 :
                   card.classList.contains('rooms') ? 2 :
                   card.classList.contains('wardens') ? 3 :
                   card.classList.contains('complaints') ? 4 :
                   card.classList.contains('occupancy') ? 5 : 6;
        var points = [];
        for (var i = 0; i < 10; i++) {
            points.push(Math.sin(i * 0.6 + seed) * 12 + 18 + (Math.random() - 0.5) * 4);
        }
        return points;
    }

    function buildSparklineSVG(data) {
        if (!data || data.length < 2) return '';
        var w = 200, h = 36, pad = 2;
        var min = Math.min.apply(null, data);
        var max = Math.max.apply(null, data);
        var range = max - min || 1;
        var stepX = (w - pad * 2) / (data.length - 1);

        var points = data.map(function (v, i) {
            var x = pad + i * stepX;
            var y = h - pad - ((v - min) / range) * (h - pad * 2);
            return x + ',' + y;
        }).join(' ');

        return '<svg class="sparkline-svg" width="' + w + '" height="' + h + '" viewBox="0 0 ' + w + ' ' + h + '">' +
            '<polyline class="sparkline" points="' + points + '" />' +
            '</svg>';
    }

    /* ============================================================
       CHARTS — ApexCharts
       ============================================================ */
    function initCharts() {
        var chartData = window.__adminCharts || {};

        // Monthly Occupancy (area)
        renderApexChart('occupancyChart', 'area', {
            name: 'Occupancy %',
            data: chartData.occupancy || [65, 70, 72, 68, 75, 78, 82, 80, 85, 83, 88, 90],
            cats: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        });

        // Revenue (bar)
        renderApexChart('revenueChart', 'bar', {
            name: 'Revenue (₹)',
            data: chartData.revenue || [45000, 52000, 48000, 61000, 55000, 67000],
            cats: ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        });

        // Complaint Analytics (donut)
        renderApexChart('complaintChart', 'donut', {
            labels: chartData.complaints ? chartData.complaints.map(function (d) { return d.label; }) : ['Open', 'In Progress', 'Resolved', 'Closed'],
            data: chartData.complaints ? chartData.complaints.map(function (d) { return d.total; }) : [5, 3, 8, 2]
        });

        // Student Growth (line)
        renderApexChart('studentGrowthChart', 'line', {
            name: 'Students',
            data: chartData.students || [120, 135, 150, 165, 180, 195, 210],
            cats: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
        });

        // Admission Trends (bar)
        renderApexChart('admissionChart', 'bar', {
            name: 'Admissions',
            data: chartData.admissions || [12, 18, 15, 22, 19, 25],
            cats: ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        });
    }

    function renderApexChart(id, type, config) {
        var el = document.getElementById(id);
        if (!el) return;

        if (typeof ApexCharts !== 'undefined') {
            try {
                var options = {
                    chart: { type: type, height: 220, toolbar: { show: false }, background: 'transparent' },
                    stroke: { curve: 'smooth', width: 3 },
                    fill: { type: type === 'area' ? 'gradient' : 'solid', gradient: { shade: 'dark', type: 'vertical', stops: [0, 100] }, opacity: 0.85 },
                    colors: ['#06B6D4', '#EC4899', '#10B981', '#F59E0B'],
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom', labels: { colors: '#94A3B8' } },
                    grid: { borderColor: 'rgba(255,255,255,0.08)', strokeDashArray: 2 },
                    xaxis: { categories: config.cats || ['1', '2', '3', '4', '5', '6'], labels: { style: { colors: '#94A3B8', fontSize: '11px' } } },
                    yaxis: { labels: { style: { colors: '#94A3B8', fontSize: '11px' } } }
                };
                if (type === 'donut') {
                    options.labels = config.labels;
                    options.series = config.data;
                } else {
                    options.series = [{ name: config.name, data: config.data }];
                }
                new ApexCharts(el, options).render();
                return;
            } catch (err) {}
        }

        // SVG Fallback Chart Generator
        renderSvgFallbackChart(el, type, config);
    }

    function renderSvgFallbackChart(el, type, config) {
        var data = config.data || [10, 20, 30, 40];
        var cats = config.cats || config.labels || ['A', 'B', 'C', 'D'];
        var w = el.clientWidth || 320;
        var h = 200;
        var max = Math.max.apply(null, data) || 1;
        var min = Math.min.apply(null, data);
        var svg = '';

        if (type === 'area' || type === 'line') {
            var stepX = (w - 40) / (data.length - 1 || 1);
            var pts = data.map(function(v, i) {
                var x = 30 + i * stepX;
                var y = h - 30 - (v / max) * (h - 60);
                return x + ',' + y;
            }).join(' ');

            var areaPts = '30,' + (h - 30) + ' ' + pts + ' ' + (30 + (data.length - 1) * stepX) + ',' + (h - 30);

            svg = '<svg width="100%" height="' + h + '" viewBox="0 0 ' + w + ' ' + h + '" style="overflow:visible;">' +
                '<defs>' +
                  '<linearGradient id="grad_' + el.id + '" x1="0" y1="0" x2="0" y2="1">' +
                    '<stop offset="0%" stop-color="#06b6d4" stop-opacity="0.4"/>' +
                    '<stop offset="100%" stop-color="#ec4899" stop-opacity="0"/>' +
                  '</linearGradient>' +
                '</defs>' +
                '<line x1="30" y1="' + (h-30) + '" x2="' + (w-10) + '" y2="' + (h-30) + '" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>' +
                '<polygon points="' + areaPts + '" fill="url(#grad_' + el.id + ')"/>' +
                '<polyline points="' + pts + '" fill="none" stroke="#06b6d4" stroke-width="3" stroke-linecap="round"/>';

            data.forEach(function(v, i) {
                var x = 30 + i * stepX;
                var y = h - 30 - (v / max) * (h - 60);
                svg += '<circle cx="' + x + '" cy="' + y + '" r="4" fill="#09090b" stroke="#06b6d4" stroke-width="2"/>';
                if (cats[i]) {
                    svg += '<text x="' + x + '" y="' + (h - 10) + '" fill="#a1a1aa" font-size="10" text-anchor="middle" font-family="sans-serif">' + cats[i] + '</text>';
                }
            });
            svg += '</svg>';
        } else if (type === 'bar') {
            var barW = Math.max(16, Math.floor((w - 50) / data.length - 12));
            var gap = Math.floor((w - 40 - barW * data.length) / (data.length + 1));
            svg = '<svg width="100%" height="' + h + '" viewBox="0 0 ' + w + ' ' + h + '">' +
                '<line x1="30" y1="' + (h-30) + '" x2="' + (w-10) + '" y2="' + (h-30) + '" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>';

            data.forEach(function(v, i) {
                var bh = Math.max(6, (v / max) * (h - 60));
                var x = 30 + gap + i * (barW + gap);
                var y = h - 30 - bh;
                svg += '<rect x="' + x + '" y="' + y + '" width="' + barW + '" height="' + bh + '" rx="6" fill="url(#barGrad)"/>';
                svg += '<text x="' + (x + barW/2) + '" y="' + (y - 6) + '" fill="#06b6d4" font-size="10" font-weight="bold" text-anchor="middle">' + v + '</text>';
                if (cats[i]) {
                    svg += '<text x="' + (x + barW/2) + '" y="' + (h - 10) + '" fill="#a1a1aa" font-size="10" text-anchor="middle">' + cats[i] + '</text>';
                }
            });
            svg += '<defs><linearGradient id="barGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#06b6d4"/><stop offset="100%" stop-color="#ec4899"/></linearGradient></defs>';
            svg += '</svg>';
        } else if (type === 'donut') {
            svg = '<div class="flex items-center justify-around h-full py-4">';
            svg += '<div class="relative w-32 h-32 rounded-full border-8 border-cyan-400/40 flex items-center justify-center shadow-lg shadow-cyan-500/20"><div class="text-center"><div class="text-2xl font-extrabold text-white">' + data.reduce(function(a,b){return a+b;},0) + '</div><div class="text-[9px] uppercase tracking-widest text-zinc-400 font-bold">Total</div></div></div>';
            svg += '<div class="space-y-1.5">';
            var colors = ['#f59e0b', '#06b6d4', '#10b981', '#f43f5e'];
            cats.forEach(function(lbl, i) {
                svg += '<div class="flex items-center gap-2 text-xs font-semibold text-zinc-300"><span class="w-3 h-3 rounded-full inline-block" style="background:' + (colors[i%4]) + '"></span><span>' + lbl + ': ' + (data[i]||0) + '</span></div>';
            });
            svg += '</div></div>';
        }

        el.innerHTML = svg;
    }

    /* ============================================================
       QUICK ACTIONS — Ripple effect
       ============================================================ */
    function initQuickActions() {
        $$('.action-card').forEach(function (card) {
            on(card, 'click', function (e) {
                var ripple = document.createElement('span');
                ripple.className = 'ripple';
                var rect = this.getBoundingClientRect();
                var size = Math.max(rect.width, rect.height);
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
                ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
                this.appendChild(ripple);
                ripple.addEventListener('animationend', function () { ripple.remove(); });
            });
        });
    }

    /* ============================================================
       RECENT ACTIVITY — Staggered reveal
       ============================================================ */
    function initActivity() {
        var items = $$('.activity-item');
        if (!items.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry, i) {
                if (entry.isIntersecting) {
                    entry.target.style.transitionDelay = (i * 0.08) + 's';
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        items.forEach(function (item) { observer.observe(item); });
    }

    /* ============================================================
       ANNOUNCEMENT PANEL — Auto-rotating cards
       ============================================================ */
    function initAnnouncements() {
        var cards = $$('.announcement-card');
        var dots = $$('.announce-dots b');
        if (!cards.length) return;

        var current = 0;
        var interval;

        function show(index) {
            cards[current].classList.remove('active');
            dots[current].classList.remove('is-active');
            current = (index + cards.length) % cards.length;
            cards[current].classList.add('active');
            dots[current].classList.add('is-active');
        }

        function next() { show(current + 1); }

        // Dot click
        dots.forEach(function (dot, i) {
            on(dot, 'click', function () {
                clearInterval(interval);
                show(i);
                interval = setInterval(next, 6000);
            });
        });

        // Auto-rotate
        interval = setInterval(next, 6000);
    }

    /* ============================================================
       THEME STUDIO — Floating panel
       ============================================================ */
    function initThemeStudio() {
        var trigger = $('.theme-studio-trigger');
        var panel = $('.theme-studio');
        if (!trigger || !panel) return;

        var isOpen = false;

        on(trigger, 'click', function () {
            isOpen = !isOpen;
            panel.classList.toggle('open', isOpen);
            trigger.querySelector('i').className = isOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-palette';
        });

        // Theme options
        $$('.theme-option').forEach(function (opt) {
            on(opt, 'click', function () {
                var theme = this.dataset.theme;
                $$('.theme-option').forEach(function (o) { o.classList.remove('active'); });
                this.classList.add('active');

                // Apply theme to dashboard
                var dashboard = $('.dashboard-shell');
                if (dashboard) {
                    dashboard.className = 'dashboard-shell theme-' + theme;
                }
                localStorage.setItem('hostelhub-dashboard-theme', theme);
            });
        });

        // Load saved theme
        var saved = localStorage.getItem('hostelhub-dashboard-theme');
        if (saved) {
            var savedOpt = $('.theme-option[data-theme="' + saved + '"]');
            if (savedOpt) {
                savedOpt.classList.add('active');
                var dashboard = $('.dashboard-shell');
                if (dashboard) dashboard.className = 'dashboard-shell theme-' + saved;
            }
        }

        // Close on outside click
        on(document, 'click', function (e) {
            if (!panel.contains(e.target) && !trigger.contains(e.target) && isOpen) {
                isOpen = false;
                panel.classList.remove('open');
                trigger.querySelector('i').className = 'fa-solid fa-palette';
            }
        });
    }

    /* ============================================================
       AI ASSISTANT — Enhanced for admin
       ============================================================ */
    function initAIAssistant() {
        var panel = document.querySelector('[data-ai-panel]');
        var launcher = document.querySelector('[data-ai-launcher]');
        if (!panel || !launcher) return;

        var chatMessages = panel.querySelector('[data-ai-messages]');
        var quickActions = panel.querySelector('[data-ai-quick-actions]');
        var chatForm = panel.querySelector('[data-ai-form]');
        var question = panel.querySelector('[data-ai-question]');

        if (!chatMessages || !quickActions) return;

        // Admin-specific quick actions
        var adminActions = ['Generate Report', 'View Complaints', 'Student Search', 'Analytics Help', 'Fee Summary', 'Room Allocation'];
        quickActions.innerHTML = adminActions.map(function (label) {
            return '<button type="button">' + label + '</button>';
        }).join('');

        // Admin-specific responses
        var adminReplies = {
            'Generate Report': 'I can help generate reports. Navigate to Reports → Export to create PDF or Excel reports for students, rooms, fees, visitors, or complaints.',
            'View Complaints': 'Open Complaints to see all tickets. You can filter by priority, status, or category. Assign tickets to wardens and track resolution.',
            'Student Search': 'Use the Students module to search by name, roll number, or registration number. Each profile shows allocation, fees, and complaint history.',
            'Analytics Help': 'The dashboard provides occupancy, revenue, complaint, and growth analytics. Hover over charts for detailed tooltips.',
            'Fee Summary': 'Open Fees to review payment status, due dates, and generate receipts. Filter by status: pending, paid, overdue, or waived.',
            'Room Allocation': 'Go to Rooms → Allocate to assign students to available beds. The system tracks capacity and prevents overbooking.'
        };

        var historyKey = 'hostelhub-ai-history-admin';

        // Load history
        try {
            var saved = JSON.parse(sessionStorage.getItem(historyKey) || '[]');
            if (saved.length) {
                chatMessages.innerHTML = '';
                saved.forEach(function (item) {
                    var node = document.createElement('div');
                    node.className = 'ai-message ' + item.kind;
                    node.innerHTML = '<p></p><time>This session</time>';
                    node.querySelector('p').textContent = item.text;
                    chatMessages.appendChild(node);
                });
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        } catch (ignore) {}

        function saveHistory() {
            sessionStorage.setItem(historyKey, JSON.stringify(
                Array.from(chatMessages.querySelectorAll('.ai-message'))
                    .filter(function (n) { return !n.classList.contains('ai-thinking'); })
                    .map(function (n) {
                        return {
                            text: n.querySelector('p')?.textContent || n.textContent,
                            kind: n.classList.contains('user-message') ? 'user-message' : 'ai-message'
                        };
                    })
            ));
        }

        function ask(topic) {
            if (!topic.trim()) return;

            var request = document.createElement('div');
            request.className = 'ai-message user-message';
            request.innerHTML = '<p></p><time>Just now</time>';
            request.querySelector('p').textContent = topic;
            chatMessages.appendChild(request);
            question.value = '';

            var thinking = document.createElement('div');
            thinking.className = 'ai-message ai-thinking';
            thinking.innerHTML = '<i></i><i></i><i></i>';
            chatMessages.appendChild(thinking);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            setTimeout(function () {
                thinking.remove();
                var answer = document.createElement('div');
                answer.className = 'ai-message';
                answer.innerHTML = '<p></p><time>Just now</time>';
                answer.querySelector('p').textContent = adminReplies[topic] || 'I can help you manage the hostel system. Try one of the quick actions below.';
                chatMessages.appendChild(answer);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                saveHistory();
            }, 520);
        }

        // Open
        var openAssistant = function () {
            panel.hidden = false;
            panel.classList.remove('is-minimized');
            launcher.setAttribute('aria-expanded', 'true');
            question.focus();
        };

        on(launcher, 'click', openAssistant);
        $$('[data-ai-open]').forEach(function (btn) {
            on(btn, 'click', openAssistant);
        });

        // Form submit
        if (chatForm) {
            on(chatForm, 'submit', function (e) {
                e.preventDefault();
                ask(question.value);
            });
        }

        // Quick action clicks
        on(quickActions, 'click', function (e) {
            var btn = e.target.closest('button');
            if (btn) ask(btn.textContent);
        });

        // Minimize / Maximize / Close
        panel.querySelector('[data-ai-minimize]')?.addEventListener('click', function () {
            panel.classList.toggle('is-minimized');
        });
        panel.querySelector('[data-ai-maximize]')?.addEventListener('click', function () {
            panel.classList.toggle('is-maximized');
        });
        panel.querySelector('[data-ai-close]')?.addEventListener('click', function () {
            saveHistory();
            panel.hidden = true;
            launcher.setAttribute('aria-expanded', 'false');
        });
    }

    /* ============================================================
       SCROLL REVEAL
       ============================================================ */
    function initScrollReveal() {
        var items = $$('.dashboard-shell [data-reveal]');
        if (!items.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        items.forEach(function (item) { observer.observe(item); });
    }

    /* ============================================================
       INITIALIZATION
       ============================================================ */
    function init() {
        initTopBar();
        initWeather();
        initSearch();
        initProfileMenu();
        initDarkMode();
        initNotifications();
        initStats();
        initCharts();
        initQuickActions();
        initActivity();
        initAnnouncements();
        initThemeStudio();
        initAIAssistant();
        initScrollReveal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
