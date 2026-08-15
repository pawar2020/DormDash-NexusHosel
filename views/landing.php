<?php
/**
 * DormDash — Nexus Hostel Management Suite
 * Emergent-grade UI with Ambient Glow, Glassmorphism, and Exact Text/Colors
 */
$isUserLoggedIn = isLoggedIn();
$currentUserRole = $isUserLoggedIn ? getCurrentUserRole() : 'guest';
$currentUserName = $isUserLoggedIn ? ($_SESSION['user']['name'] ?? 'User') : '';
$occupancyRateVal = isset($occupancyRate) ? (float)$occupancyRate : 83.4;
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>DormDash | Nexus Hostel — Management Suite</title>
<style>
    @layer base {
      html, body { margin: 0; padding: 0; scroll-behavior: smooth; background-color: #09090b; color: #f4f4f5; }
      body { overscroll-behavior: none; }
      main > :first-child { margin-top: 0 !important; }
      main > :last-child { margin-bottom: 0 !important; }
    }
    ::-webkit-scrollbar { display: none; }

    /* Custom Emergent Glow Effects */
    .glow-bg {
      position: absolute;
      pointer-events: none;
      border-radius: 9999px;
      filter: blur(120px);
      opacity: 0.5;
    }

    /* Ultra Glassmorphism Engine */
    .glass {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.07) 0%, rgba(255, 255, 255, 0.02) 100%);
      backdrop-filter: blur(24px) saturate(180%);
      -webkit-backdrop-filter: blur(24px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.12);
      box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.15);
      position: relative;
    }

    .glass-strong {
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.75) 0%, rgba(9, 9, 11, 0.85) 100%);
      backdrop-filter: blur(32px) saturate(200%);
      -webkit-backdrop-filter: blur(32px) saturate(200%);
      border: 1px solid rgba(255, 255, 255, 0.15);
      box-shadow: 0 16px 48px 0 rgba(0, 0, 0, 0.5), inset 0 1px 0 0 rgba(255, 255, 255, 0.2);
    }

    .glass-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.01) 100%);
      backdrop-filter: blur(20px) saturate(160%);
      -webkit-backdrop-filter: blur(20px) saturate(160%);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), inset 0 1px 0 0 rgba(255, 255, 255, 0.12);
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .glass-card:hover {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.03) 100%);
      border-color: rgba(6, 182, 212, 0.4);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(6, 182, 212, 0.25), inset 0 1px 0 0 rgba(255, 255, 255, 0.25);
      transform: translateY(-6px);
    }

    .glass-pill {
      background: linear-gradient(135deg, rgba(6, 182, 212, 0.15) 0%, rgba(236, 72, 153, 0.1) 100%);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(6, 182, 212, 0.3);
      box-shadow: 0 4px 20px rgba(6, 182, 212, 0.15), inset 0 1px 0 0 rgba(255, 255, 255, 0.2);
    }

    /* Custom Animations */
    @keyframes breathe {
      0%, 100% { transform: scale(1) translate(0, 0); opacity: 0.4; }
      50% { transform: scale(1.15) translate(3%, 3%); opacity: 0.65; }
    }
    .animate-breathe {
      animation: breathe 10s ease-in-out infinite;
    }

    @keyframes shimmer {
      0% { transform: translateX(-150%) skewX(-25deg); }
      100% { transform: translateX(150%) skewX(-25deg); }
    }
    .shimmer-effect::after {
      content: "";
      position: absolute;
      top: 0; left: 0; width: 45%; height: 100%;
      background: linear-gradient(to right, transparent, rgba(255,255,255,0.4), transparent);
      animation: shimmer 3s infinite;
    }

    /* Entrance Reveal Classes */
    .reveal {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }

    .modal-backdrop {
      background: rgba(9, 9, 11, 0.85);
      backdrop-filter: blur(16px);
    }
