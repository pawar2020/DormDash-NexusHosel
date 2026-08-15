<?php
$title = 'Rooms Inventory';
$view = 'rooms/index';
ob_start();
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-5">
    <div>
        <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">INVENTORY</div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Rooms</h1>
    </div>
    <?php if (hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?>
        <a href="<?=APP_URL?>/index.php?action=rooms&amp;subaction=add" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20 hover:scale-105 active:scale-95">
            <span class="material-symbols-outlined text-[18px]">add</span>
            <span>+ Add room</span>
        </a>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <?php foreach ($rooms as $r): 
        $capacity = (int)($r['capacity'] ?? 1);
        $occupied = (int)($r['occupied'] ?? 0);
        $pct = min(100, max(0, round(($occupied / max(1, $capacity)) * 100)));
        $occupants = $studentsByRoom[$r['id']] ?? [];
        $block = !empty($r['block_name']) ? $r['block_name'] : 'BLOCK A';
        $floor = 'F' . (substr($r['room_number'], 0, 1) ?: '1');
    ?>
        <div class="glass-card border border-white/10 rounded-3xl p-5 flex flex-col justify-between hover:border-cyan-500/40 hover:shadow-2xl transition-all group relative">
            <div>
                <!-- Top Block & Occupancy Count -->
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-zinc-400"><?=escapeOutput($block)?> · <?=escapeOutput($floor)?></span>
                    <div class="flex items-center gap-1">
                        <span class="font-['JetBrains_Mono'] font-extrabold text-cyan-300 text-sm"><?=$occupied?>/<?=$capacity?></span>
                        <span class="text-[9px] font-bold text-zinc-500 uppercase tracking-widest">OCCUPIED</span>
                    </div>
                </div>

                <!-- Large Room Number -->
                <h2 class="text-3xl font-extrabold text-white tracking-tight my-1"><?=escapeOutput($r['room_number'])?></h2>

                <!-- Occupancy Gradient Progress Bar -->
                <div class="w-full h-1.5 bg-zinc-900/80 rounded-full overflow-hidden my-3 border border-white/5">
                    <div class="h-full bg-gradient-to-r from-cyan-400 to-pink-500 rounded-full transition-all duration-500" style="width: <?=$pct?>%;"></div>
                </div>

                <!-- Allocated Students Roster -->
                <?php if (!empty($occupants)): ?>
                    <div class="space-y-1.5 my-3 pt-1 border-t border-white/5">
                        <?php foreach ($occupants as $st): ?>
                            <div class="flex items-center justify-between text-xs text-zinc-300 hover:text-white transition-colors py-0.5">
                                <span class="truncate font-medium text-xs">
                                    <?=escapeOutput($st['full_name'])?> 
                                    <span class="text-zinc-500 text-[11px]">· <?=escapeOutput($st['department'] ?: $st['course'] ?: 'Student')?></span>
                                </span>
                                <span class="material-symbols-outlined text-[14px] text-zinc-500 group-hover:text-cyan-400 transition-colors">logout</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Allocate Button if Space Available -->
                <?php if ($occupied < $capacity && hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?>
                    <a href="<?=APP_URL?>/index.php?action=students&amp;subaction=add&amp;room_id=<?=(int)$r['id']?>" class="mt-3 w-full py-2.5 rounded-2xl border border-cyan-500/30 text-cyan-300 bg-cyan-500/10 hover:bg-cyan-500/25 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98] shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">person_add</span>
                        <span>ALLOCATE</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Card Bottom Actions (Edit / Delete) -->
            <?php if (hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?>
                <div class="flex items-center justify-end gap-3 pt-3 mt-4 border-t border-white/5">
                    <a href="<?=APP_URL?>/index.php?action=rooms&amp;subaction=edit&amp;id=<?=(int)$r['id']?>" class="text-zinc-500 hover:text-cyan-400 transition-colors p-1" title="Edit Room">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                    </a>
                    <a href="<?=APP_URL?>/index.php?action=rooms&amp;subaction=delete&amp;id=<?=(int)$r['id']?>" onclick="return confirm('Delete room <?=escapeOutput($r['room_number'])?>?')" class="text-zinc-500 hover:text-rose-400 transition-colors p-1" title="Delete Room">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (empty($rooms)): ?>
        <div class="col-span-full text-center py-16 glass-card rounded-3xl">
            <span class="material-symbols-outlined text-5xl text-zinc-600 mb-3">bed</span>
            <p class="text-zinc-400 font-medium">No rooms found in inventory.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';

