<?php

$conn = new mysqli("localhost", "root", "vansh123", "barcode_app");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$item_name = $_POST['item_name'];
$month = str_pad($_POST['month'], 2, "0", STR_PAD_LEFT);
$year = $_POST['year'];
$qty = intval($_POST['qty']);
$mrp = floatval($_POST['mrp']);

$item_codes = [
    "MX11 Pro" => "MX11",
    "AX11 Pro" => "AX11",
    "PS11 Pro" => "PS11"
];

$item_code = $item_codes[$item_name];

/* Get last generated number */
$result = $conn->query("SELECT barcode_no FROM label_print ORDER BY barcode_id DESC LIMIT 1");

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_barcode = $row['barcode_no'];
    $last_number = (int) substr($last_barcode, -9);
} else {
    $last_number = 355177490; // base
}

$generated_barcodes = [];

for ($i = 0; $i < $qty; $i++) {

    $random_add = rand(1, 50);
    $last_number += $random_add;

    $new_number = str_pad($last_number, 9, "0", STR_PAD_LEFT);
    $barcode_no = $item_code . $month . $year . $new_number;

    /* Duplicate safety check */
    $check = $conn->prepare("SELECT barcode_id FROM label_print WHERE barcode_no=?");
    $check->bind_param("s", $barcode_no);
    $check->execute();
    $check->store_result();

    while ($check->num_rows > 0) {
        $last_number += rand(1, 50);
        $new_number = str_pad($last_number, 9, "0", STR_PAD_LEFT);
        $barcode_no = $item_code . $month . $year . $new_number;

        $check->bind_param("s", $barcode_no);
        $check->execute();
        $check->store_result();
    }

    /* Insert */
    $stmt = $conn->prepare("INSERT INTO label_print (barcode_no, product, mrp, month, year) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdii", $barcode_no, $item_name, $mrp, $month, $year);
    $stmt->execute();

    $generated_barcodes[] = $barcode_no;
}

/* Output all generated */
echo "<h3>Generated Barcodes:</h3>";
foreach ($generated_barcodes as $code) {
    echo $code . "<br>";
}

$conn->close();
?>