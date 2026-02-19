<?php
$config = require 'config.php';
$conn = new mysqli(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

if (!empty($_POST['ids'])) {
    foreach ($_POST['ids'] as $id) {
        $qty = intval($_POST['qty'][$id] ?? 1);
        $stmt = $conn->prepare("UPDATE barcode_print SET print_count=? WHERE barcode_id=?");
        $stmt->bind_param("ii", $qty, $id);
        $stmt->execute();
    }
}

if (!empty($_POST['range_start']) && !empty($_POST['range_end'])) {
    $start = intval($_POST['range_start']);
    $end = intval($_POST['range_end']);
    $qty = intval($_POST['range_qty'] ?? 1);

    $stmt = $conn->prepare(
        "UPDATE barcode_print SET print_count=? WHERE barcode_id BETWEEN ? AND ?"
    );
    $stmt->bind_param("iii", $qty, $start, $end);
    $stmt->execute();
}

header("Location: print.php");