</style>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            zinc: {
              950: '#09090b',
              900: '#18181b',
              800: '#27272a',
            }
          }
        }
      }
    }
</script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" referrerpolicy="no-referrer"/>
</head>
<body class="bg-zinc-950 text-zinc-100 font-['Inter'] antialiased min-h-screen relative selection:bg-cyan-500/30 selection:text-cyan-200">

<!-- Global Background Glow Orbs -->
<div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
  <div class="glow-bg bg-cyan-500/30 w-[600px] h-[600px] -top-32 -left-32 animate-breathe"></div>
  <div class="glow-bg bg-pink-500/25 w-[650px] h-[650px] top-1/3 -right-40 animate-breathe" style="animation-delay: -5s;"></div>
  <div class="glow-bg bg-cyan-400/20 w-[500px] h-[500px] -bottom-32 left-1/3 animate-breathe" style="animation-delay: -2.5s;"></div>
</div>

<!-- Header / Navigation -->
<header class="fixed top-0 w-full z-50 glass-strong border-b border-white/10">
<div class="h-16 max-w-7xl mx-auto px-4 md:px-8 flex items-center justify-between">
<a href="<?=APP_URL?>/index.php" class="flex items-center gap-3 cursor-pointer hover:opacity-90 transition-opacity">
  <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-pink-500 grid place-items-center shadow-lg shadow-cyan-500/20">
    <span class="material-symbols-outlined text-[20px] text-zinc-950 font-bold">apartment</span>
  </div>
  <span class="text-xl font-extrabold tracking-tight text-white">Dorm<span class="bg-gradient-to-r from-cyan-400 to-pink-400 bg-clip-text text-transparent">Dash</span></span>
</a>

<nav class="flex items-center gap-6 md:gap-8">
<a class="relative group text-sm font-medium text-zinc-400 hover:text-white transition-colors py-1" href="#modules">
          Explore
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-cyan-400 to-pink-400 transition-all duration-300 group-hover:w-full"></span>
</a>
<button type="button" onclick="triggerSupportAI()" class="relative group text-sm font-medium text-zinc-400 hover:text-white transition-colors py-1 bg-transparent border-0 cursor-pointer">
          Support
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-cyan-400 to-pink-400 transition-all duration-300 group-hover:w-full"></span>
</button>

<?php if($isUserLoggedIn): ?>
<a href="<?=APP_URL?>/index.php?action=dashboard" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20">
<span class="material-symbols-outlined text-[18px]">dashboard</span>
<span>Workspace</span>
</a>
<a href="<?=APP_URL?>/index.php?action=profile" class="w-9 h-9 rounded-full glass border border-white/20 grid place-items-center text-cyan-300 hover:scale-105 transition-transform" title="<?=escapeOutput($currentUserName)?>">
<span class="material-symbols-outlined text-[20px]">person</span>
</a>
<?php else: ?>
<button type="button" onclick="openLoginModal()" class="text-sm font-medium text-zinc-300 hover:text-white transition-colors py-1 bg-transparent border-0 cursor-pointer">
          Sign In
</button>
<button type="button" onclick="openLoginModal()" class="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-400 to-pink-500 grid place-items-center text-zinc-950 shadow-lg shadow-cyan-500/25 hover:scale-110 transition-transform">
<span class="material-symbols-outlined text-[20px]">person</span>
</button>
<?php endif; ?>
</nav>
</div>
</header>

<main class="w-full pt-16 relative z-10">
<div class="flex flex-col w-full text-zinc-100">

<!-- Section 1: Hero -->
<section class="relative px-4 md:px-8 py-16 md:py-28 overflow-hidden">
<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

<!-- Left Content -->
<div class="lg:col-span-7 flex flex-col items-start gap-6">
<div class="reveal" style="transition-delay: 100ms;">
<div class="inline-flex items-center gap-2 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-cyan-300 bg-cyan-500/10 border border-cyan-500/20 rounded-full shadow-inner shadow-cyan-500/10">
<span class="material-symbols-outlined text-[14px] text-cyan-400" style="font-variation-settings: 'FILL' 1;">verified_user</span>
<span>Institutional-Grade Management</span>
</div>
</div>

