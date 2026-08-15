<?php
$title = 'Terms of Service';
$view = 'legal/terms';
ob_start();
?>

<div class="mb-8 border-b border-white/10 pb-4">
    <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">LEGAL &amp; COMPLIANCE</div>
    <h1 class="text-3xl font-extrabold text-white tracking-tight">Terms of Service</h1>
    <p class="text-xs text-zinc-400 mt-1 font-['JetBrains_Mono']">Last updated: July 2026 · DormDash Management Suite</p>
</div>

<div class="glass-card max-w-4xl mx-auto rounded-3xl p-6 md:p-10 border border-white/10 shadow-2xl space-y-8 text-zinc-300 leading-relaxed text-sm">
    <!-- Section 1 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-pink-400"></span>
            <span>1. Acceptance of Terms</span>
        </h2>
        <p>By accessing or using the DormDash Hostel Management Portal, residents, wardens, and administrators agree to comply with these Terms of Service and all applicable campus accommodation regulations.</p>
    </div>

    <!-- Section 2 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-pink-400"></span>
            <span>2. User Account Responsibilities</span>
        </h2>
        <ul class="list-disc list-inside space-y-1.5 text-zinc-400 pl-4">
            <li>Users are responsible for maintaining the confidentiality of their portal credentials (<code class="text-cyan-300">email</code> and <code class="text-cyan-300">password</code>).</li>
            <li>All activities performed under a user account are the sole responsibility of the account holder.</li>
            <li>Users must immediately report any unauthorized access or security breach to the hostel warden.</li>
        </ul>
    </div>

    <!-- Section 3 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-pink-400"></span>
            <span>3. Hostel Regulations &amp; Conduct</span>
        </h2>
        <div class="mt-3 space-y-3">
            <div class="p-4 bg-white/[0.03] border border-white/10 rounded-2xl">
                <strong class="text-white text-xs uppercase font-['JetBrains_Mono'] block mb-1">Leave Requests</strong>
                <p class="text-xs text-zinc-400">Leave applications must be submitted prior to departure with accurate dates and valid reasons. Students must await warden approval before leaving campus.</p>
            </div>
            <div class="p-4 bg-white/[0.03] border border-white/10 rounded-2xl">
                <strong class="text-white text-xs uppercase font-['JetBrains_Mono'] block mb-1">Visitor Entry</strong>
                <p class="text-xs text-zinc-400">All visitors must be logged at gate entry. Visitors must adhere to visiting hours and check out promptly upon departure.</p>
            </div>
            <div class="p-4 bg-white/[0.03] border border-white/10 rounded-2xl">
                <strong class="text-white text-xs uppercase font-['JetBrains_Mono'] block mb-1">Fee Payments</strong>
                <p class="text-xs text-zinc-400">Hostel accommodation fees must be settled within the designated deadlines to avoid penalty or room reallocation.</p>
            </div>
        </div>
    </div>

    <!-- Section 4 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-pink-400"></span>
            <span>4. System Availability &amp; Modifications</span>
        </h2>
        <p>Hostel administration strives to maintain 24/7 portal availability. Scheduled maintenance windows or feature enhancements will be communicated in advance via portal announcements.</p>
    </div>

    <!-- Section 5 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-pink-400"></span>
            <span>5. Account Termination</span>
        </h2>
        <p>Violation of hostel policies, falsification of leave records, or misuse of portal services may result in suspension of portal privileges and disciplinary action under university guidelines.</p>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';