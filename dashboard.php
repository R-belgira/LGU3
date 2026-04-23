<?php
// admin/dashboard.php
// FIXED: RESTORED LOGS (Blue Default, Green for Approve, Red for Reject)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Connection & Config
if (!file_exists('../config/db.php')) { die("Database configuration file not found."); }
include('../config/db.php');

// Define Roles and User
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}

$current_user = htmlspecialchars($_SESSION["name"] ?? 'Admin');
$current_role = ucfirst($_SESSION["role"] ?? 'Staff');
$page_title = "Executive Dashboard";

// =========================================================
// DATA FETCHING (SAFE MODE)
// =========================================================

// A. PENDING REPAIRS (Maintenance Alert)
$pending_repairs = 0;
try {
    $repair_res = $conn->query("SELECT COUNT(*) AS total FROM stall_repair_requests WHERE status='Pending'");
    if($repair_res) $pending_repairs = $repair_res->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) { }


// B. SLAUGHTERHOUSE DAILY HEADS
$date_today = date('Y-m-d');
$daily_heads = 0;
$daily_kilos = 0;

try {
    // 1. New Table (slaughter_registration)
    $slaughter_sql = "SELECT SUM(quantity) as total_heads, SUM(total_weight) as total_kilos FROM slaughter_registration WHERE intake_date = '$date_today'";
    $slaughter_res = $conn->query($slaughter_sql);
    if($slaughter_res) {
        $slaughter_data = $slaughter_res->fetch_assoc();
        $daily_heads = $slaughter_data['total_heads'] ?? 0;
        $daily_kilos = $slaughter_data['total_kilos'] ?? 0;
    }
} catch (mysqli_sql_exception $e) {
    // 2. Fallback Table
    try {
        $fallback_sql = "SELECT SUM(animal_count) as total_heads FROM slaughterhouse_entries WHERE DATE(entry_date) = '$date_today'";
        $fallback_res = $conn->query($fallback_sql);
        if($fallback_res) {
            $daily_heads = $fallback_res->fetch_assoc()['total_heads'] ?? 0;
        }
    } catch (Exception $ex) { $daily_heads = 0; }
}


// C. STALL OCCUPANCY
$stall_occ = 0; $stall_tot = 0;
try {
    $stall_d = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status='occupied' THEN 1 ELSE 0 END) as occupied FROM stalls")->fetch_assoc();
    $stall_occ = $stall_d['occupied'] ?? 0;
    $stall_tot = $stall_d['total'] ?? 0;
} catch (Exception $e) {}
$stall_perc = ($stall_tot > 0) ? round(($stall_occ / $stall_tot) * 100) : 0;


