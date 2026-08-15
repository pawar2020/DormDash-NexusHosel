<?php
$title = 'My Profile';
$view = 'profile';
ob_start();
?>

<div class="mb-6 border-b border-white/10 pb-4">
    <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">ACCOUNT SETTINGS</div>
    <h1 class="text-3xl font-extrabold text-white tracking-tight">User Profile</h1>
</div>

<div class="glass-card max-w-2xl mx-auto rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl">
    <div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-6 border-b border-white/10">
        <!-- Avatar Photo or Initial Badge -->
        <div class="relative group">
            <?php if (!empty($userData['photo_path']) && file_exists(APP_ROOT . '/' . $userData['photo_path'])): ?>
                <img src="<?=APP_URL?>/<?=escapeOutput($userData['photo_path'])?>?v=<?=time()?>" alt="Profile Photo" class="w-24 h-24 rounded-full object-cover border-2 border-cyan-400 shadow-xl shadow-cyan-500/20">
            <?php else: ?>
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-cyan-400 via-cyan-500 to-pink-500 text-zinc-950 font-black text-4xl grid place-items-center shadow-xl shadow-cyan-500/20">
                    <?=strtoupper(substr($userData['name'] ?? 'U', 0, 1))?>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center sm:text-left flex-1">
            <h2 class="text-2xl font-extrabold text-white tracking-tight"><?=escapeOutput($userData['name'] ?? 'User')?></h2>
            <p class="text-sm text-zinc-400 font-['JetBrains_Mono'] mt-0.5"><?=escapeOutput($userData['email'] ?? '')?></p>
            
            <div class="mt-3 flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <span class="px-3 py-1 bg-cyan-500/15 text-cyan-300 border border-cyan-500/30 rounded-full text-xs font-extrabold uppercase tracking-wider">
                    Role: <?=escapeOutput(ucfirst($userData['role'] ?? $userRole))?>
                </span>
                <span class="px-3 py-1 bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 rounded-full text-xs font-extrabold uppercase tracking-wider">
                    Status: <?=escapeOutput(ucfirst($userData['status'] ?? 'Active'))?>
                </span>
            </div>

            <!-- Upload Photo Form -->
            <form action="<?=APP_URL?>/index.php?action=profile&amp;subaction=upload-photo" method="post" enctype="multipart/form-data" class="mt-4 flex items-center justify-center sm:justify-start gap-3">
                <input type="hidden" name="csrf_token" value="<?=escapeOutput($csrf)?>">
                <label class="px-4 py-2 bg-cyan-500/20 hover:bg-cyan-500/35 text-cyan-300 border border-cyan-500/30 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-1.5 shadow-md shadow-cyan-500/10 hover:scale-105 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">photo_camera</span>
                    <span>Upload Photo</span>
                    <input type="file" name="profile_photo" accept="image/*" class="hidden" onchange="this.form.submit()">
                </label>
                <span class="text-[11px] text-zinc-400">JPG, PNG or WEBP</span>
            </form>
        </div>
    </div>

    <!-- Profile Details Roster -->
    <div class="space-y-4">
        <div class="flex items-center justify-between py-3 border-b border-white/5 text-sm">
            <span class="font-['JetBrains_Mono'] text-xs font-bold text-zinc-400 uppercase tracking-wider">Full Name</span>
            <span class="text-white font-medium"><?=escapeOutput($userData['name'] ?? '')?></span>
        </div>
        <div class="flex items-center justify-between py-3 border-b border-white/5 text-sm">
            <span class="font-['JetBrains_Mono'] text-xs font-bold text-zinc-400 uppercase tracking-wider">Email Address</span>
            <span class="text-white font-medium font-['JetBrains_Mono']"><?=escapeOutput($userData['email'] ?? '')?></span>
        </div>
        <div class="flex items-center justify-between py-3 border-b border-white/5 text-sm">
            <span class="font-['JetBrains_Mono'] text-xs font-bold text-zinc-400 uppercase tracking-wider">System Role</span>
            <span class="text-cyan-300 font-bold uppercase text-xs tracking-wider"><?=escapeOutput($userData['role'] ?? $userRole)?></span>
        </div>
        <div class="flex items-center justify-between py-3 border-b border-white/5 text-sm">
            <span class="font-['JetBrains_Mono'] text-xs font-bold text-zinc-400 uppercase tracking-wider">Account ID</span>
            <span class="text-zinc-300 font-['JetBrains_Mono'] font-bold">#<?=sprintf('%04d', $userData['id'] ?? 1)?></span>
        </div>
        <div class="flex items-center justify-between py-3 border-b border-white/5 text-sm">
            <span class="font-['JetBrains_Mono'] text-xs font-bold text-zinc-400 uppercase tracking-wider">Member Since</span>
            <span class="text-zinc-400 text-xs"><?=formatDate($userData['created_at'] ?? 'now')?></span>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';