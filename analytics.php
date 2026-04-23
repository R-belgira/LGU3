<?php
// admin/analytics.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db.php');
$page_title = "Data Analytics";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php"); exit();
}

// Default date range: unang araw at huling araw ng kasalukuyang buwan
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Kumuha ng data para sa report
$stmt = $conn->prepare("
    SELECT pc.category_name, SUM(t.amount) as total_amount
    FROM transactions t
    JOIN payment_categories pc ON t.category_id = pc.id
    WHERE t.status = 'valid' AND (t.payment_date BETWEEN ? AND ?)
    GROUP BY pc.category_name ORDER BY total_amount DESC
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$report_data = $stmt->get_result();

// Ihanda ang data para sa Chart.js
$chart_labels = [];
$chart_data_values = [];
$grand_total = 0;
while($row = $report_data->fetch_assoc()) {
    $chart_labels[] = $row['category_name'];
    $chart_data_values[] = $row['total_amount'];
    $grand_total += $row['total_amount'];
}
// I-rewind ang data para magamit ulit sa table
$report_data->data_seek(0); 

$current_user = htmlspecialchars($_SESSION["name"] ?? 'Guest');
$current_role = htmlspecialchars($_SESSION["role"] ?? "User");
?>
<?php include('includes/header.php'); ?>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <?php include('includes/navbar.php'); ?>
    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>
        <main class="flex-1 overflow-y-auto p-6">
            <!-- DATE RANGE FILTER -->
            <div class="bg-white rounded-lg shadow border p-6 mb-6">
                <form method="GET">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Data Analytics</h2>
                            <p class="text-sm text-gray-500">Analyze revenue from different sources over a period of time.</p>
                        </div>
                        <div class="flex gap-4">
                            <div><label class="text-sm">From</label><input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="mt-1 w-full p-2 border rounded-md"></div>
                            <div><label class="text-sm">To</label><input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="mt-1 w-full p-2 border rounded-md"></div>
                            <button type="submit" class="self-end px-4 py-2 text-sm rounded-md bg-blue-600 text-white">Analyze</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- RESULTS AREA -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- Table sa Kaliwa -->
                <div class="lg:col-span-3 bg-white rounded-lg shadow border p-6">
                    <h3 class="font-semibold mb-4">Revenue Breakdown by Source</h3>
                    <p class="text-sm text-gray-600 mb-4">Period: <strong><?= date('M d, Y', strtotime($start_date)) ?></strong> to <strong><?= date('M d, Y', strtotime($end_date)) ?></strong></p>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100"><tr class="border-b"><th class="py-2 px-4 text-left">Category</th><th class="py-2 px-4 text-right">Total Revenue</th></tr></thead>
                        <tbody>
                            <?php if ($report_data->num_rows === 0): ?>
                                <tr><td colspan="2" class="text-center py-4">No data for this period.</td></tr>
                            <?php else: ?>
                                 <?php while ($row = $report_data->fetch_assoc()): ?>
                                    <tr class="border-b"><td class="py-3 px-4"><?= htmlspecialchars($row['category_name']) ?></td><td class="py-3 px-4 text-right font-semibold">₱<?= number_format($row['total_amount'], 2) ?></td></tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="font-bold"><tr class="bg-gray-200"><td class="py-3 px-4 text-right">GRAND TOTAL</td><td class="py-3 px-4 text-right text-lg">₱<?= number_format($grand_total, 2) ?></td></tr></tfoot>
                    </table>
                </div>
                
                <!-- Chart sa Kanan -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow border p-6">
                     <h3 class="font-semibold mb-4">Revenue Visualization</h3>
                     <div style="height: 300px; position: relative;">
                        <canvas id="analyticsPieChart"></canvas>
                     </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- JavaScript para sa Chart -->
    <script src="js/global.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('analyticsPieChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut', // Pwedeng 'pie' o 'doughnut'
                    data: {
                        labels: <?= json_encode($chart_labels) ?>,
                        datasets: [{
                            data: <?= json_encode($chart_data_values) ?>,
                            backgroundColor: ['#3B82F6', '#EF4444', '#10B981', '#F59E0B'],
                        }]
                    },
                    options: { maintainAspectRatio: false, responsive: true }
                });
            }
        });
    </script>
    <?php include('includes/footer.php'); ?>
</body>
</html>