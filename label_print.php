<?php
$config = include 'config.php';
$db = $config['db'];

$conn = mysqli_connect(
    $db['host'],
    $db['user'],
    $db['pass'],
    $db['name']
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$allTspl = "";

/* -------------------------------------------------------
   DETERMINE PRINT MODE
   1. Checkbox selection (ids[])
   2. Range selection (start_id → end_id)
--------------------------------------------------------*/

$ids = [];

/* ---- Mode 1: Checkbox selection ---- */
if (isset($_POST['ids']) && !empty($_POST['ids'])) {
    foreach ($_POST['ids'] as $id) {
        $ids[] = intval($id);
    }
}

/* ---- Mode 2: Range selection ---- */
if (
    isset($_POST['start_id']) && isset($_POST['end_id']) &&
    $_POST['start_id'] !== "" && $_POST['end_id'] !== ""
) {

    $start_id = intval($_POST['start_id']);
    $end_id = intval($_POST['end_id']);

    if ($start_id <= 0 || $end_id <= 0 || $start_id > $end_id) {
        die("Invalid Range Selected.");
    }

    $rangeQuery = mysqli_query(
        $conn,
        "SELECT barcode_id FROM label_print 
         WHERE barcode_id BETWEEN $start_id AND $end_id 
         ORDER BY barcode_id ASC"
    );

    while ($row = mysqli_fetch_assoc($rangeQuery)) {
        $ids[] = intval($row['barcode_id']);
    }
}

/* ---- If still empty ---- */
if (empty($ids)) {
    die("No barcodes selected.");
}

/* -------------------------------------------------------
   CONNECT TO PRINTER
--------------------------------------------------------*/

$systemName = gethostname();
$printerPath = "\\\\$systemName\\TSC TTP-244 Pro";
$printer = fopen($printerPath, "w");

if (!$printer) {
    die("Printer not connected.");
}

/* -------------------------------------------------------
   PRINT LOOP
--------------------------------------------------------*/

foreach ($ids as $id) {

    $result = mysqli_query(
        $conn,
        "SELECT * FROM label_print WHERE barcode_id = $id"
    );

    $data = mysqli_fetch_assoc($result);
    if (!$data)
        continue;

    $barcode = $data['barcode_no'];
    $company_low = $data['company'];
    $company_cap = strtoupper($data['company']);
    $product = $data['product'];
    $website = $data['website'];
    $customer = $data['customer_care'];
    $mrp = number_format($data['mrp'], 2);

    $monthNumber = $data['month'];   // 1–12
    $year = $data['year'];    // 2026

    $mfg = date('M, Y', mktime(0, 0, 0, $monthNumber, 1, $year));

    $tspl = "SIZE 100 mm,50 mm\n";
    $tspl .= "GAP 2 mm,0 mm\n";
    $tspl .= "DENSITY 8\n";
    $tspl .= "SPEED 4\n";
    $tspl .= "DIRECTION 1\n";
    $tspl .= "CLS\n";

    $tspl .= "TEXT 30,30,\"3\",0,1,1,\"$company_cap\"\n";
    $tspl .= "TEXT 275,30,\"1\",0,1,1,\"TM\"\n";
    $tspl .= "TEXT 30,61,\"3\",0,1,1,\"Item: $product\"\n";
    $tspl .= "TEXT 30,92,\"3\",0,1,1,\"MRP: Rs $mrp\"\n";
    $tspl .= "TEXT 30,124,\"3\",0,1,1,\"MFG & PKD: $mfg\"\n";
    $tspl .= "TEXT 30,155,\"3\",0,1,1,\"Serial No.: $barcode\"\n";
    $tspl .= "TEXT 30,188,\"3\",0,1,1,\"Marketed by: $company_low (India)\"\n";
    $tspl .= "TEXT 30,220,\"3\",0,1,1,\"Website: $website\"\n";
    $tspl .= "TEXT 30,250,\"3\",0,1,1,\"Customer Care: $customer\"\n";
    $tspl .= "BARCODE 30,290,\"128\",60,1,0,2,2,\"$barcode\"\n";

    $qr_url = "https://sggamestudios.in/register-product?sn=$barcode";
    $tspl .= "QRCODE 650,40,L,4,A,0,M2,S7,\"$qr_url\"\n";

    $tspl .= "PRINT 1,1\n";

    $allTspl .= $tspl;

    // Increment print_count by 1
    mysqli_query(
        $conn,
        "UPDATE label_print 
         SET print_count = print_count + 1 
         WHERE barcode_id = $id"
    );
}

/* -------------------------------------------------------
   CLEANUP
--------------------------------------------------------*/

fclose($printer);

echo "
<html>
<head>
<script src='https://cdn.jsdelivr.net/npm/qz-tray@2.2.2/qz-tray.js'></script>
</head>
<body>
<script>
async function printLabel() {
    try {
        await qz.websocket.connect();
        const config = qz.configs.create('TSC TTP-244 Pro');
        const data = [{
            type: 'raw',
            format: 'command',
            data: `" . addslashes($allTspl) . "`
        }];
        await qz.print(config, data);
        await qz.websocket.disconnect();
        window.location.href = 'label_print_ui';
    } catch (err) {
        alert('Printing failed: ' + err);
    }
}
printLabel();
</script>
</body>
</html>";
exit;
?>