// D. PENDING APPLICATIONS
$p_apps = 0;
try {
    $p_apps_res = $conn->query("SELECT COUNT(*) AS total FROM business_applications WHERE status='Pending'");
    if($p_apps_res) $p_apps = $p_apps_res->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {}


// E. ACTIVE VENDORS
$active_vendors = 0;
try {
    $vendor_res = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='vendor' AND approved_by_admin=1");
    if($vendor_res) $active_vendors = $vendor_res->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {}


// F. REVENUE CHART DATA
$months = []; $revenues = []; $forecast_revenue = "₱0.00"; $forecast_status = "No Data";

try {
    $chart_sql = "SELECT DATE_FORMAT(payment_date, '%M') as m_name, DATE_FORMAT(payment_date, '%Y-%m') as m_order, SUM(amount) as total 
                  FROM transactions 
                  WHERE status = 'valid' 
                  GROUP BY m_order ORDER BY m_order ASC LIMIT 12";
    $chart_res = $conn->query($chart_sql);
    
    $x_values = []; $y_values = []; $i = 1;
    if ($chart_res && $chart_res->num_rows > 0) {
        while($row = $chart_res->fetch_assoc()){
            $months[] = $row['m_name'];
            $revenues[] = $row['total'];
            $x_values[] = $i++;
            $y_values[] = (float)$row['total'];
        }
        if (count($y_values) >= 2) {
            $n = count($y_values);
            $sum_x = array_sum($x_values); $sum_y = array_sum($y_values);
            $sum_xx = 0; $sum_xy = 0;
            for ($k = 0; $k < $n; $k++) { $sum_xx += $x_values[$k]**2; $sum_xy += $x_values[$k] * $y_values[$k]; }
            
            $denom = (($n * $sum_xx) - ($sum_x**2));
            if($denom != 0){
                $slope = (($n * $sum_xy) - ($sum_x * $sum_y)) / $denom;
                $intercept = ($sum_y - ($slope * $sum_x)) / $n;
                $predicted_val = ($slope * ($n + 1)) + $intercept;
                $forecast_revenue = "₱" . number_format($predicted_val, 2);
                $forecast_status = $slope > 0 ? "Trending Up 📈" : "Trending Down 📉";
            }
        }
    } else {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
        $revenues = [0, 0, 0, 0, 0];
    }
} catch (Exception $e) {}

// G. RECENT TABLES

// 1. Applications
$rec_apps = null;
try {
    $rec_apps = $conn->query("SELECT ba.*, u.name FROM business_applications ba JOIN users u ON ba.vendor_id = u.id ORDER BY submitted_at DESC LIMIT 5");
} catch(Exception $e){}

// 2. LOGS (Records) - This restores the Logs view from the screenshot
$rec_logs = null;
try {
    $rec_logs = $conn->query("SELECT * FROM records ORDER BY created_at DESC LIMIT 5");
} catch(Exception $e){}

?>

<!-- ======================= HTML VIEW ========================= -->
<?php include('includes/header.php'); ?>
<body class="bg-slate-50 flex h-screen overflow-hidden font-sans text-slate-800">

    <?php include('includes/navbar.php'); ?>

    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>

        <main class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">
            
            <!-- HEADER with Welcome Message -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 bg-white p-6 rounded-2xl shadow-sm border-l-8 border-blue-600">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Overview Dashboard</h2>
                    <p class="text-sm text-slate-500 mt-1">Welcome back, <span class="text-blue-600 font-semibold"><?= $current_user ?></span> (<?= $current_role ?>)</p>
                </div>
                
                <!-- Alert Badges -->
                <div class="flex items-center gap-3 mt-4 md:mt-0">
                    <?php if($pending_repairs > 0): ?>
                    <div class="flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-700 rounded-lg border border-amber-200 animate-pulse">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span class="text-xs font-bold"><?= $pending_repairs ?> Repairs</span>
                    </div>
                    <?php endif; ?>
                    <div class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg border border-blue-100 flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span> 
                        <span class="text-xs font-bold uppercase">Online</span>
                    </div>
                </div>
            </div>

            <!-- KEY METRICS CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- 1. AI REVENUE FORECAST -->
                <a href="reports.php" class="bg-gradient-to-r from-blue-800 to-blue-600 p-6 rounded-2xl shadow-lg text-white hover:shadow-blue-300/50 hover:shadow-xl transition transform hover:-translate-y-1 block relative overflow-hidden group">
                     <div class="absolute right-0 top-0 opacity-10 scale-150 translate-x-4 group-hover:scale-110 transition-transform duration-700">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1010 10A10 10 0 0012 2zm0 18a8 8 0 118-8 8 8 0 01-8 8z"/><path d="M12 6a1 1 0 00-1 1v2.17l-1.6-1.6a1 1 0 00-1.41 1.41L11.59 12l-3.6 3.6a1 1 0 101.41 1.41l1.6-1.6V17a1 1 0 002 0v-2.17l1.6 1.6a1 1 0 001.41-1.41L12.41 12l3.6-3.6a1 1 0 00-1.41-1.41l-1.6 1.6V7a1 1 0 00-1-1z"/></svg>
                     </div>
                     <p class="text-blue-100 text-[10px] font-bold uppercase tracking-wider">AI Predicted Income</p>
                     <h3 class="text-2xl font-extrabold mt-1"><?= $forecast_revenue ?></h3>
                     <div class="mt-3 inline-block bg-white/20 backdrop-blur-sm px-2 py-0.5 rounded text-[10px] text-white border border-white/10">
                         <?= $forecast_status ?>
                     </div>
                </a>

                <!-- 2. SLAUGHTERHOUSE -->
                <a href="slaughter_home.php" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:border-red-300 transition hover:-translate-y-1 block group">
                     <div class="flex justify-between mb-2">
                        <div class="p-2 bg-red-50 text-red-600 rounded-lg group-hover:bg-red-500 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Meat Ops</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <h3 class="text-3xl font-bold text-slate-800"><?= $daily_heads ?></h3>
                        <span class="text-xs text-slate-500">Heads</span>
                    </div>
                    <p class="text-[10px] text-red-500 mt-1">Processed Today</p>
                </a>

                <!-- 3. STALL OCCUPANCY -->
                <a href="stalls.php" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:border-blue-400 transition hover:-translate-y-1 block group">
                    <div class="flex justify-between mb-2">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Stalls</span>
                    </div>
                    <h3 class="text-3xl font-bold text-slate-800"><?= $stall_occ ?> <span class="text-sm font-light text-slate-400">/ <?= $stall_tot ?></span></h3>
                    <div class="w-full bg-slate-100 h-1.5 mt-2 rounded-full overflow-hidden">
                        <div class="bg-blue-500 h-full rounded-full" style="width: <?= $stall_perc ?>%"></div>
                    </div>
                </a>

                <!-- 4. PENDING PERMITS -->
                <a href="permit_verifications.php" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:border-emerald-400 transition hover:-translate-y-1 block group">
                    <div class="flex justify-between mb-2">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                           <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Review</span>
                    </div>
                    <h3 class="text-3xl font-bold text-slate-800"><?= $p_apps ?></h3>
                    <p class="text-[10px] text-emerald-600 mt-1">Applications</p>
                </a>
            </div>

            <!-- CHART & ANALYTICS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Main Chart -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-slate-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            Revenue Analytics
                        </h3>
                    </div>
                    <div class="w-full h-80">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Summary Side List -->
                <div class="flex flex-col gap-4">
                    <div class="bg-slate-800 p-5 rounded-2xl shadow-sm text-white flex justify-between items-center">
                         <div>
                             <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Registered Vendors</p>
                             <h3 class="text-3xl font-bold mt-1"><?= $active_vendors ?></h3>
                         </div>
                         <div class="bg-slate-700 p-3 rounded-xl">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                         </div>
                    </div>
                    <!-- Quick Shortcuts -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex-1">
                         <h4 class="font-bold text-sm text-slate-700 mb-4">Quick Shortcuts</h4>
                         <div class="space-y-3">
                             <a href="transactions.php?create=new" class="block w-full text-center py-3 rounded-xl border-2 border-dashed border-blue-200 text-blue-600 text-xs font-bold hover:bg-blue-50 transition">
                                 + Transaction
                             </a>
                             <a href="slaughter_intake.php" class="block w-full text-center py-3 rounded-xl border-2 border-dashed border-red-200 text-red-600 text-xs font-bold hover:bg-red-50 transition">
                                 + Animal Entry
                             </a>
                             <a href="stall_add.php" class="block w-full text-center py-3 rounded-xl border-2 border-dashed border-slate-200 text-slate-500 text-xs font-bold hover:bg-slate-50 transition">
                                 + Add Stall
                             </a>
                         </div>
                    </div>
                </div>
            </div>

            <!-- BOTTOM: DATA TABLES -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Business Applications Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-50 bg-white flex justify-between items-center">
                        <h3 class="font-bold text-slate-700 flex items-center gap-2">📜 Pending Applications</h3>
                        <a href="permit_verifications.php" class="text-xs text-blue-600 hover:underline">See All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-xs text-blue-900/50 uppercase font-semibold"><tr><th class="px-6 py-3">Business</th><th class="px-6 py-3 text-right">State</th></tr></thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if($rec_apps && $rec_apps->num_rows > 0): while($r = $rec_apps->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 font-bold text-slate-800"><?= $r['business_name'] ?>
                                            <div class="text-xs text-slate-400 font-normal"><?= $r['name'] ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="bg-yellow-50 text-yellow-600 border border-yellow-100 px-2 py-0.5 rounded text-[10px] uppercase font-bold"><?= $r['status'] ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; else: ?><tr><td colspan="2" class="p-6 text-center text-xs text-slate-400">No applications.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activities (LOGS) - MODIFIED: COLORS PER YOUR REQUEST -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                     <div class="px-6 py-4 border-b border-slate-50 bg-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <h3 class="font-bold text-slate-700">Recent Activities</h3>
                     </div>
                      <div class="overflow-x-auto">
                        <ul class="divide-y divide-slate-50 p-4">
                            <?php if($rec_logs && $rec_logs->num_rows > 0): while($log = $rec_logs->fetch_assoc()): 
                                $action = strtoupper($log['action']);
                                
                                // Color Logic
                                if ($action === 'APPROVED') {
                                    $dotColor = 'bg-emerald-500';
                                    $textColor = 'text-slate-800';
                                    $actionClass = 'text-emerald-600 font-bold'; // Green Text
                                } elseif ($action === 'REJECTED') {
                                    $dotColor = 'bg-rose-500';
                                    $textColor = 'text-slate-800';
                                    $actionClass = 'text-rose-600 font-bold'; // Red Text
                                } else {
                                    // Default (Added, Updated, Deleted)
                                    $dotColor = 'bg-blue-500';
                                    $textColor = 'text-slate-600';
                                    $actionClass = 'text-slate-700 font-bold'; // Standard Blueish/Slate
                                }
                            ?>
                                <li class="relative pl-6 pb-6 last:pb-0">
                                    <!-- Timeline Line -->
                                    <div class="absolute left-1.5 top-2 h-full w-0.5 bg-slate-100 last:hidden"></div>
                                    <!-- Dot -->
                                    <div class="absolute left-0 top-1.5 h-3.5 w-3.5 rounded-full ring-4 ring-white <?= $dotColor ?>"></div>
                                    
                                    <div>
                                        <p class="text-xs text-slate-700">
                                            <span class="<?= $actionClass ?>"><?= $action ?></span> 
                                            <span class="text-blue-500 font-medium">Stall <?= htmlspecialchars($log['stall_number']) ?></span>
                                        </p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">
                                            <?= date('M j, g:i A', strtotime($log['created_at'])) ?>
                                        </p>
                                    </div>
                                </li>
                            <?php endwhile; else: ?><li class="text-center text-xs text-slate-400">No logs recorded.</li><?php endif; ?>
                        </ul>
                      </div>
                </div>

            </div>

        </main>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/global.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart');
            if(ctx) {
                const labels = <?= json_encode($months) ?>;
                const data = <?= json_encode($revenues) ?>;
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels.length > 0 ? labels : ['No Data'],
                        datasets: [{
                            label: 'Income (₱)',
                            data: data.length > 0 ? data : [0],
                            borderColor: '#2563eb', // Blue-600
                            backgroundColor: (context) => {
                                const ctx = context.chart.ctx;
                                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                                gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
                                gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');
                                return gradient;
                            },
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#2563eb',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [2, 2], color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#64748b' } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#64748b' } }
                        }
                    }
                });
            }
        });
    </script>
    <?php include('includes/footer.php'); ?>
</body>
</html>