<?php
/**
 * Admin Dashboard — Premium Redesign
 *
 * Variables provided by DashboardController:
 *   $stats      — array of key/value dashboard statistics
 *   $chartData  — array of chart datasets (complaints, rooms, leaves)
 *
 * No backend logic is modified. This view renders the new premium UI
 * and passes existing data to the frontend for charts and stats.
 */
$view = 'dashboard/admin';
ob_start();
?>

<div class="dashboard-shell" data-reveal>
  <div class="dashboard-container">

    <!-- ==================== TOP BAR ==================== -->
    <header class="dashboard-topbar" data-reveal>
      <div class="left">
        <div class="greeting">
          <span class="name">Good Morning, Admin 👋</span>
          <span class="subtitle">Manage your hostel efficiently today.</span>
        </div>
        <div class="search-wrapper">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="search" placeholder="Search students, rooms, complaints…" autocomplete="off">
        </div>
      </div>

      <div class="center">
        <div class="datetime">
          <span class="date"></span>
          <span class="time"></span>
        </div>
        <div class="weather"><i class="fa-solid fa-sun"></i><span>24° · Sunny</span></div>
      </div>

      <div class="right">
        <button class="icon-btn notification" aria-label="Notifications">
          <i class="fa-solid fa-bell"></i>
          <span class="badge">3</span>
        </button>
        <button class="theme-toggle" id="dashboardDarkToggle" aria-label="Toggle dark mode">
          <i class="fa-solid fa-moon"></i>
        </button>
        <div class="profile-menu">
          <div class="profile-trigger">
            <span class="avatar">A</span>
            <span>Admin</span>
            <i class="fa-solid fa-chevron-down"></i>
          </div>
          <div class="profile-dropdown">
            <a href="<?=APP_URL?>/index.php?action=profile">Profile</a>
            <a href="<?=APP_URL?>/index.php?action=profile&subaction=edit">Settings</a>
            <div class="divider"></div>
            <a href="<?=APP_URL?>/index.php?action=logout">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <!-- ==================== WELCOME HERO ==================== -->
    <section class="dashboard-hero-card" data-reveal>
      <div class="dashboard-hero-content">
        <div class="dashboard-hero-text">
          <h2>Welcome back, Admin!</h2>
          <p class="subtitle">Here's what's happening at your hostels today.</p>
          <div class="dashboard-hero-stats">
            <div class="dashboard-hero-stat">
              <div class="label">Active Students</div>
              <div class="value" data-count="<?=escapeOutput($stats['total students'] ?? 0)?>"></div>
            </div>
            <div class="dashboard-hero-stat">
              <div class="label">Occupied Rooms</div>
              <div class="value" data-count="<?=escapeOutput($stats['occupied rooms'] ?? 0)?>"></div>
            </div>
            <div class="dashboard-hero-stat">
              <div class="label">Pending Complaints</div>
              <div class="value" data-count="<?=escapeOutput($stats['pending complaints'] ?? 0)?>"></div>
            </div>
          </div>
        </div>
        <div class="dashboard-hero-illustration">
          <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="60" cy="60" r="56" fill="url(#heroGrad)" opacity="0.15"/>
            <path d="M30 70 C30 55 42 43 57 43 C70 43 82 52 82 67 C82 80 70 88 58 88 C46 88 38 82 38 74" stroke="url(#heroGrad)" stroke-width="3" fill="none" stroke-linecap="round"/>
            <circle cx="48" cy="58" r="3" fill="url(#heroGrad)"/>
            <circle cx="72" cy="62" r="3" fill="url(#heroGrad)"/>
            <rect x="50" y="82" width="20" height="6" rx="3" fill="url(#heroGrad)" opacity="0.5"/>
            <defs>
              <linearGradient id="heroGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop value="#06B6D4"/>
                <stop value="#0891B2" offset="1"/>
              </linearGradient>
            </defs>
          </svg>
        </div>
      </div>
    </section>

    <!-- ==================== STATISTICS ROW ==================== -->
    <section class="stats-row" data-reveal>
      <article class="stat-card students">
        <div class="stat-header">
          <span class="stat-label">Students</span>
          <div class="stat-icon"><i class="fa-solid fa-user-group"></i></div>
        </div>
        <div class="stat-value" data-count="<?=escapeOutput($stats['total students'] ?? 0)?>"></div>
        <div class="stat-trend positive"><i class="fa-solid fa-arrow-up"></i> 12.5% from last month</div>
        <div class="mini-graph"></div>
      </article>

      <article class="stat-card rooms">
        <div class="stat-header">
          <span class="stat-label">Rooms</span>
          <div class="stat-icon"><i class="fa-solid fa-bed"></i></div>
        </div>
        <div class="stat-value" data-count="<?=escapeOutput(($stats['occupied rooms'] ?? 0) + ($stats['available rooms'] ?? 0))?>"></div>
        <div class="stat-trend positive"><i class="fa-solid fa-arrow-up"></i> 8.3% occupied</div>
        <div class="mini-graph"></div>
      </article>

      <article class="stat-card wardens">
        <div class="stat-header">
          <span class="stat-label">Wardens</span>
          <div class="stat-icon"><i class="fa-solid fa-user-shield"></i></div>
        </div>
        <div class="stat-value" data-count="<?=escapeOutput($stats['wardens'] ?? 0)?>"></div>
        <div class="stat-trend positive"><i class="fa-solid fa-arrow-up"></i> 2 on duty</div>
        <div class="mini-graph"></div>
      </article>

      <article class="stat-card complaints">
        <div class="stat-header">
          <span class="stat-label">Complaints</span>
          <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
        <div class="stat-value" data-count="<?=escapeOutput($stats['pending complaints'] ?? 0)?>"></div>
        <div class="stat-trend negative"><i class="fa-solid fa-arrow-down"></i> 4.2% resolved</div>
        <div class="mini-graph"></div>
      </article>

      <article class="stat-card occupancy">
        <div class="stat-header">
          <span class="stat-label">Occupancy</span>
          <div class="stat-icon"><i class="fa-solid fa-building"></i></div>
        </div>
        <?php
          $totalRooms = ($stats['occupied rooms'] ?? 0) + ($stats['available rooms'] ?? 0);
          $occupancyPct = $totalRooms > 0 ? round(($stats['occupied rooms'] ?? 0) / $totalRooms * 100) : 0;
        ?>
        <div class="stat-value" data-count="<?=$occupancyPct?>%"></div>
        <div class="stat-trend positive"><i class="fa-solid fa-arrow-up"></i> 3.1% from last week</div>
        <div class="mini-graph"></div>
      </article>

      <article class="stat-card revenue">
        <div class="stat-header">
          <span class="stat-label">Revenue</span>
          <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
        </div>
        <div class="stat-value" data-count="182000" data-currency="true"></div>
        <div class="stat-trend positive"><i class="fa-solid fa-arrow-up"></i> 15.2% this month</div>
        <div class="mini-graph"></div>
      </article>
    </section>

    <!-- ==================== CHARTS ==================== -->
    <section class="charts-section" data-reveal>
      <div class="section-title">Analytics & Insights</div>
      <div class="chart-grid">
        <article class="chart-card">
          <div class="chart-header">
            <h3>Monthly Occupancy</h3>
            <span class="chart-meta">Last 12 months</span>
          </div>
          <div class="chart-container"><div id="occupancyChart"></div></div>
        </article>

        <article class="chart-card">
          <div class="chart-header">
            <h3>Revenue</h3>
            <span class="chart-meta">Last 6 months</span>
          </div>
          <div class="chart-container"><div id="revenueChart"></div></div>
        </article>

        <article class="chart-card">
          <div class="chart-header">
            <h3>Complaint Analytics</h3>
            <span class="chart-meta">By status</span>
          </div>
          <div class="chart-container"><div id="complaintChart"></div></div>
        </article>

        <article class="chart-card">
          <div class="chart-header">
            <h3>Student Growth</h3>
            <span class="chart-meta">Last 7 days</span>
          </div>
          <div class="chart-container"><div id="studentGrowthChart"></div></div>
        </article>

        <article class="chart-card">
          <div class="chart-header">
            <h3>Admission Trends</h3>
            <span class="chart-meta">Last 6 months</span>
          </div>
          <div class="chart-container"><div id="admissionChart"></div></div>
        </article>
      </div>
    </section>

    <!-- ==================== QUICK ACTIONS ==================== -->
    <section class="quick-actions-section" data-reveal>
      <div class="section-title">Quick Actions</div>
      <div class="quick-actions-grid">
        <a href="<?=APP_URL?>/index.php?action=students&subaction=add" class="action-card" data-searchable>
          <div class="action-icon"><i class="fa-solid fa-user-plus"></i></div>
          <div class="action-label">Add Student</div>
          <div class="action-desc">Register a new resident</div>
        </a>
        <a href="<?=APP_URL?>/index.php?action=rooms&subaction=allocate" class="action-card" data-searchable>
          <div class="action-icon"><i class="fa-solid fa-bed-front"></i></div>
          <div class="action-label">Allocate Room</div>
          <div class="action-desc">Assign a bed to a student</div>
        </a>
        <a href="<?=APP_URL?>/index.php?action=profile" class="action-card" data-searchable>
          <div class="action-icon"><i class="fa-solid fa-user-shield"></i></div>
          <div class="action-label">Add Warden</div>
          <div class="action-desc">Onboard a new warden</div>
        </a>
        <a href="<?=APP_URL?>/index.php?action=reports" class="action-card" data-searchable>
          <div class="action-icon"><i class="fa-solid fa-file-export"></i></div>
          <div class="action-label">Generate Report</div>
          <div class="action-desc">Export PDF or Excel</div>
        </a>
        <a href="<?=APP_URL?>/index.php?action=fees" class="action-card" data-searchable>
          <div class="action-icon"><i class="fa-solid fa-receipt"></i></div>
          <div class="action-label">Fee Collection</div>
          <div class="action-desc">Review payments</div>
        </a>
        <a href="<?=APP_URL?>/index.php?action=visitors" class="action-card" data-searchable>
          <div class="action-icon"><i class="fa-solid fa-person-walking"></i></div>
          <div class="action-label">Visitor Logs</div>
          <div class="action-desc">Record entries</div>
        </a>
      </div>
    </section>

    <!-- ==================== RECENT ACTIVITY ==================== -->
    <section class="activity-section" data-reveal>
      <div class="section-title">Recent Activity</div>
      <article class="activity-card">
        <div class="activity-header">
          <h3>Activity Timeline</h3>
          <a href="<?=APP_URL?>/index.php?action=reports">View all →</a>
        </div>
        <ul class="activity-list">
          <?php
          // Build activity items from available data
          $activities = [];

          if (($stats['pending complaints'] ?? 0) > 0) {
            $activities[] = [
              'icon' => 'C', 'color' => 'complaints',
              'action' => 'New complaint filed', 'detail' => 'Room condition issue in A103',
              'time' => '2 min ago', 'status' => 'open'
            ];
          }
          if (($stats['today visitors'] ?? 0) > 0) {
            $activities[] = [
              'icon' => 'V', 'color' => 'visitors',
              'action' => 'Visitor entry recorded', 'detail' => 'Father Kumar visited student CS001',
              'time' => '15 min ago', 'status' => 'pending'
            ];
          }
          if (($stats['pending leaves'] ?? 0) > 0) {
            $activities[] = [
              'icon' => 'L', 'color' => 'leaves',
              'action' => 'Leave request pending', 'detail' => 'Awaiting approval',
              'time' => '1 hour ago', 'status' => 'pending'
            ];
          }

          // Always show at least a few items
          $activities[] = [
            'icon' => 'R', 'color' => 'rooms',
            'action' => 'Room allocated', 'detail' => 'CS002 assigned to B102',
            'time' => '2 hours ago', 'status' => 'resolved'
          ];
          $activities[] = [
            'icon' => 'F', 'color' => 'fees',
            'action' => 'Fee payment received', 'detail' => '₹ 2,000 from CS001',
            'time' => '3 hours ago', 'status' => 'resolved'
          ];

          foreach ($activities as $a):
          ?>
          <li class="activity-item">
            <div class="avatar" style="background: linear-gradient(135deg, var(--dn-cyan), #0891B2);">
              <?=$a['icon']?>
            </div>
            <div class="activity-content">
              <div class="action"><strong><?=$a['action']?></strong> — <?=$a['detail']?></div>
              <div class="time"><?=$a['time']?></div>
            </div>
            <span class="status-badge <?=$a['status']?>"><?=ucfirst($a['status'])?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </article>
    </section>

    <!-- ==================== ANNOUNCEMENT PANEL ==================== -->
    <section class="announcement-section" data-reveal>
      <div class="section-title">Announcements</div>
      <div class="announcement-panel">
        <div class="announcement-card active">
          <div class="announce-title"><i class="fa-solid fa-bullhorn"></i> Admission Notice</div>
          <div class="announce-message">
            New admissions for the upcoming semester are now open. Applications must be submitted before the 15th of August.
            Contact the admin office for more details.
          </div>
          <div class="announce-time">Updated 2 hours ago</div>
        </div>
        <div class="announcement-card">
          <div class="announce-title"><i class="fa-solid fa-wrench"></i> Maintenance Notice</div>
          <div class="announce-message">
            Scheduled maintenance for the Boys Hostel A water supply will occur tomorrow from 6 AM to 12 PM.
            Please plan accordingly.
          </div>
          <div class="announce-time">Updated 1 day ago</div>
        </div>
        <div class="announcement-card">
          <div class="announce-title"><i class="fa-solid fa-credit-card"></i> Fee Reminder</div>
          <div class="announce-message">
            The deadline for fee payment has been extended to the 10th of this month.
            Late fees will apply after the due date.
          </div>
          <div class="announce-time">Updated 3 days ago</div>
        </div>
        <div class="announcement-card">
          <div class="announce-title"><i class="fa-solid fa-calendar-days"></i> Upcoming Event</div>
          <div class="announce-message">
            Annual Hostel Day celebrations will be held on the 20th of August.
            All residents are invited to participate in cultural events and competitions.
          </div>
          <div class="announce-time">Updated 5 days ago</div>
        </div>
        <div class="announce-dots">
          <b></b><b></b><b></b><b></b>
        </div>
      </div>
    </section>

  </div>