<h1 class="reveal text-4xl sm:text-6xl lg:text-[72px] font-extrabold tracking-tight text-white leading-[1.08]" style="transition-delay: 200ms;">
  The hostel <span class="bg-gradient-to-r from-cyan-300 via-cyan-400 to-pink-400 bg-clip-text text-transparent italic font-serif">command deck</span> your campus deserves.
</h1>

<p class="reveal text-base md:text-lg text-zinc-400 max-w-xl leading-relaxed" style="transition-delay: 350ms;">
  One workspace for admins, wardens, and students — allocate rooms, log visitors, track fees, and resolve complaints without touching a single spreadsheet.
</p>

<div class="reveal flex flex-wrap gap-4 mt-2" style="transition-delay: 500ms;">
<?php if($isUserLoggedIn): ?>
<a href="<?=APP_URL?>/index.php?action=dashboard" class="relative overflow-hidden bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold text-sm px-7 py-3.5 rounded-full hover:brightness-110 transition-all hover:scale-105 active:scale-95 shadow-xl shadow-cyan-500/25 shimmer-effect flex items-center gap-2">
<span class="relative z-10">Open Command Deck</span>
<span class="material-symbols-outlined text-[18px] relative z-10">arrow_forward</span>
</a>
<?php else: ?>
<button type="button" onclick="openLoginModal('admin')" class="relative overflow-hidden bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold text-sm px-7 py-3.5 rounded-full hover:brightness-110 transition-all hover:scale-105 active:scale-95 shadow-xl shadow-cyan-500/25 shimmer-effect">
<span class="relative z-10">Get started — free demo</span>
</button>
<button type="button" onclick="openLoginModal()" class="glass text-zinc-200 font-semibold text-sm px-7 py-3.5 rounded-full hover:bg-white/10 transition-all hover:scale-105 active:scale-95 border border-white/10">
  Sign in
</button>
<?php endif; ?>
</div>

<div class="reveal grid grid-cols-3 gap-4 mt-8 w-full max-w-2xl" style="transition-delay: 650ms;">
<div class="glass-card p-4 rounded-2xl cursor-default">
<span class="font-['JetBrains_Mono'] text-xs text-cyan-400">01</span>
<div class="font-bold text-white mt-1 text-base">Roles</div>
<div class="text-xs text-zinc-400">Admin · Warden · Student</div>
</div>
<div class="glass-card p-4 rounded-2xl cursor-default">
<span class="font-['JetBrains_Mono'] text-xs text-cyan-400">02</span>
<div class="font-bold text-white mt-1 text-base">Modules</div>
<div class="text-xs text-zinc-400">7 Integrated</div>
</div>
<div class="glass-card p-4 rounded-2xl cursor-default">
<span class="font-['JetBrains_Mono'] text-xs text-cyan-400">03</span>
<div class="font-bold text-white mt-1 text-base">Setup</div>
<div class="text-xs text-zinc-400">Zero manual entry</div>
</div>
</div>
</div>

<!-- Right Visual Snapshot -->
<div class="lg:col-span-5 relative group perspective-1000">
<div class="reveal aspect-[4/5] rounded-[32px] overflow-hidden glass relative shadow-2xl transition-transform duration-500 ease-out border border-white/15 group-hover:border-cyan-500/30 group-hover:shadow-[0_0_40px_rgba(6,182,212,0.25)]" id="parallax-container" style="transition-delay: 350ms;">
<img alt="DormDash Campus" class="w-full h-full object-cover grayscale-[0.25] brightness-75 group-hover:scale-105 group-hover:grayscale-0 transition-all duration-1000" src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1200&q=80"/>

