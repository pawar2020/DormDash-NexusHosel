<?php
/**
 * Shared Application Layout — DormDash Glassmorphism Suite
 */

// Derive view from URL if not explicitly set
$view = $view ?? (sanitizeInput($_GET['action'] ?? 'dashboard') . '/' . sanitizeInput($_GET['subaction'] ?? 'index'));

$titleMap = [
    'leaves/add' => 'Request Leave',
    'leaves/index' => 'Leave Requests',
    'rooms/index' => 'Rooms Inventory',
    'rooms/add' => 'Add Room',
    'rooms/edit' => 'Edit Room',
    'students/index' => 'Students Roster',
    'students/add' => 'Add Student',
    'students/edit' => 'Edit Student',
    'complaints/index' => 'Complaints & Tickets',
    'complaints/add' => 'File Complaint',
    'reports/index' => 'Reports & Insights',
    'visitors/index' => 'Visitor Logs',
    'visitors/entry' => 'Log Visitor Entry',
    'fees/index' => 'Fees Records',
    'fees/add' => 'Record Fee',
    'profile/index' => 'My Profile'
];
$title = $titleMap[$view] ?? ucwords(str_replace(['-', '/'], ' ', $view));
$message = getMessage();
$csrf = generateCsrfToken();
$base = APP_URL . '/index.php';
$isLoginPage = $view === 'auth/login' || $view === 'home' || $view === 'landing';
$isAdminDashboard = strpos($view ?? '', 'dashboard/admin') === 0;

$userRole = isLoggedIn() ? getCurrentUserRole() : 'guest';
$userName = isLoggedIn() ? ($_SESSION['user']['name'] ?? 'User') : '';

