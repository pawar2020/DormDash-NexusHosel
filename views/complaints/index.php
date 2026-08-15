<?php
$title = 'Complaints Management';
$view = 'complaints/index';
ob_start();
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
    <div>
        <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">COMMAND DECK</div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Complaints &amp; Tickets</h1>
    </div>
    <?php if (hasRole(ROLE_STUDENT)): ?>
        <a href="<?=APP_URL?>/index.php?action=complaints&amp;subaction=add" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20 hover:scale-105 active:scale-95">
            <span class="material-symbols-outlined text-[18px]">add_comment</span>
            <span>+ File Complaint</span>
        </a>
    <?php endif; ?>
</div>

<div class="glass-card rounded-3xl p-6 border border-white/10 overflow-hidden shadow-2xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="border-b border-white/10 bg-white/[0.04]">
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Ticket #</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Student</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Category</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Title</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Priority</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Status</th>
                    <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Date</th>
                    <?php if (hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?>
                        <th class="py-3.5 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider text-right">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $c): 
                    $prio = strtolower($c['priority'] ?? 'low');
                    $st = strtolower($c['status'] ?? 'open');
                ?>
                    <tr class="border-b border-white/5 hover:bg-white/[0.03] transition-colors">
                        <td class="py-3.5 px-4 font-['JetBrains_Mono'] font-bold text-cyan-300 text-xs">#<?=sprintf('%04d', $c['id'])?></td>
                        <td class="py-3.5 px-4 text-white font-medium">
                            <?=escapeOutput($c['student_name'] ?? 'Student')?>
                            <small class="text-zinc-500 font-['JetBrains_Mono'] block text-[11px]"><?=escapeOutput($c['roll_number'] ?? '')?></small>
                        </td>
                        <td class="py-3.5 px-4 text-zinc-300 uppercase text-xs font-semibold"><?=escapeOutput(ucwords(str_replace('_', ' ', $c['category'] ?? '')))?></td>
                        <td class="py-3.5 px-4 text-white font-medium max-w-xs truncate"><?=escapeOutput($c['title'] ?? '')?></td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border <?=in_array($prio, ['high','urgent']) ? 'bg-rose-500/15 text-rose-400 border-rose-500/30' : ($prio === 'medium' ? 'bg-amber-500/15 text-amber-300 border-amber-500/30' : 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30')?>">
                                <?=escapeOutput(ucfirst($prio))?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="badge badge-<?=escapeOutput($st)?>">
                                <?=escapeOutput(ucwords(str_replace('_', ' ', $st)))?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-zinc-400 text-xs"><?=formatDate($c['created_at'] ?? 'now')?></td>
                        <?php if (hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?>
                            <td class="py-3.5 px-4 text-right">
                                <?php if (!in_array($st, ['resolved', 'closed'])): ?>
                                    <a href="<?=APP_URL?>/index.php?action=complaints&amp;subaction=resolve&amp;id=<?=(int)$c['id']?>" onclick="return confirm('Mark complaint #<?=(int)$c['id']?> as resolved?')" class="px-3.5 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/35 text-emerald-300 border border-emerald-500/30 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-md shadow-emerald-500/10 hover:scale-105 active:scale-95 cursor-pointer">
                                        <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                        <span>Resolve</span>
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-emerald-400 font-bold text-xs">
                                        <span class="material-symbols-outlined text-[15px]">done_all</span>
                                        <span>Resolved</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($complaints)): ?>
                    <tr>
                        <td colspan="8" class="py-12 text-center text-zinc-400 font-medium">No complaints found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';

