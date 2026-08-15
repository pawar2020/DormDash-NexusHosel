<?php
$title = 'Warden Workspace';
$view = 'dashboard/warden';
ob_start();
?>

<div class="mb-6 border-b border-white/10 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">WARDEN PORTAL</div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Resident Care &amp; Operations Workspace</h1>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?=APP_URL?>/index.php?action=complaints" class="px-4 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-xl text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20 hover:scale-105 active:scale-95">
            <span class="material-symbols-outlined text-[16px]">support_agent</span>
            <span>Review Complaints</span>
        </a>
    </div>
</div>

<!-- Operational Priority Banner -->
<div class="glass-card rounded-2xl p-4 border border-amber-500/30 bg-amber-500/10 mb-6 flex items-center justify-between gap-4 shadow-lg">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-300 grid place-items-center flex-shrink-0">
            <span class="material-symbols-outlined text-[20px]">notifications_active</span>
        </div>
        <div>
            <strong class="text-sm font-bold text-white block">Operational Priority</strong>
            <span class="text-xs text-amber-200">Review pending visitor and leave requests before evening check-in.</span>
        </div>
    </div>
    <a href="<?=APP_URL?>/index.php?action=visitors" class="px-3.5 py-1.5 bg-amber-500/20 hover:bg-amber-500/35 text-amber-300 border border-amber-500/30 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
        Open Queue &rarr;
    </a>
</div>

<!-- Stats Cards Grid -->
<?php $icons = ['active students' => 'groups', 'open complaints' => 'warning', 'visitor requests' => 'badge', 'leave requests' => 'event_available']; ?>
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

<!-- Charts Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Complaint Status Bar Chart -->
    <div class="glass-card rounded-3xl p-6 border border-white/10 flex flex-col justify-between">
        <div class="mb-4">
            <div class="text-[10px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-0.5">SERVICE TICKETS</div>
            <h3 class="text-lg font-bold text-white tracking-tight">Complaint Status</h3>
        </div>
        <div class="relative w-full h-64">
            <canvas id="complaintChart"></canvas>
        </div>
    </div>

    <!-- Room Occupancy Donut Chart -->
    <div class="glass-card rounded-3xl p-6 border border-white/10 flex flex-col justify-between">
        <div class="mb-4">
            <div class="text-[10px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-0.5">INVENTORY</div>
            <h3 class="text-lg font-bold text-white tracking-tight">Room Occupancy</h3>
        </div>
        <div class="relative w-full h-64 flex items-center justify-center">
            <canvas id="roomChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const wardenCharts = <?=json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)?>;
    
    // Complaint Status Bar Chart
    const cCtx = document.getElementById('complaintChart');
    if (cCtx && wardenCharts.complaints) {
        new Chart(cCtx, {
            type: 'bar',
            data: {
                labels: wardenCharts.complaints.map(x => x.label || 'Open'),
                datasets: [{
                    label: 'Complaints',
                    data: wardenCharts.complaints.map(x => x.total),
                    backgroundColor: ['#06b6d4', '#10b981', '#f59e0b', '#f43f5e'],
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#18181b',
                        borderColor: 'rgba(255,255,255,0.15)',
                        borderWidth: 1,
                        titleColor: '#06b6d4',
                        bodyColor: '#ffffff'
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#a1a1aa', font: { family: 'Inter', size: 11, weight: '600' } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.08)' },
                        ticks: { color: '#a1a1aa', font: { family: 'Inter', size: 11 } },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Room Occupancy Donut Chart
    const rCtx = document.getElementById('roomChart');
    if (rCtx && wardenCharts.rooms) {
        new Chart(rCtx, {
            type: 'doughnut',
            data: {
                labels: wardenCharts.rooms.map(x => x.label || 'Available'),
                datasets: [{
                    data: wardenCharts.rooms.map(x => x.total),
                    backgroundColor: ['#06b6d4', '#ec4899', '#10b981', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#d4d4d8', font: { family: 'Inter', size: 12, weight: '500' }, padding: 15 }
                    }
                },
                cutout: '70%'
            }
        });
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';