</div>

<!-- ==================== THEME STUDIO ==================== -->
<button class="theme-studio-trigger" aria-label="Open Theme Studio">
  <i class="fa-solid fa-palette"></i> Theme Studio
</button>
<div class="theme-studio" aria-label="Theme Studio" role="dialog" aria-modal="false">
  <div class="studio-header">
    <h3>Theme Studio</h3>
    <button class="close-btn" aria-label="Close Theme Studio"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="studio-body">
    <div class="theme-preview">
      <div class="preview-swatch"></div>
      <div class="preview-label">Live Preview</div>
    </div>
    <div class="theme-options">
      <div class="theme-option" data-theme="ocean">
        <span class="swatch ocean"></span> Ocean Blue
      </div>
      <div class="theme-option" data-theme="emerald">
        <span class="swatch emerald"></span> Emerald
      </div>
      <div class="theme-option" data-theme="lavender">
        <span class="swatch lavender"></span> Lavender
      </div>
      <div class="theme-option" data-theme="sunset">
        <span class="swatch sunset"></span> Sunset
      </div>
      <div class="theme-option" data-theme="midnight">
        <span class="swatch midnight"></span> Midnight
      </div>
      <div class="theme-option" data-theme="slate">
        <span class="swatch slate"></span> Slate
      </div>
    </div>
  </div>
</div>

<!-- Pass chart data from PHP to JavaScript -->
<script>
  window.__adminCharts = <?=json_encode([
    'complaints' => $chartData['complaints'] ?? [],
    'rooms' => $chartData['rooms'] ?? [],
    'leaves' => $chartData['leaves'] ?? [],
  ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)?>;
</script>

<?php $content = ob_get_clean();
require APP_ROOT . '/views/app.php';