// Form field definitions
$fieldSets = [
    'auth/login' => [
        ['email', 'Email', 'email'],
        ['password', 'Password', 'password']
    ],
    'auth/register' => [
        ['name', 'Name'],
        ['email', 'Email', 'email'],
        ['phone', 'Phone'],
        ['password', 'Password', 'password'],
        ['confirm_password', 'Confirm password', 'password']
    ],
    'auth/forgot-password' => [
        ['email', 'Email', 'email']
    ],
    'auth/reset-password' => [
        ['new_password', 'New password', 'password'],
        ['confirm_password', 'Confirm password', 'password']
    ],
    'students/add' => [
        ['name', 'Name'],
        ['email', 'Email', 'email'],
        ['phone', 'Phone'],
        ['password', 'Password', 'password'],
        ['roll_number', 'Roll / Student ID'],
        ['course', 'Course'],
        ['department', 'Department'],
        ['year_level', 'Year Level', 'select', ['1' => '1st Year', '2' => '2nd Year', '3' => '3rd Year', '4' => '4th Year']],
        ['address', 'Address', 'textarea'],
        ['guardian_name', 'Guardian Name'],
        ['guardian_phone', 'Guardian Phone']
    ],
    'students/edit' => [
        ['full_name', 'Full Name'],
        ['email', 'Email', 'email'],
        ['phone', 'Phone'],
        ['course', 'Course'],
        ['department', 'Department'],
        ['year_level', 'Year Level', 'select', ['1' => '1st Year', '2' => '2nd Year', '3' => '3rd Year', '4' => '4th Year']],
        ['address', 'Address', 'textarea'],
        ['guardian_name', 'Guardian Name'],
        ['guardian_phone', 'Guardian Phone'],
        ['status', 'Status', 'select', ['active' => 'Active', 'inactive' => 'Inactive', 'left' => 'Left']]
    ],
    'rooms/add' => [
        ['room_number', 'Room Number'],
        ['room_type', 'Room Type', 'select', ['single' => 'Single Bed', 'double' => 'Double Bed', 'triple' => 'Triple Bed']],
        ['capacity', 'Capacity', 'number'],
        ['monthly_fee', 'Monthly Rent (₹)', 'number']
    ],
    'rooms/edit' => [
        ['room_number', 'Room Number'],
        ['room_type', 'Room Type', 'select', ['single' => 'Single Bed', 'double' => 'Double Bed', 'triple' => 'Triple Bed']],
        ['capacity', 'Capacity', 'number'],
        ['occupied', 'Occupied Beds', 'number'],
        ['monthly_fee', 'Monthly Rent (₹)', 'number'],
        ['status', 'Status', 'select', ['available' => 'Available', 'occupied' => 'Occupied', 'maintenance' => 'Under Maintenance']]
    ],
    'fees/add' => [
        ['student_id', 'Student', 'select', 'students'],
        ['fee_type', 'Fee Type', 'select', ['semester' => 'Semester Fee', 'monthly' => 'Monthly Rent', 'mess' => 'Mess Charges', 'deposit' => 'Security Deposit']],
        ['amount', 'Amount (₹)', 'number'],
        ['due_date', 'Due Date', 'date']
    ],
    'visitors/entry' => [
        ['visitor_name', 'Visitor Name'],
        ['visitor_phone', 'Phone'],
        ['purpose', 'Purpose', 'textarea']
    ],
    'visitors/record-entry' => [
        ['student_id', 'Student', 'select', 'students'],
        ['visitor_name', 'Visitor Name'],
        ['visitor_phone', 'Phone'],
        ['purpose', 'Purpose', 'textarea']
    ],
    'complaints/add' => [
        ['category', 'Category', 'select', ['room_condition' => 'Room Condition', 'plumbing' => 'Plumbing Issue', 'electrical' => 'Electrical Issue', 'cleanliness' => 'Cleanliness', 'noise' => 'Noise Complaint', 'other' => 'Other']],
        ['title', 'Title'],
        ['description', 'Description', 'textarea'],
        ['priority', 'Priority', 'select', ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High']]
    ],
    'profile/edit' => [
        ['name', 'Name'],
        ['email', 'Email', 'email']
    ],
    'profile/change-password' => [
        ['current_password', 'Current password', 'password'],
        ['new_password', 'New password', 'password'],
        ['confirm_password', 'Confirm password', 'password']
    ]
];

$record = $studentData ?? $roomData ?? $feeData ?? $complaintData ?? $visitorData ?? $userData ?? [];
$rows = $students ?? $rooms ?? $fees ?? $visitors ?? $complaints ?? $reports ?? $activities ?? [];
?>
<!doctype html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=escapeOutput($title)?> | DormDash — Management Suite</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              colors: {
                zinc: { 950: '#09090b', 900: '#18181b', 800: '#27272a' }
              }
            }
          }
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" referrerpolicy="no-referrer">
    
    <link rel="stylesheet" href="<?=APP_URL?>/assets/css/style.css?v=<?=time()?>">
    <?php if($isAdminDashboard): ?><link rel="stylesheet" href="<?=APP_URL?>/assets/css/dashboard-premium.css?v=<?=time()?>"><?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php if($isAdminDashboard): ?><script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script><?php endif; ?>
    
    <style>
        .glow-bg {
          position: absolute; pointer-events: none; border-radius: 9999px; filter: blur(120px); opacity: 0.4;
        }
        .glass {
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.07) 0%, rgba(255, 255, 255, 0.02) 100%);
          backdrop-filter: blur(24px) saturate(180%);
          -webkit-backdrop-filter: blur(24px) saturate(180%);
          border: 1px solid rgba(255, 255, 255, 0.12);
          box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.15);
        }
        .glass-strong {
          background: linear-gradient(135deg, rgba(15, 23, 42, 0.75) 0%, rgba(9, 9, 11, 0.85) 100%);
          backdrop-filter: blur(32px) saturate(200%);
          -webkit-backdrop-filter: blur(32px) saturate(200%);
          border: 1px solid rgba(255, 255, 255, 0.15);
          box-shadow: 0 16px 48px 0 rgba(0, 0, 0, 0.5), inset 0 1px 0 0 rgba(255, 255, 255, 0.2);
        }
        .glass-card {
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.01) 100%);
          backdrop-filter: blur(20px) saturate(160%);
          -webkit-backdrop-filter: blur(20px) saturate(160%);
          border: 1px solid rgba(255, 255, 255, 0.1);
          box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), inset 0 1px 0 0 rgba(255, 255, 255, 0.12);
          transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-card:hover {
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.03) 100%);
          border-color: rgba(6, 182, 212, 0.4);
          box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(6, 182, 212, 0.25), inset 0 1px 0 0 rgba(255, 255, 255, 0.25);
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 font-['Inter'] antialiased min-h-screen relative selection:bg-cyan-500/30 selection:text-cyan-200" data-role="<?=escapeOutput($userRole)?>">

