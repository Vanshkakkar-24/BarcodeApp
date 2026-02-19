<?php
$config = require 'config.php';
$conn = new mysqli(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

$result = $conn->query("SELECT * FROM barcode_print WHERE print_count > 0");

if ($result->num_rows == 0) {
    die("No barcodes scheduled.");
}

$labelWidth = 406;   // 2 inch
$middle = 203;       // exact center of 2 inch

$tspl = "";
$tspl .= "SIZE 4,1.5\n";
$tspl .= "GAP 2 mm, 0\n";
$tspl .= "DIRECTION 1\n";

$labels = [];

while($row = $result->fetch_assoc()) {
    for ($i = 0; $i < $row['print_count']; $i++) {
        $labels[] = $row;
    }
}

$total = count($labels);

for ($i = 0; $i < $total; $i += 2) {

    $tspl .= "CLS\n";

    // ================= LEFT LABEL =================
    if (isset($labels[$i])) {

        $row = $labels[$i];

        $company = strtoupper($row['company']);
        $product = strtoupper($row['product']);
        $barcode = $row['barcode_no'];

        // $tspl .= "TEXT $middle,20,\"3\",0,1,1,\"$company\"\n";
        // $tspl .= "TEXT $middle,48,\"4\",0,1,1,\"$product\"\n";

        // $tspl .= "BARCODE 63,85,\"128\",80,1,0,2,2,\"$barcode\"\n";

        $tspl .= "TEXT 20,55,\"2\",0,1,1,\"$company\"\n";
        $tspl .= "TEXT 20,80,\"2\",0,1,1,\"Product: $product\"\n";

        $tspl .= "BARCODE 20,115,\"128\",80,1,0,2,2,\"$barcode\"\n";
    }

    // ================= RIGHT LABEL =================
    if (isset($labels[$i+1])) {

        $row = $labels[$i+1];

        $company = strtoupper($row['company']);
        $product = strtoupper($row['product']);
        $barcode = $row['barcode_no'];

        $offset = 405;

        // $tspl .= "TEXT " . ($middle + $offset) . ",18,\"4\",0,1,1,\"$company\"\n";
        // $tspl .= "TEXT " . ($middle + $offset) . ",48,\"4\",0,1,1,\"$product\"\n";

        // $tspl .= "BARCODE " . (63 + $offset) . ",85,\"128\",80,1,0,2,2,\"$barcode\"\n";

        $tspl .= "TEXT " . (20 + $offset) . ",60,\"2\",0,1,1,\"$company\"\n";
        $tspl .= "TEXT " . (20 + $offset) . ",90,\"2\",0,1,1,\"Product: $product\"\n";
        $tspl .= "BARCODE " . (20 + $offset) . ",125,\"128\",80,1,0,2,2,\"$barcode\"\n";
    }

    $tspl .= "PRINT 1\n";
}


file_put_contents("label.txt", $tspl);

$printer = $config['printer']['name'];
exec("copy /b label.txt \"\\\\localhost\\$printer\"");

// Track how many times each barcode printed in this job
$printSummary = [];

while($row = $result->fetch_assoc()) {

    $barcodeNo = $row['barcode_no'];
    $qty = $row['print_count'];

    // Store quantity for updating total
    if (!isset($printSummary[$barcodeNo])) {
        $printSummary[$barcodeNo] = 0;
    }

    $printSummary[$barcodeNo] += $qty;

    for ($i = 0; $i < $qty; $i++) {
        $labels[] = $row;
    }
}

foreach ($printSummary as $barcodeNo => $qtyPrinted) {

    $stmt = $conn->prepare(
        "UPDATE barcode_print 
         SET print_count = print_count + ? 
         WHERE barcode_no = ?"
    );

    $stmt->bind_param("is", $qtyPrinted, $barcodeNo);
    $stmt->execute();
}


echo "Printing completed.";
