<?php
// admin/generate_certificate.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');

// Kunin ang ID mula sa URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Entry ID.");
}
$entry_id = (int)$_GET['id'];

// Kunin ang lahat ng data tungkol sa entry na iyon
// --- PALITAN MO YUNG QUERY MO NITO PARA MAG-MATCH SA DB MO ---
$stmt = $conn->prepare("
    SELECT animal_type, SUM(animal_count) AS total_count
    FROM slaughterhouse_entries
    WHERE entry_date BETWEEN ? AND ? 
    AND status IN ('Passed', 'Processed', 'Released') 
    GROUP BY animal_type 
    ORDER BY total_count DESC
");

if($stmt) {
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Habang kinukuha ang data, i-update rin ang HTML table loop sa baba
    // Kung sa loop mo sa baba ay '$row["total_head"]' ang nakasulat,
    // palitan mo dapat ng '$row["total_count"]'
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meat Inspection Certificate - <?= htmlspecialchars($entry['certificate_number'] ?? 'N/A') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gray-200">
    <div class="max-w-4xl mx-auto my-12 bg-white p-12 border-4 border-blue-800 relative">
        
        <div class="no-print absolute top-4 right-4">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded shadow-lg hover:bg-blue-700">Print</button>
        </div>
        
        <div class="text-center mb-8">
            <img src="../assets/img/logo.png" alt="LGU Logo" class="w-24 h-24 mx-auto mb-4">
            <h1 class="text-xl font-bold text-gray-800">LGU3 Local Economic Enterprise</h1>
            <h2 class="text-3xl font-bold text-blue-800 mt-2">MEAT INSPECTION CERTIFICATE</h2>
        </div>
        
        <div class="grid grid-cols-2 gap-x-12 gap-y-4 text-lg">
            <div class="col-span-2 border-t pt-4">
                <strong>Owner's Name:</strong>
                <span class="font-light ml-2"><?= htmlspecialchars($entry['owner_name']) ?></span>
            </div>
            <div>
                <strong>Animal Type:</strong>
                <span class="font-light ml-2"><?= htmlspecialchars($entry['animal_type']) ?></span>
            </div>
            <div>
                <strong>Quantity:</strong>
                <span class="font-light ml-2"><?= htmlspecialchars($entry['animal_count']) ?></span>
            </div>
            <div>
                <strong>Date Inspected:</strong>
                <span class="font-light ml-2"><?= date('F j, Y', strtotime($entry['processed_date'] ?? $entry['entry_date'])) ?></span>
            </div>
            <div>
                <strong>Certificate No:</strong>
                <span class="font-light ml-2"><?= htmlspecialchars($entry['certificate_number'] ?? "LGU3-" . date('Y') . "-" . $entry['id']) ?></span>
            </div>
        </div>

        <div class="mt-12 flex justify-between items-end">
            <div class="text-center">
                <p class="font-bold text-xl text-green-600">PASSED INSPECTION</p>
                <p class="text-sm">This is to certify that the meat from this entry has passed the ante- and post-mortem inspections and is fit for human consumption.</p>
            </div>
            <div class="text-center border-t-2 border-gray-800 pt-2 w-64">
                <p class="font-semibold">Authorizing Officer</p>
                <p class="text-sm">Meat Inspector, LGU3</p>
            </div>
        </div>
        
    </div>
</body>
</html>