<?php
$title = 'Request Leave';
$view = 'leaves/add';
ob_start();
?>

<div class="mb-6 border-b border-white/10 pb-4">
    <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">LEAVE MANAGEMENT</div>
    <h1 class="text-3xl font-extrabold text-white tracking-tight">Request Leave</h1>
</div>

<div class="glass-card max-w-2xl mx-auto rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl">
    <form method="post" action="<?=APP_URL?>/index.php?action=leaves&amp;subaction=add" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?=escapeOutput(generateCsrfToken())?>">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="from_date" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">From Date</label>
                <input id="from_date" name="from_date" type="date" min="<?=date('Y-m-d')?>" required class="w-full bg-zinc-900/90 border border-white/15 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400 transition-colors text-sm shadow-inner">
            </div>
            <div>
                <label for="to_date" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">To Date</label>
                <input id="to_date" name="to_date" type="date" min="<?=date('Y-m-d')?>" required class="w-full bg-zinc-900/90 border border-white/15 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400 transition-colors text-sm shadow-inner">
            </div>
        </div>

        <div>
            <label for="reason" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">Reason for Leave</label>
            <textarea id="reason" name="reason" rows="4" required placeholder="Provide details regarding your leave request..." class="w-full bg-zinc-900/90 border border-white/15 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400 transition-colors text-sm shadow-inner"></textarea>
        </div>

        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-extrabold rounded-2xl hover:brightness-110 transition-all shadow-lg shadow-cyan-500/25 text-xs uppercase tracking-wider hover:scale-[1.01] active:scale-[0.99]">
            Submit Leave Request
        </button>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';

