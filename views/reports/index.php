<?php
$title = 'Reports & Insights';
$view = 'reports/index';
ob_start();
?>

<div class="mb-6 border-b border-white/10 pb-4">
    <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400 mb-1">INSIGHTS</div>
    <h1 class="text-3xl font-extrabold text-white tracking-tight">Reports</h1>
</div>

<!-- Top 4 Stat Cards Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <div class="glass-card rounded-2xl p-5 border border-white/10 flex flex-col justify-between">
        <div class="text-[10px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-zinc-400 mb-2">STUDENTS</div>
        <div class="text-3xl font-extrabold text-cyan-400 tracking-tight"><?=(int)($statsData['students'] ?? 20)?></div>
    </div>
    <div class="glass-card rounded-2xl p-5 border border-white/10 flex flex-col justify-between">
        <div class="text-[10px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-zinc-400 mb-2">ROOMS</div>
        <div class="text-3xl font-extrabold text-cyan-400 tracking-tight"><?=(int)($statsData['rooms'] ?? 21)?></div>
    </div>
    <div class="glass-card rounded-2xl p-5 border border-white/10 flex flex-col justify-between">
        <div class="text-[10px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-zinc-400 mb-2">OCCUPANCY</div>
        <div class="text-3xl font-extrabold text-pink-400 tracking-tight"><?=(int)($statsData['occupancy_pct'] ?? 27)?>%</div>
    </div>
    <div class="glass-card rounded-2xl p-5 border border-white/10 flex flex-col justify-between">
        <div class="text-[10px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-zinc-400 mb-2">OPEN TICKETS</div>
        <div class="text-3xl font-extrabold text-pink-400 tracking-tight"><?=(int)($statsData['open_tickets'] ?? 3)?></div>
    </div>
</div>