<!-- Glassmorphic Snapshot Card -->
<div class="absolute bottom-6 left-6 right-6 p-6 glass-strong rounded-2xl border border-white/15 shadow-2xl">
<div class="flex flex-col gap-2">
<div class="flex items-center justify-between">
<span class="font-['JetBrains_Mono'] text-[11px] font-semibold text-cyan-300 uppercase tracking-wider">Live Snapshot</span>
<span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
</div>
<div class="flex items-baseline gap-2">
<span class="text-4xl md:text-5xl font-extrabold text-white tracking-tight" id="occupancy-counter">0.0%</span>
<span class="text-xs text-zinc-400">room occupancy · Block A + B</span>
</div>
<div class="w-full h-2 bg-white/10 rounded-full overflow-hidden mt-1">
<div class="h-full bg-gradient-to-r from-cyan-400 to-pink-400 w-0 transition-all duration-1500 ease-out relative" id="occupancy-progress">
<div class="absolute right-0 top-0 h-full w-4 bg-white/40 blur-sm"></div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

<!-- Section 2: Modules -->
<section id="modules" class="px-4 md:px-8 py-16 scroll-mt-16 relative">
<div class="max-w-7xl mx-auto">
<div class="flex flex-col mb-12 reveal">
<span class="font-['JetBrains_Mono'] text-xs font-semibold text-cyan-400 uppercase tracking-[0.2em] mb-2">Modules</span>
<h2 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">Everything hostel operations, unified.</h2>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<!-- Module Cards -->
<div onclick="navigateToModule('students')" class="reveal group glass-card p-6 rounded-2xl cursor-pointer" style="transition-delay: 50ms;">
<div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 grid place-items-center mb-6 group-hover:bg-cyan-500/20 group-hover:scale-110 transition-all">
<span class="material-symbols-outlined text-cyan-400 text-[24px]">group</span>
</div>
<h3 class="text-lg font-bold text-white mb-2 group-hover:text-cyan-300 transition-colors flex items-center justify-between">
  <span>Student Records</span>
  <span class="material-symbols-outlined text-[18px] opacity-0 group-hover:opacity-100 transition-opacity text-cyan-400">north_east</span>
</h3>
<p class="text-sm text-zinc-400 mb-6">Central roster with search, filters, and quick edits.</p>
<span class="font-['JetBrains_Mono'] text-[10px] text-zinc-500">01</span>
</div>

<div onclick="navigateToModule('rooms')" class="reveal group glass-card p-6 rounded-2xl cursor-pointer" style="transition-delay: 150ms;">
<div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 grid place-items-center mb-6 group-hover:bg-cyan-500/20 group-hover:scale-110 transition-all">
<span class="material-symbols-outlined text-cyan-400 text-[24px]">apartment</span>
</div>
<h3 class="text-lg font-bold text-white mb-2 group-hover:text-cyan-300 transition-colors flex items-center justify-between">
  <span>Room Allocation</span>
  <span class="material-symbols-outlined text-[18px] opacity-0 group-hover:opacity-100 transition-opacity text-cyan-400">north_east</span>
</h3>
<p class="text-sm text-zinc-400 mb-6">Live occupancy, block/floor breakdown, one-click allocate.</p>
<span class="font-['JetBrains_Mono'] text-[10px] text-zinc-500">02</span>
</div>

<div onclick="navigateToModule('fees')" class="reveal group glass-card p-6 rounded-2xl cursor-pointer" style="transition-delay: 250ms;">
<div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 grid place-items-center mb-6 group-hover:bg-cyan-500/20 group-hover:scale-110 transition-all">
<span class="material-symbols-outlined text-cyan-400 text-[24px]">account_balance_wallet</span>
</div>
<h3 class="text-lg font-bold text-white mb-2 group-hover:text-cyan-300 transition-colors flex items-center justify-between">
  <span>Fee Tracking</span>
  <span class="material-symbols-outlined text-[18px] opacity-0 group-hover:opacity-100 transition-opacity text-cyan-400">north_east</span>