<!-- Background Glow Orbs -->
<div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
  <div class="glow-bg bg-cyan-500/25 w-[600px] h-[600px] -top-32 -left-32 animate-pulse"></div>
  <div class="glow-bg bg-pink-500/20 w-[650px] h-[650px] top-1/3 -right-40 animate-pulse" style="animation-delay: -3s;"></div>
  <div class="glow-bg bg-cyan-400/15 w-[500px] h-[500px] -bottom-32 left-1/3 animate-pulse" style="animation-delay: -1.5s;"></div>
</div>

<nav class="glass-strong border-b border-white/10 relative z-50 flex items-center justify-between px-4 md:px-8 py-3">
    <div class="flex items-center gap-4">
        <a class="nav-brand flex items-center gap-3" href="<?=$base?>?action=dashboard">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-cyan-400 to-pink-500 grid place-items-center text-zinc-950 font-bold shadow-md shadow-cyan-500/20">
                <span class="material-symbols-outlined text-[18px]">apartment</span>
            </div>
            <span class="text-lg font-extrabold tracking-tight text-white">Dorm<span class="bg-gradient-to-r from-cyan-400 to-pink-400 bg-clip-text text-transparent">Dash</span></span>
        </a>
        
        <?php if(isLoggedIn()): ?>
            <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-cyan-300 bg-cyan-500/10 border border-cyan-500/20 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                <span>Portal: <?=escapeOutput($userRole)?></span>
            </span>
        <?php endif; ?>
    </div>

    <?php if(isLoggedIn()): ?>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 text-xs font-semibold text-zinc-300">
                <span class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-400 to-pink-500 text-zinc-950 font-extrabold flex items-center justify-center text-xs">
                    <?=strtoupper(substr($userName, 0, 1))?>
                </span>
                <span class="hidden md:inline text-white"><?=escapeOutput($userName)?></span>
            </div>
            <a href="<?=$base?>?action=logout" class="px-3.5 py-1.5 bg-rose-500/15 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px]">logout</span>
                <span>Sign Out</span>
            </a>
        </div>
    <?php endif ?>
</nav>