<!-- Middle Charts Row (Occupancy By Block & Fee Collection) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Occupancy By Block -->
    <div class="glass-card rounded-3xl p-6 border border-white/10 flex flex-col justify-between">
        <div class="mb-4">
            <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-zinc-400 mb-1">OCCUPANCY BY BLOCK</div>
            <h3 class="text-lg font-bold text-white tracking-tight">Capacity vs occupied</h3>
        </div>

        <div class="relative w-full h-64 flex items-end justify-around pb-6 border-b border-white/10">
            <!-- Grid Lines -->
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-20">
                <div class="border-b border-dashed border-white text-[10px] text-zinc-400">36</div>
                <div class="border-b border-dashed border-white text-[10px] text-zinc-400">27</div>
                <div class="border-b border-dashed border-white text-[10px] text-zinc-400">18</div>
                <div class="border-b border-dashed border-white text-[10px] text-zinc-400">9</div>
                <div class="border-b border-solid border-white text-[10px] text-zinc-400">0</div>
            </div>

            <!-- Block A -->
            <div class="relative z-10 flex items-end gap-2 group">
                <!-- Capacity Bar (Cyan) -->
                <div class="w-12 bg-cyan-400 rounded-t-lg transition-all duration-500 hover:brightness-110" style="height: 180px;" title="Capacity: 33"></div>
                <!-- Occupied Bar (Pink) -->
                <div class="w-12 bg-pink-500 rounded-t-lg transition-all duration-500 hover:brightness-110" style="height: 95px;" title="Occupied: 17"></div>
                <!-- Tooltip Popup -->
                <div class="absolute -top-12 left-1/2 -translate-x-1/2 glass-strong border border-cyan-400/40 px-3 py-1.5 rounded-xl text-[11px] font-bold text-white opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-xl">
                    <span class="text-cyan-300">capacity : 33</span> · <span class="text-pink-300">occupied : 17</span>
                </div>
            </div>

            <!-- Block B -->
            <div class="relative z-10 flex items-end gap-2 group">
                <!-- Capacity Bar (Cyan) -->
                <div class="w-12 bg-cyan-400 rounded-t-lg transition-all duration-500 hover:brightness-110" style="height: 165px;" title="Capacity: 30"></div>
                <!-- Occupied Bar (Pink) -->
                <div class="w-12 bg-pink-500 rounded-t-lg transition-all duration-500 hover:brightness-110" style="height: 115px;" title="Occupied: 21"></div>
                <div class="absolute -top-12 left-1/2 -translate-x-1/2 glass-strong border border-cyan-400/40 px-3 py-1.5 rounded-xl text-[11px] font-bold text-white opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-xl">
                    <span class="text-cyan-300">capacity : 30</span> · <span class="text-pink-300">occupied : 21</span>
                </div>
            </div>
        </div>
        <div class="flex justify-around text-xs font-semibold text-zinc-400 mt-3">
            <span>Block A</span>
            <span>Block B</span>
        </div>
    </div>

    <!-- Fee Collection (Donut) -->
    <div class="glass-card rounded-3xl p-6 border border-white/10 flex flex-col justify-between">
        <div class="mb-4">
            <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-zinc-400 mb-1">FEE COLLECTION</div>
            <h3 class="text-lg font-bold text-white tracking-tight">By status (₹)</h3>
        </div>

        <div class="flex items-center justify-center my-4">
            <!-- Donut Circle SVG -->
            <div class="relative w-48 h-48">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                    <!-- Donut Track -->
                    <path class="text-zinc-800" stroke-width="5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <!-- Paid Segment (Emerald 66%) -->
                    <path class="text-emerald-400" stroke-dasharray="66, 100" stroke-width="5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <!-- Pending Segment (Amber 34%) -->
                    <path class="text-amber-400" stroke-dasharray="34, 100" stroke-dashoffset="-66" stroke-width="5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-xs text-zinc-400 uppercase tracking-widest font-bold">TOTAL</span>
                    <span class="text-xl font-extrabold text-white">₹3,75,000</span>
                </div>
            </div>
        </div>

        <!-- Legend Footer -->
        <div class="flex items-center justify-center gap-6 pt-3 border-t border-white/10 text-xs font-medium">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                <span class="text-zinc-300">Paid <strong class="text-white">₹2,50,000</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                <span class="text-zinc-300">Pending <strong class="text-white">₹1,25,000</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Complaint Pipeline Bar Chart -->
<div class="glass-card rounded-3xl p-6 border border-white/10 mb-6">
    <div class="mb-6">
        <div class="text-[11px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-zinc-400 mb-1">COMPLAINT PIPELINE</div>
        <h3 class="text-lg font-bold text-white tracking-tight">By status</h3>
    </div>

    <div class="relative w-full h-56 flex items-end justify-around pb-6 border-b border-white/10">
        <!-- Grid Lines -->
        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-20">
            <div class="border-b border-dashed border-white text-[10px] text-zinc-400">8</div>
            <div class="border-b border-dashed border-white text-[10px] text-zinc-400">6</div>
            <div class="border-b border-dashed border-white text-[10px] text-zinc-400">4</div>
            <div class="border-b border-dashed border-white text-[10px] text-zinc-400">2</div>
            <div class="border-b border-solid border-white text-[10px] text-zinc-400">0</div>
        </div>

        <!-- Open Bar -->
        <div class="relative z-10 flex flex-col items-center group">
            <div class="w-36 md:w-56 bg-cyan-400 rounded-t-xl transition-all duration-500 hover:brightness-110" style="height: 75px;" title="Open: 3"></div>
            <span class="text-xs font-semibold text-zinc-400 mt-3">Open</span>
        </div>

        <!-- Resolved Bar -->
        <div class="relative z-10 flex flex-col items-center group">
            <div class="w-36 md:w-56 bg-cyan-400 rounded-t-xl transition-all duration-500 hover:brightness-110" style="height: 125px;" title="Resolved: 5"></div>
            <span class="text-xs font-semibold text-zinc-400 mt-3">Resolved</span>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require APP_ROOT . '/views/app.php';