</h3>
<p class="text-sm text-zinc-400 mb-6">Track paid, pending, and overdue payments per student.</p>
<span class="font-['JetBrains_Mono'] text-[10px] text-zinc-500">03</span>
</div>

<div onclick="navigateToModule('visitors')" class="reveal group glass-card p-6 rounded-2xl cursor-pointer" style="transition-delay: 50ms;">
<div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 grid place-items-center mb-6 group-hover:bg-cyan-500/20 group-hover:scale-110 transition-all">
<span class="material-symbols-outlined text-cyan-400 text-[24px]">person_pin_circle</span>
</div>
<h3 class="text-lg font-bold text-white mb-2 group-hover:text-cyan-300 transition-colors flex items-center justify-between">
  <span>Visitor Log</span>
  <span class="material-symbols-outlined text-[18px] opacity-0 group-hover:opacity-100 transition-opacity text-cyan-400">north_east</span>
</h3>
<p class="text-sm text-zinc-400 mb-6">Wardens record entry/exit with relationship &amp; contact.</p>
<span class="font-['JetBrains_Mono'] text-[10px] text-zinc-500">04</span>
</div>

<div onclick="navigateToModule('complaints')" class="reveal group glass-card p-6 rounded-2xl cursor-pointer" style="transition-delay: 150ms;">
<div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 grid place-items-center mb-6 group-hover:bg-cyan-500/20 group-hover:scale-110 transition-all">
<span class="material-symbols-outlined text-cyan-400 text-[24px]">chat_bubble_outline</span>
</div>
<h3 class="text-lg font-bold text-white mb-2 group-hover:text-cyan-300 transition-colors flex items-center justify-between">
  <span>Complaints</span>
  <span class="material-symbols-outlined text-[18px] opacity-0 group-hover:opacity-100 transition-opacity text-cyan-400">north_east</span>
</h3>
<p class="text-sm text-zinc-400 mb-6">Students file, admins resolve — with status timeline.</p>
<span class="font-['JetBrains_Mono'] text-[10px] text-zinc-500">05</span>
</div>

<div onclick="navigateToModule('reports')" class="reveal group glass-card p-6 rounded-2xl cursor-pointer" style="transition-delay: 250ms;">
<div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 grid place-items-center mb-6 group-hover:bg-cyan-500/20 group-hover:scale-110 transition-all">
<span class="material-symbols-outlined text-cyan-400 text-[24px]">bar_chart</span>
</div>
<h3 class="text-lg font-bold text-white mb-2 group-hover:text-cyan-300 transition-colors flex items-center justify-between">
  <span>Reports</span>
  <span class="material-symbols-outlined text-[18px] opacity-0 group-hover:opacity-100 transition-opacity text-cyan-400">north_east</span>
</h3>
<p class="text-sm text-zinc-400 mb-6">Real-time KPIs: occupancy, collections, open tickets.</p>
<span class="font-['JetBrains_Mono'] text-[10px] text-zinc-500">06</span>
</div>
</div>
</div>
</section>

<!-- Section 3: Demo CTA -->
<section class="px-4 md:px-8 py-16 mb-12 reveal">
<div class="max-w-7xl mx-auto">
<div class="relative w-full rounded-3xl overflow-hidden glass p-8 md:p-20 group border border-white/15 shadow-2xl hover:border-cyan-500/30 transition-all">
<div class="glow-bg bg-cyan-500/25 w-96 h-96 -top-24 -right-24 group-hover:scale-125 transition-transform duration-1000"></div>
<div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
<div class="flex flex-col gap-4 max-w-xl">
<h2 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight leading-tight">Try the demo. <br/><span class="bg-gradient-to-r from-cyan-300 to-pink-400 bg-clip-text text-transparent">See allocation in seconds.</span></h2>
<p class="text-base text-zinc-400">
                  Pre-seeded with 20 students, 20 rooms, fee records, complaints, and a visitor log. Experience the seamless workflow of a modern campus.
                </p>
