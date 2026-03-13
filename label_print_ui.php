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

/* -------------------------------------------------
   CHECK IF TABLE HAS ANY DATA
--------------------------------------------------*/
$totalCheck = mysqli_query($conn, "SELECT COUNT(*) as total FROM label_print");
$totalRow = mysqli_fetch_assoc($totalCheck);
$totalRecords = $totalRow['total'];

/* -------------------------------------------------
   GET NEXT UNPRINTED BARCODE
--------------------------------------------------*/
$nextResult = mysqli_query(
    $conn,
    "SELECT MIN(barcode_id) AS next_id 
     FROM label_print 
     WHERE print_count = 0"
);
$nextRow = mysqli_fetch_assoc($nextResult);
$nextStartId = $nextRow['next_id'];

/* -------------------------------------------------
   GET LAST PRINTED BARCODE
--------------------------------------------------*/
$lastPrintedResult = mysqli_query(
    $conn,
    "SELECT * FROM label_print
     WHERE print_count > 0
     ORDER BY barcode_id DESC
     LIMIT 1"
);
$lastPrinted = mysqli_fetch_assoc($lastPrintedResult);

/* -------------------------------------------------
   FETCH LAST 30 RECORDS
--------------------------------------------------*/
$result = mysqli_query(
    $conn,
    "SELECT * FROM label_print 
     ORDER BY barcode_id DESC 
     LIMIT 30"
);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Barcode Label List</title>

    <link rel="stylesheet" href="label_print_ui_style.css">

    <script>
        function toggleSelectAll(source) {
            let checkboxes = document.getElementsByName('ids[]');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</head>

<body>

    <h2>Barcode Label List</h2>

    <?php if ($totalRecords == 0): ?>

        <div class="card">
            <div class="no-record">No record found</div>
        </div>

    <?php else: ?>

        <!-- LAST PRINTED SECTION -->
        <div class="card last-printed-card">
            <div class="last-printed-title">
                Last Printed Label
            </div>

            <?php if ($lastPrinted): ?>
                <div class="last-printed-grid">
                    <div><strong>Barcode ID:</strong></div>
                    <div><?php echo $lastPrinted['barcode_id']; ?></div>

                    <div><strong>Barcode No:</strong></div>
                    <div><?php echo $lastPrinted['barcode_no']; ?></div>

                    <div><strong>Product:</strong></div>
                    <div><?php echo $lastPrinted['product']; ?></div>

                    <div><strong>Total Prints:</strong></div>
                    <div><?php echo $lastPrinted['print_count']; ?></div>
                </div>
            <?php else: ?>
                <div class="no-record">No record found</div>
            <?php endif; ?>
        </div>

        <!-- RANGE SECTION -->
        <div class="card">
            <form method="POST" action="label_print.php">

                <h3>Print by Barcode ID Range</h3>

                <div class="range-section">
                    <div>
                        <label>Start Barcode ID</label>
                        <input type="number" name="start_id" placeholder="Enter Start ID"
                            value="<?php echo $nextStartId ? $nextStartId : ''; ?>">
                    </div>

                    <div>
                        <label>End Barcode ID</label>
                        <input type="number" name="end_id" placeholder="Enter End ID">
                    </div>

                    <div>
                        <button type="submit" class="print-btn">
                            Print Selected
                        </button>
                    </div>
                </div>

        </div>
        <div class="top-action-buttons">
            <a href="generate_barcode" class="generate-btn">
                + Generate Barcode
            </a>

            <a href="label_print_all" class="view-all-btn">
                View All Records
            </a>
        </div>

        <table>
            <tr>
                <th><input type="checkbox" onclick="toggleSelectAll(this)"></th>
                <th>ID</th>
                <th>Barcode No</th>
                <th>Company</th>
                <th>Product</th>
                <th>MRP</th>
                <th>Print Count</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr class="<?php echo ($row['barcode_id'] == $nextStartId) ? 'highlight' : ''; ?>">
                    <td>
                        <input type="checkbox" name="ids[]" value="<?php echo $row['barcode_id']; ?>">
                    </td>
                    <td><?php echo $row['barcode_id']; ?></td>
                    <td><?php echo $row['barcode_no']; ?></td>
                    <td><?php echo $row['company']; ?></td>
                    <td><?php echo $row['product']; ?></td>
                    <td>₹<?php echo number_format($row['mrp'], 2); ?></td>
                    <td><?php echo $row['print_count']; ?></td>
                </tr>
            <?php } ?>

        </table>

        </form>

    <?php endif; ?>

</body>

</html>