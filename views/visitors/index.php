<?php
$title = 'Visitor Logs';
$view = 'visitors/index';
ob_start();
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
    <div>
        <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">SECURITY &amp; ACCESS</div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Visitor Logs</h1>
    </div>
    <a href="<?=APP_URL?>/index.php?action=visitors&amp;subaction=entry" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20 hover:scale-105 active:scale-95">
        <span class="material-symbols-outlined text-[18px]">person_add</span>
        <span>+ Log New Visitor</span>
    </a>
</div>

<div class="glass-card rounded-3xl p-6 border border-white/10 overflow-hidden shadow-2xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="border-b border-white/10 bg-white/[0.04]">
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Visitor Name</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Visiting Student</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Contact Phone</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Purpose</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Check-in Time</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($visitors as $v): 
                    $st = strtolower($v['status'] ?? 'checked_in');
                    $isCheckIn = in_array($st, ['in_house', 'checked_in', 'active', 'approved']);
                ?>
                    <tr class="border-b border-white/5 hover:bg-white/[0.03] transition-colors">
                        <td class="py-3.5 px-4 font-medium text-white flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-cyan-500/20 text-cyan-300 font-bold flex items-center justify-center text-xs">
                                <?=strtoupper(substr($v['visitor_name'] ?? 'V', 0, 1))?>
                            </span>
                            <span><?=escapeOutput($v['visitor_name'] ?? 'Visitor')?></span>
                        </td>
                        <td class="py-3.5 px-4 text-zinc-200 font-medium">
                            <?=escapeOutput($v['student_name'] ?? 'Student')?>
                            <small class="text-zinc-500 font-['JetBrains_Mono'] block text-[11px]"><?=escapeOutput($v['roll_number'] ?? '')?></small>
                        </td>
                        <td class="py-3.5 px-4 text-zinc-300 font-['JetBrains_Mono'] text-xs"><?=escapeOutput($v['phone'] ?? $v['visitor_phone'] ?? 'N/A')?></td>
                        <td class="py-3.5 px-4 text-zinc-300 text-xs"><?=escapeOutput($v['purpose'] ?? 'Guest Visit')?></td>
                        <td class="py-3.5 px-4 text-zinc-400 text-xs"><?=formatDate($v['in_time'] ?? $v['created_at'] ?? 'now')?></td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border <?=$isCheckIn ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'bg-zinc-500/15 text-zinc-400 border-zinc-500/30'?>">
                                <?=$isCheckIn ? 'Checked In ✓' : 'Checked Out'?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($visitors)): ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-zinc-400 font-medium">No visitor logs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';