</div>
<div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
<?php if($isUserLoggedIn): ?>
<a href="<?=APP_URL?>/index.php?action=dashboard" class="bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold text-sm px-8 py-4 rounded-full shadow-lg shadow-cyan-500/25 hover:brightness-110 hover:-translate-y-1 transition-all active:scale-95 shimmer-effect relative overflow-hidden flex items-center justify-center gap-2">
<span class="relative z-10">Open Command Deck</span>
</a>
<?php else: ?>
<button type="button" onclick="openLoginModal('admin')" class="bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold text-sm px-8 py-4 rounded-full shadow-lg shadow-cyan-500/25 hover:brightness-110 hover:-translate-y-1 transition-all active:scale-95 shimmer-effect relative overflow-hidden">
<span class="relative z-10">Sign in to demo</span>
</button>
<a href="<?=APP_URL?>/index.php?action=register" class="glass text-white font-semibold text-sm px-8 py-4 rounded-full hover:bg-white/10 hover:-translate-y-1 transition-all active:scale-95 text-center border border-white/10">
                  Register
                </a>
<?php endif; ?>
</div>
</div>
</div>
</div>
</section>

</div>
</main>

<footer class="w-full relative z-10 border-t border-white/10 py-8 bg-zinc-950/80">
<div class="max-w-7xl mx-auto px-4 md:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
<div class="flex items-center gap-2 text-xs text-zinc-500 font-['JetBrains_Mono']">
<span>© 2026 DormDash. Nexus Hostel — Management Suite.</span>
</div>
<div class="flex gap-6">
<a class="text-xs text-zinc-400 hover:text-cyan-300 transition-colors" href="<?=APP_URL?>/index.php?action=terms">Terms of Service</a>
<a class="text-xs text-zinc-400 hover:text-cyan-300 transition-colors" href="<?=APP_URL?>/index.php?action=privacy">Privacy Policy</a>
</div>
</div>
</footer>