<?php if(isLoggedIn()): ?>
<div class="app-shell relative z-10">
<aside class="app-sidebar glass-strong border-r border-white/10" data-sidebar aria-label="Primary navigation">
    <div class="px-4 py-3 border-b border-white/10 mb-2">
        <div class="text-[10px] font-['JetBrains_Mono'] font-bold tracking-widest uppercase text-cyan-400">COMMAND DECK</div>
        <div class="text-xs text-zinc-400 truncate"><?=escapeOutput($userName)?> (<?=escapeOutput($userRole)?>)</div>
    </div>
    
    <a href="<?=$base?>?action=dashboard" class="<?=strpos($view, 'dashboard')===0?'active':''?>"><i class="fa-solid fa-grid-2"></i><span>Dashboard</span></a>
    <?php if(hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?>
        <a href="<?=$base?>?action=students" class="<?=strpos($view, 'students')===0?'active':''?>"><i class="fa-solid fa-user-graduate"></i><span>Students</span></a>
        <a href="<?=$base?>?action=rooms" class="<?=strpos($view, 'rooms')===0?'active':''?>"><i class="fa-solid fa-bed"></i><span>Rooms</span></a>
    <?php endif ?>
    <?php if(hasRole(ROLE_ADMIN)): ?>
        <a href="<?=$base?>?action=fees" class="<?=strpos($view, 'fees')===0?'active':''?>"><i class="fa-solid fa-wallet"></i><span>Fees</span></a>
        <a href="<?=$base?>?action=reports" class="<?=strpos($view, 'reports')===0?'active':''?>"><i class="fa-solid fa-chart-line"></i><span>Reports</span></a>
    <?php endif ?>
    <a href="<?=$base?>?action=visitors" class="<?=strpos($view, 'visitors')===0?'active':''?>"><i class="fa-solid fa-person-walking"></i><span>Visitors</span></a>
    <a href="<?=$base?>?action=complaints" class="<?=strpos($view, 'complaints')===0?'active':''?>"><i class="fa-solid fa-message"></i><span>Complaints</span></a>
    <a href="<?=$base?>?action=leaves" class="<?=strpos($view, 'leaves')===0?'active':''?>"><i class="fa-solid fa-calendar-check"></i><span>Leave Requests</span></a>
    <a href="<?=$base?>?action=profile" class="<?=strpos($view, 'profile')===0?'active':''?>"><i class="fa-solid fa-user-gear"></i><span>Profile</span></a>
    
    <div class="mt-auto pt-4 border-t border-white/10">
        <a class="sidebar-logout text-rose-400 hover:text-rose-300" href="<?=$base?>?action=logout"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Sign Out</span></a>
    </div>
</aside>
<?php endif; ?>

<main class="relative z-10 px-4 md:px-8 py-6 max-w-7xl mx-auto">
    <?php if(!$isLoginPage && !$isAdminDashboard && !in_array($view, ['rooms/index', 'reports/index', 'complaints/index', 'leaves/add', 'leaves/index', 'auth/register', 'visitors/index', 'legal/privacy', 'legal/terms', 'students/add', 'students/edit'])): ?>
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/10 pb-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight bg-gradient-to-r from-white via-zinc-200 to-zinc-400 bg-clip-text text-transparent"><?=escapeOutput($title)?></h1>
            <p class="text-xs text-zinc-400 mt-1">Manage <?=escapeOutput(strtolower($title))?> records for your campus command deck.</p>
        </div>
        <?php if((strpos($view, 'students') === 0 || $title === 'Students Index' || ($_GET['action'] ?? '') === 'students') && hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?>
            <a href="<?=$base?>?action=students&subaction=add" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20 hover:scale-105 active:scale-95">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>+ Add Student</span>
            </a>
        <?php elseif($view === 'rooms/index'): ?>
            <a href="<?=$base?>?action=rooms&subaction=add" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20">+ Add Room</a>
        <?php elseif($view === 'fees/index'): ?>
            <a href="<?=$base?>?action=fees&subaction=add" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20">+ Record Fee</a>
        <?php elseif($view === 'complaints/index'): ?>
            <a href="<?=$base?>?action=complaints&subaction=add" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20">+ File Complaint</a>
        <?php elseif($view === 'visitors/index'): ?>
            <a href="<?=$base?>?action=visitors&subaction=entry" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20">+ Log Visitor Entry</a>
        <?php elseif($view === 'leaves/index'): ?>
            <a href="<?=$base?>?action=leaves&subaction=add" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-cyan-500/20">+ Apply Leave</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <div class="mb-6 p-4 bg-rose-950/60 border border-rose-500/30 text-rose-300 rounded-2xl text-sm flex items-center gap-2 shadow-lg">
            <span class="material-symbols-outlined text-[20px] text-rose-400">error</span>
            <span><?=escapeOutput($error)?></span>
        </div>
    <?php endif ?>

    <?php if(!empty($success)): ?>
        <div class="mb-6 p-4 bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 rounded-2xl text-sm flex items-center gap-2 shadow-lg">
            <span class="material-symbols-outlined text-[20px] text-emerald-400">check_circle</span>
            <span><?=escapeOutput($success)?></span>
        </div>
    <?php endif ?>

    <?php if($message): ?>
        <div class="mb-6 p-4 bg-cyan-950/60 border border-cyan-500/30 text-cyan-300 rounded-2xl text-sm flex items-center gap-2 shadow-lg">
            <span class="material-symbols-outlined text-[20px] text-cyan-400">info</span>
            <span><?=escapeOutput($message['text'])?></span>
        </div>
    <?php endif ?>

    <?php if(!empty($content)): ?>
        <?= $content ?>
    <?php elseif(strpos($view, 'dashboard/') === 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach(($stats ?? []) as $label => $value): ?>
                <div class="glass-card p-6 rounded-2xl">
                    <small class="font-['JetBrains_Mono'] text-xs font-semibold text-cyan-400 uppercase tracking-wider block mb-2"><?=escapeOutput(ucwords(str_replace('_', ' ', $label)))?></small>
                    <div class="text-3xl font-extrabold text-white tracking-tight"><?=escapeOutput((string)$value)?></div>
                    <div class="w-full h-1.5 bg-white/5 rounded-full overflow-hidden mt-4">
                      <div class="h-full bg-gradient-to-r from-cyan-400 to-pink-400 w-3/4"></div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    <?php elseif(isset($fieldSets[$view])): ?>
        <form class="glass-card p-6 md:p-8 rounded-3xl max-w-2xl mx-auto shadow-2xl" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?=escapeOutput($csrf)?>">
            <?php foreach($fieldSets[$view] as $f):
                $key = $f[0];
                $label = $f[1];
                $type = $f[2] ?? 'text';
                $value = $record[$key] ?? '';
            ?>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1.5"><?=escapeOutput($label)?></label>
                    <?php if($type === 'textarea'): ?>
                        <textarea name="<?=escapeOutput($key)?>" rows="4" class="w-full bg-zinc-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400 transition-colors text-sm"><?=escapeOutput($value)?></textarea>
                    <?php elseif($type === 'select'): ?>
                        <?php $options = is_array($f[3] ?? null) ? $f[3] : ($$f[3] ?? []); ?>
                        <select name="<?=escapeOutput($key)?>" class="w-full bg-zinc-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400 transition-colors text-sm">
                            <option value="">-- Select <?=escapeOutput($label)?> --</option>
                            <?php foreach($options as $optValue => $optLabel): ?>
                                <option value="<?=escapeOutput($optValue)?>" <?=($value == $optValue) ? 'selected' : ''?>><?=escapeOutput($optLabel)?></option>
                            <?php endforeach ?>
                        </select>
                    <?php else: ?>
                        <input type="<?=escapeOutput($type)?>" name="<?=escapeOutput($key)?>" value="<?=escapeOutput($value)?>" class="w-full bg-zinc-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400 transition-colors text-sm">
                    <?php endif ?>
                </div>
            <?php endforeach ?>
            <button type="submit" class="w-full bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-extrabold py-3.5 rounded-xl hover:brightness-110 transition-all shadow-lg shadow-cyan-500/25 mt-4 text-sm uppercase tracking-wider">Save Changes</button>
        </form>
    <?php elseif(!empty($record)): ?>
        <div class="glass-card p-6 rounded-3xl overflow-hidden">
            <table class="w-full text-left text-sm">
                <?php foreach($record as $key => $value): 
                    if (in_array(strtolower($key), ['password', 'password_hash', 'reset_token', 'token'])) continue;
                ?>
                    <tr class="border-b border-white/5">
                        <th class="py-3 px-4 font-semibold text-zinc-400 uppercase text-xs tracking-wider w-1/3"><?=escapeOutput(ucwords(str_replace('_', ' ', $key)))?></th>
                        <td class="py-3 px-4 text-white font-medium"><?=escapeOutput((string)$value)?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    <?php else: ?>
        <div class="glass-card p-6 rounded-3xl overflow-hidden">
            <?php if((strpos($view, 'students') === 0 || ($_GET['action'] ?? '') === 'students') && hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?>
                <div class="flex items-center justify-between p-4 border-b border-white/10 mb-4 bg-white/[0.02]">
                    <span class="text-xs font-['JetBrains_Mono'] font-bold uppercase tracking-wider text-cyan-400">STUDENT ROSTER RECORDS</span>
                    <a href="<?=$base?>?action=students&subaction=add" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-cyan-400 to-pink-500 text-zinc-950 font-bold rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-md shadow-cyan-500/20 hover:scale-105 active:scale-95">
                        <span class="material-symbols-outlined text-[16px]">person_add</span>
                        <span>+ Add Student</span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if(!empty($rows)): ?>
                <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/[0.02]">
                            <?php foreach(array_keys($rows[0]) as $key): ?>
                                <th class="py-3 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider"><?=escapeOutput($key)?></th>
                            <?php endforeach ?>
                            <?php if($view === 'visitors/index' && hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?><th class="py-3 px-4 font-semibold text-zinc-400 uppercase text-[11px] tracking-wider">Action</th><?php endif ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rows as $row): ?>
                            <tr class="border-b border-white/5 hover:bg-white/[0.03] transition-colors">
                                <?php foreach($row as $value): ?>
                                    <td class="py-3.5 px-4 text-zinc-200"><?=escapeOutput((string)$value)?></td>
                                <?php endforeach ?>
                                <?php if($view === 'visitors/index' && hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?>
                                    <td class="py-3.5 px-4">
                                        <?php if(($row['status'] ?? '') === 'pending'): ?>
                                            <form method="post" class="inline-form" action="<?=$base?>?action=visitors&subaction=approve&id=<?=(int)$row['id']?>">
                                                <input type="hidden" name="csrf_token" value="<?=escapeOutput(generateCsrfToken())?>">
                                                <button type="submit" class="px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-lg text-xs font-semibold hover:bg-emerald-500/30 transition-colors">Approve</button>
                                            </form>
                                        <?php else: ?>—<?php endif ?>
                                    </td>
                                <?php endif ?>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12 text-zinc-400">
                    <span class="material-symbols-outlined text-4xl text-zinc-600 mb-2">inbox</span>
                    <p class="text-sm font-medium">No records found for this view.</p>
                </div>
            <?php endif ?>
        </div>
    <?php endif ?>
</main>
<?php if(isLoggedIn()): ?></div><?php endif; ?>

<!-- Floating Theme Studio Trigger Button -->
<button id="theme-studio-trigger" onclick="toggleThemeStudio()" class="theme-studio-trigger">
    <span class="material-symbols-outlined text-[18px]">palette</span>
    <span>Theme Studio</span>
</button>

<!-- Interactive Theme Studio Panel -->
<div id="theme-studio-panel" class="theme-studio-panel">
    <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-cyan-400">palette</span>
            <h3 class="font-extrabold text-white text-sm mb-0">Theme &amp; Typography Studio</h3>
        </div>
        <button type="button" onclick="toggleThemeStudio()" class="w-6 h-6 rounded-full bg-white/10 text-zinc-400 hover:text-white flex items-center justify-center">
            <span class="material-symbols-outlined text-[14px]">close</span>
        </button>
    </div>

    <!-- Font Family Selector -->
    <div class="mb-4">
        <label class="block text-[10px] font-['JetBrains_Mono'] font-bold text-zinc-400 uppercase tracking-widest mb-2">Font Style</label>
        <div class="grid grid-cols-2 gap-2">
            <button type="button" onclick="setAppFont('Inter')" class="px-3 py-2 glass rounded-xl text-xs font-semibold text-zinc-300 hover:text-white hover:border-cyan-400 text-left transition-colors border border-white/10">
                <span>Inter SaaS</span>
            </button>
            <button type="button" onclick="setAppFont('Hanken Grotesk')" class="px-3 py-2 glass rounded-xl text-xs font-semibold text-zinc-300 hover:text-white hover:border-cyan-400 text-left transition-colors border border-white/10">
                <span>Hanken Modern</span>
            </button>
            <button type="button" onclick="setAppFont('JetBrains Mono')" class="px-3 py-2 glass rounded-xl text-xs font-semibold text-zinc-300 hover:text-white hover:border-cyan-400 text-left transition-colors border border-white/10 col-span-2">
                <span>JetBrains Tech Mono</span>
            </button>
        </div>
    </div>

    <!-- Color Theme Selector -->
    <div>
        <label class="block text-[10px] font-['JetBrains_Mono'] font-bold text-zinc-400 uppercase tracking-widest mb-2">Accent Palette</label>
        <div class="grid grid-cols-2 gap-2">
            <button type="button" onclick="setAppTheme('cyan')" class="flex items-center gap-2 p-2 glass rounded-xl border border-white/10 text-xs font-semibold text-cyan-300 hover:border-cyan-400 transition-colors">
                <span class="w-4 h-4 rounded-full bg-gradient-to-r from-cyan-400 to-pink-500"></span>
                <span>Cyan &amp; Pink</span>
            </button>
            <button type="button" onclick="setAppTheme('emerald')" class="flex items-center gap-2 p-2 glass rounded-xl border border-white/10 text-xs font-semibold text-emerald-300 hover:border-emerald-400 transition-colors">
                <span class="w-4 h-4 rounded-full bg-gradient-to-r from-emerald-400 to-cyan-500"></span>
                <span>Emerald Mint</span>
            </button>
            <button type="button" onclick="setAppTheme('lavender')" class="flex items-center gap-2 p-2 glass rounded-xl border border-white/10 text-xs font-semibold text-purple-300 hover:border-purple-400 transition-colors">
                <span class="w-4 h-4 rounded-full bg-gradient-to-r from-purple-400 to-pink-500"></span>
                <span>Neon Purple</span>
            </button>
            <button type="button" onclick="setAppTheme('sunset')" class="flex items-center gap-2 p-2 glass rounded-xl border border-white/10 text-xs font-semibold text-amber-300 hover:border-amber-400 transition-colors">
                <span class="w-4 h-4 rounded-full bg-gradient-to-r from-amber-400 to-rose-500"></span>
                <span>Sunset Glow</span>
            </button>
            <button type="button" onclick="setAppTheme('midnight')" class="flex items-center gap-2 p-2 glass rounded-xl border border-white/10 text-xs font-semibold text-indigo-300 hover:border-indigo-400 transition-colors">
                <span class="w-4 h-4 rounded-full bg-gradient-to-r from-indigo-400 to-cyan-400"></span>
                <span>Midnight Glow</span>
            </button>
            <button type="button" onclick="setAppTheme('slate')" class="flex items-center gap-2 p-2 glass rounded-xl border border-white/10 text-xs font-semibold text-zinc-300 hover:border-zinc-400 transition-colors">
                <span class="w-4 h-4 rounded-full bg-gradient-to-r from-zinc-400 to-zinc-600"></span>
                <span>Slate Mono</span>
            </button>
        </div>
    </div>
</div>

<script>
    function toggleThemeStudio() {
        const panel = document.getElementById('theme-studio-panel');
        if (panel) {
            panel.classList.toggle('is-open');
        }
    }

    function setAppFont(fontName) {
        document.body.style.fontFamily = "'" + fontName + "', sans-serif";
        document.documentElement.style.fontFamily = "'" + fontName + "', sans-serif";
        localStorage.setItem('dormdash-font', fontName);
    }

    function setAppTheme(themeKey) {
        document.documentElement.dataset.theme = themeKey;
        document.body.dataset.theme = themeKey;
        localStorage.setItem('dormdash-theme', themeKey);
    }

    // Load saved preferences on init
    window.addEventListener('DOMContentLoaded', () => {
        const savedFont = localStorage.getItem('dormdash-font');
        if (savedFont) setAppFont(savedFont);
        const savedTheme = localStorage.getItem('dormdash-theme');
        if (savedTheme) setAppTheme(savedTheme);
    });
</script>

<script src="<?=APP_URL?>/assets/js/main.js" defer></script>
<?php if($isAdminDashboard): ?><script src="<?=APP_URL?>/assets/js/dashboard-premium.js" defer></script><?php endif; ?>
</body>
</html>
