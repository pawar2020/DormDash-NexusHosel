<?php
$title = 'Student Portal';
$view = 'dashboard/student';
ob_start();
?>

<div class="mb-6 border-b border-white/10 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">STUDENT PORTAL</div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Welcome, <?=escapeOutput(getCurrentUser()['name'] ?? 'Student')?></h1>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?=APP_URL?>/index.php?action=visitors&amp;subaction=entry" class="px-4 py-2.5 bg-cyan-500/20 hover:bg-cyan-500/35 text-cyan-300 border border-cyan-500/30 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-md shadow-cyan-500/10 hover:scale-105 active:scale-95">
            <span class="material-symbols-outlined text-[16px]">person_add</span>
            <span>+ Log Visitor</span>
        </a>
        <a href="<?=APP_URL?>/index.php?action=leaves&amp;subaction=add" class="px-4 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-xl text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20 hover:scale-105 active:scale-95">
            <span class="material-symbols-outlined text-[16px]">flight_takeoff</span>
            <span>Apply Leave</span>
        </a>
    </div>
</div>

<!-- Stats Overview Grid -->
<?php $icons = ['room' => 'bed', 'pending fees' => 'payments', 'complaints' => 'report_problem', 'leave requests' => 'event_available']; ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <?php foreach ($stats as $label => $value): ?>
        <div class="glass-card rounded-2xl p-5 border border-white/10 flex items-center justify-between">
            <div>
                <small class="font-['JetBrains_Mono'] text-[10px] font-bold text-zinc-400 uppercase tracking-widest block mb-1"><?=escapeOutput($label)?></small>
                <div class="text-2xl font-extrabold text-white tracking-tight"><?=escapeOutput((string)$value)?></div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 grid place-items-center text-cyan-400">
                <span class="material-symbols-outlined text-[20px]"><?=$icons[strtolower($label)] ?? 'analytics'?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Main Detail Grid (Visitor Logs & Semester Progress) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Visitor Logs Card -->
    <div class="glass-card rounded-3xl p-6 border border-white/10 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-3">
            <div>
                <div class="text-[10px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-0.5">SECURITY &amp; ACCESS</div>
                <h3 class="text-lg font-bold text-white tracking-tight">Visitor Requests &amp; Logs</h3>
            </div>
            <a href="<?=APP_URL?>/index.php?action=visitors" class="text-xs font-bold text-cyan-300 hover:text-cyan-200 transition-colors flex items-center gap-1">
                <span>View All Logs</span>
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </a>
        </div>

        <div class="space-y-3 my-2">
            <div class="p-3.5 bg-white/[0.03] border border-white/5 rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-400 to-pink-500 text-zinc-950 font-black grid place-items-center text-xs">V</div>
                    <div>
                        <h4 class="text-sm font-bold text-white">Recent Visitors Roster</h4>
                        <p class="text-xs text-zinc-400 font-['JetBrains_Mono']">Track visitor entries &amp; active passes</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">Active</span>
            </div>
        </div>

        <div class="pt-4 border-t border-white/10 flex items-center justify-between text-xs">
            <span class="text-zinc-400">Need to invite a guest or family member?</span>
            <a href="<?=APP_URL?>/index.php?action=visitors&amp;subaction=entry" class="text-cyan-300 hover:underline font-bold flex items-center gap-1">
                <span>+ Request Pass</span>
            </a>
        </div>
    </div>

    <!-- Progress Checklist -->
    <div class="glass-card rounded-3xl p-6 border border-white/10 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-3">
            <div>
                <div class="text-[10px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-0.5">ACADEMIC &amp; HOSTEL</div>
                <h3 class="text-lg font-bold text-white tracking-tight">Semester Checklist</h3>
            </div>
            <span class="px-2.5 py-1 bg-cyan-500/15 text-cyan-300 border border-cyan-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">On Track</span>
        </div>

        <div class="space-y-4 my-2">
            <div>
                <div class="flex justify-between text-xs font-semibold mb-1.5">
                    <span class="text-zinc-300">Hostel Profile Completion</span>
                    <strong class="text-cyan-400">80%</strong>
                </div>
                <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-cyan-400 to-pink-400 rounded-full" style="width: 80%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-xs font-semibold mb-1.5">
                    <span class="text-zinc-300">Fee Payment Progress</span>
                    <strong class="text-pink-400">65%</strong>
                </div>
                <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-pink-400 to-amber-400 rounded-full" style="width: 65%"></div>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-white/10 flex items-center justify-between text-xs">
            <span class="text-zinc-400">Notice: Mess menu updated for this week.</span>
            <a href="<?=APP_URL?>/index.php?action=complaints&amp;subaction=add" class="text-cyan-300 hover:underline font-bold">Raise Issue</a>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';