<!-- Sign In Modal -->
<div id="login-modal" class="fixed inset-0 z-[100] modal-backdrop flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
  <div class="glass-strong border border-white/15 rounded-3xl p-6 md:p-8 max-w-md w-full shadow-2xl relative transform scale-95 transition-all duration-300" id="login-modal-card">
    <button type="button" onclick="closeLoginModal()" class="absolute top-6 right-6 w-8 h-8 rounded-full bg-white/5 text-zinc-400 hover:text-white flex items-center justify-center transition-colors border border-white/10">
      <span class="material-symbols-outlined text-[18px]">close</span>
    </button>
    
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-pink-500 grid place-items-center text-zinc-950 font-bold">
        <span class="material-symbols-outlined text-[24px]">apartment</span>
      </div>
      <div>
        <h3 class="font-extrabold text-white text-xl">Welcome back.</h3>
        <p class="text-xs text-zinc-400">Access your command deck</p>
      </div>
    </div>

    <?php if(!empty($error)): ?>
    <div class="mb-4 p-3 bg-rose-950/60 border border-rose-500/30 text-rose-300 rounded-2xl text-xs flex items-center gap-2">
      <span class="material-symbols-outlined text-[18px] text-rose-400">error</span>
      <span><?=escapeOutput($error)?></span>
    </div>
    <?php endif; ?>

    <!-- Quick Demo Login Pills -->
    <div class="mb-6 p-3 glass rounded-2xl border border-white/10">
      <span class="font-['JetBrains_Mono'] text-[10px] font-semibold text-cyan-300 uppercase tracking-widest block mb-2">⚡ Quick Demo Credentials</span>
      <div class="grid grid-cols-3 gap-2">
        <button type="button" onclick="fillDemo('admin')" class="px-2 py-1.5 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 rounded-xl text-[11px] font-semibold transition-colors border border-cyan-500/20 text-left flex items-center justify-between">
          <span>Admin</span>
          <span class="material-symbols-outlined text-[14px]">bolt</span>
        </button>
        <button type="button" onclick="fillDemo('warden')" class="px-2 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 rounded-xl text-[11px] font-semibold transition-colors border border-emerald-500/20 text-left flex items-center justify-between">
          <span>Warden</span>
          <span class="material-symbols-outlined text-[14px]">bolt</span>
        </button>
        <button type="button" onclick="fillDemo('student')" class="px-2 py-1.5 bg-pink-500/10 hover:bg-pink-500/20 text-pink-300 rounded-xl text-[11px] font-semibold transition-colors border border-pink-500/20 text-left flex items-center justify-between">
          <span>Student</span>
          <span class="material-symbols-outlined text-[14px]">bolt</span>
        </button>
      </div>
    </div>

    <form method="post" action="<?=APP_URL?>/index.php?action=login" class="flex flex-col gap-4">
      <input type="hidden" name="csrf_token" value="<?=escapeOutput($csrfToken)?>">
      
      <div>
        <label for="modal-role" class="block text-xs font-semibold text-zinc-400 mb-1">Portal Role</label>
        <select id="modal-role" name="role" required class="w-full bg-zinc-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400 transition-colors text-sm">
          <option value="">Choose Portal</option>
          <option value="admin" selected>Administrator</option>
          <option value="warden">Warden</option>
          <option value="student">Student</option>
        </select>
      </div>

      <div>
        <label for="modal-email" class="block text-xs font-semibold text-zinc-400 mb-1">Email address</label>
        <input type="email" id="modal-email" name="email" required placeholder="admin@hostel.com" class="w-full bg-zinc-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400 transition-colors text-sm"/>
      </div>

      <div>
        <label for="modal-password" class="block text-xs font-semibold text-zinc-400 mb-1">Password</label>
        <input type="password" id="modal-password" name="password" required placeholder="••••••••" class="w-full bg-zinc-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400 transition-colors text-sm"/>
      </div>

      <div class="flex items-center justify-between text-xs">
        <label class="flex items-center gap-2 text-zinc-400 cursor-pointer">
          <input type="checkbox" name="remember" value="1" class="rounded bg-zinc-900 border-white/20 text-cyan-400 focus:ring-0">
          <span>Remember me</span>
        </label>
        <a href="<?=APP_URL?>/index.php?action=forgot-password" class="text-cyan-400 hover:underline">Forgot password?</a>
      </div>

      <button type="submit" class="w-full bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-extrabold py-3 rounded-xl hover:brightness-110 transition-all shadow-lg shadow-cyan-500/25 mt-1">
        Sign in to portal
      </button>
    </form>

    <div class="mt-4 text-center text-xs text-zinc-400 border-t border-white/10 pt-4">
      New to DormDash? <a href="<?=APP_URL?>/index.php?action=register" class="text-cyan-400 font-bold hover:underline">Create Student Account</a>
    </div>
  </div>
</div>

