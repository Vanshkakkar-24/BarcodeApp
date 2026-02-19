<?php
$config = require 'config.php';
$conn = new mysqli(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

if (empty($_POST['ids'])) {
    die("No barcode selected.");
}

$ids = $_POST['ids'];

$tspl = "";
$tspl .= "SIZE 4,1.5\n";
$tspl .= "GAP 2 mm,0 mm\n";
$tspl .= "DIRECTION 1\n";

$labels = [];

foreach ($ids as $id) {

    $stmt = $conn->prepare("SELECT * FROM barcode_print WHERE barcode_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $labels[] = $row;
    }
}

$total = count($labels);

for ($i = 0; $i < $total; $i += 2) {

    $tspl .= "CLS\n";

    // LEFT LABEL
    if (isset($labels[$i])) {

        $row = $labels[$i];

        $company = strtoupper($row['company']);
        $product = strtoupper($row['product']);
        $barcode = $row['barcode_no'];

        $tspl .= "TEXT 20,55,\"2\",0,1,1,\"$company\"\n";
        $tspl .= "TEXT 20,80,\"2\",0,1,1,\"Product: $product\"\n";
        $tspl .= "BARCODE 20,115,\"128\",80,1,0,2,2,\"$barcode\"\n";

        // increment lifetime counter
        $update = $conn->prepare(
            "UPDATE barcode_print 
             SET print_count = print_count + 1 
             WHERE barcode_id=?"
        );
        $update->bind_param("i", $row['barcode_id']);
        $update->execute();
    }

    // RIGHT LABEL
    if (isset($labels[$i+1])) {

        $row = $labels[$i+1];

        $company = strtoupper($row['company']);
        $product = strtoupper($row['product']);
        $barcode = $row['barcode_no'];

        $offset = 405;

        $tspl .= "TEXT " . (20 + $offset) . ",55,\"2\",0,1,1,\"$company\"\n";
        $tspl .= "TEXT " . (20 + $offset) . ",80,\"2\",0,1,1,\"Product: $product\"\n";
        $tspl .= "BARCODE " . (20 + $offset) . ",115,\"128\",80,1,0,2,2,\"$barcode\"\n";

        // increment lifetime counter
        $update = $conn->prepare(
            "UPDATE barcode_print 
             SET print_count = print_count + 1 
             WHERE barcode_id=?"
        );
        $update->bind_param("i", $row['barcode_id']);
        $update->execute();
    }

    $tspl .= "PRINT 1\n";
}


file_put_contents("label.txt", $tspl);

$printer = $config['printer']['name'];
exec("copy /b label.txt \"\\\\localhost\\$printer\"");

header("Location: print_ui.php");
exit();