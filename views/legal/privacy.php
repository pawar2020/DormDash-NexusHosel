<?php
$title = 'Privacy Policy';
$view = 'legal/privacy';
ob_start();
?>

<div class="mb-8 border-b border-white/10 pb-4">
    <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">LEGAL &amp; COMPLIANCE</div>
    <h1 class="text-3xl font-extrabold text-white tracking-tight">Privacy Policy</h1>
    <p class="text-xs text-zinc-400 mt-1 font-['JetBrains_Mono']">Last updated: July 2026 · DormDash Management Suite</p>
</div>

<div class="glass-card max-w-4xl mx-auto rounded-3xl p-6 md:p-10 border border-white/10 shadow-2xl space-y-8 text-zinc-300 leading-relaxed text-sm">
    <!-- Section 1 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            <span>1. Information We Collect</span>
        </h2>
        <p class="mb-3">DormDash collects personal information necessary to deliver hostel management services, ensure campus security, and maintain resident welfare:</p>
        <ul class="list-disc list-inside space-y-1.5 text-zinc-400 pl-4 font-['JetBrains_Mono'] text-xs">
            <li><strong class="text-white">Resident Profile:</strong> Full name, student ID, roll number, department, course, email, and contact phone number.</li>
            <li><strong class="text-white">Accommodation Data:</strong> Room number, hostel block allocation, occupancy status, and admission dates.</li>
            <li><strong class="text-white">Access &amp; Security Logs:</strong> Visitor entry records, emergency contacts, and leave request dates.</li>
            <li><strong class="text-white">Service Records:</strong> Complaint tickets, maintenance feedback, and fee payment receipts.</li>
        </ul>
    </div>

    <!-- Section 2 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            <span>2. How We Use Information</span>
        </h2>
        <p class="mb-3">Collected information is strictly used for operational and security purposes within the hostel campus:</p>
        <ul class="list-disc list-inside space-y-1.5 text-zinc-400 pl-4">
            <li>Managing room allocations, bed inventory, and student check-ins.</li>
            <li>Processing leave approvals and logging visitor gate access.</li>
            <li>Resolving maintenance complaints and tracking service ticket progress.</li>
            <li>Generating administrative reports for campus wardens and administration.</li>
        </ul>
    </div>

    <!-- Section 3 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            <span>3. Data Security &amp; Encryption</span>
        </h2>
        <p>We enforce strict technical and organizational safeguards to protect your personal information:</p>
        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-white/[0.03] border border-white/10 rounded-2xl">
                <strong class="text-cyan-300 text-xs uppercase font-['JetBrains_Mono'] block mb-1">Password Hashing</strong>
                <p class="text-xs text-zinc-400">All user passwords are encrypted using industry-standard BCRYPT hashing algorithms before database storage.</p>
            </div>
            <div class="p-4 bg-white/[0.03] border border-white/10 rounded-2xl">
                <strong class="text-pink-300 text-xs uppercase font-['JetBrains_Mono'] block mb-1">Role-Based Access</strong>
                <p class="text-xs text-zinc-400">Access controls ensure students, wardens, and administrators only view data relevant to their role.</p>
            </div>
        </div>
    </div>

    <!-- Section 4 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            <span>4. Third-Party Sharing</span>
        </h2>
        <p>DormDash <strong class="text-white">does not sell, rent, or trade</strong> personal data to any external commercial third parties. Data is shared exclusively with authorized university officials for administrative or emergency purposes.</p>
    </div>

    <!-- Section 5 -->
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            <span>5. Contact &amp; Support</span>
        </h2>
        <p>If you have questions or concerns regarding your privacy or data records, please contact the Hostel Administration Office or email <a href="mailto:admin@hostel.com" class="text-cyan-400 font-['JetBrains_Mono'] underline">admin@hostel.com</a>.</p>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';