<?php
/**
 * Forgot Password View — DormDash Dark Glass Edition
 */
$view = 'auth/forgot-password';
ob_start();
?>
<div class="max-w-md mx-auto py-8">
    <div class="glass-card p-8 rounded-3xl shadow-2xl relative overflow-hidden">
        <div class="glow-bg bg-cyan-500/20 w-64 h-64 -top-20 -right-20 pointer-events-none"></div>
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-pink-500 grid place-items-center text-zinc-950 font-bold">
                <span class="material-symbols-outlined text-[24px]">key</span>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-white tracking-tight mb-0">Reset Password</h1>
                <p class="text-xs text-zinc-400">Enter your registered email address</p>
            </div>
        </div>

        <form method="post" action="<?=APP_URL?>/index.php?action=forgot-password" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?=escapeOutput(generateCsrfToken())?>">
            
            <div>
                <label for="email" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1">Email Address</label>
                <input type="email" id="email" name="email" required autofocus placeholder="name@example.com" class="w-full bg-zinc-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400 text-sm">
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-extrabold py-3.5 rounded-xl hover:brightness-110 transition-all shadow-lg shadow-cyan-500/25 text-sm uppercase tracking-wider mt-2">
                Send Reset Link
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-zinc-400 border-t border-white/10 pt-4">
            <a href="<?=APP_URL?>/index.php?action=login" class="text-cyan-400 font-bold hover:underline">← Back to Sign In</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require APP_ROOT . '/views/app.php';