<script>
      const targetOccupancy = <?=$occupancyRateVal?>;

      // 1. Reveal Animations on Scroll
      const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('active');
          }
        });
      }, { threshold: 0.1 });

      document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

      // 2. Occupancy Counter & Progress Animation
      let counterRun = false;
      const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !counterRun) {
            counterRun = true;
            animateValue("occupancy-counter", 0, targetOccupancy, 2000);
            const progress = document.getElementById('occupancy-progress');
            if (progress) progress.style.width = targetOccupancy + '%';
          }
        });
      }, { threshold: 0.3 });

      const counterEl = document.getElementById('occupancy-counter');
      if (counterEl) statsObserver.observe(counterEl);

      function animateValue(id, start, end, duration) {
        const obj = document.getElementById(id);
        if (!obj) return;
        let startTimestamp = null;
        const step = (timestamp) => {
          if (!startTimestamp) startTimestamp = timestamp;
          const progress = Math.min((timestamp - startTimestamp) / duration, 1);
          const current = (progress * (end - start) + start).toFixed(1);
          obj.innerHTML = current + "%";
          if (progress < 1) {
            window.requestAnimationFrame(step);
          }
        };
        window.requestAnimationFrame(step);
      }

      // 3. Parallax Effect for Hero Image
      const heroContainer = document.getElementById('parallax-container');
      if (heroContainer && window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
        document.addEventListener('mousemove', (e) => {
          const { clientX, clientY } = e;
          const { innerWidth, innerHeight } = window;
          const xPos = (clientX / innerWidth - 0.5) * 20;
          const yPos = (clientY / innerHeight - 0.5) * 20;
          
          heroContainer.style.transform = `translate(${xPos}px, ${yPos}px) rotateX(${-yPos/10}deg) rotateY(${xPos/10}deg)`;
        });
        
        document.addEventListener('mouseleave', () => {
          heroContainer.style.transform = `translate(0, 0) rotateX(0) rotateY(0)`;
        });
      }

      // Initial reveal trigger
      window.addEventListener('load', () => {
        document.querySelectorAll('.reveal').forEach(el => {
          const rect = el.getBoundingClientRect();
          if (rect.top < window.innerHeight) {
            el.classList.add('active');
          }
        });
        <?php if(!empty($error)): ?>
        openLoginModal();
        <?php endif; ?>
      });

      // Modal Handlers
      function openLoginModal(defaultRole = 'admin') {
        const modal = document.getElementById('login-modal');
        const modalCard = document.getElementById('login-modal-card');
        const roleSelect = document.getElementById('modal-role');
        if (roleSelect && defaultRole) roleSelect.value = defaultRole;
        if (modal && modalCard) {
          modal.classList.remove('opacity-0', 'pointer-events-none');
          modalCard.classList.remove('scale-95');
          modalCard.classList.add('scale-100');
        }
      }

      function closeLoginModal() {
        const modal = document.getElementById('login-modal');
        const modalCard = document.getElementById('login-modal-card');
        if (modal && modalCard) {
          modal.classList.add('opacity-0', 'pointer-events-none');
          modalCard.classList.remove('scale-100');
          modalCard.classList.add('scale-95');
        }
      }

      function fillDemo(role) {
        const emailInput = document.getElementById('modal-email');
        const passwordInput = document.getElementById('modal-password');
        const roleSelect = document.getElementById('modal-role');
        if (role === 'admin') {
          if (emailInput) emailInput.value = 'admin@hostel.com';
          if (passwordInput) passwordInput.value = 'password123';
          if (roleSelect) roleSelect.value = 'admin';
        } else if (role === 'warden') {
          if (emailInput) emailInput.value = 'warden@hostel.com';
          if (passwordInput) passwordInput.value = 'password123';
          if (roleSelect) roleSelect.value = 'warden';
        } else if (role === 'student') {
          if (emailInput) emailInput.value = 'student@hostel.com';
          if (passwordInput) passwordInput.value = 'password123';
          if (roleSelect) roleSelect.value = 'student';
        }
      }

      function navigateToModule(moduleAction) {
        <?php if($isUserLoggedIn): ?>
        window.location.href = '<?=APP_URL?>/index.php?action=' + moduleAction;
        <?php else: ?>
        openLoginModal('admin');
        <?php endif; ?>
      }

      function triggerSupportAI() {
        openLoginModal();
      }

      // Close modal on background click
      document.getElementById('login-modal')?.addEventListener('click', (e) => {
        if (e.target.id === 'login-modal') closeLoginModal();
      });
</script>
</body>
</html>
