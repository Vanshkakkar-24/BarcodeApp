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

/* -----------------------------
   PAGINATION SETTINGS
------------------------------*/
$limit = 20; // records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* -----------------------------
   TOTAL RECORD COUNT
------------------------------*/
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM label_print");
$totalRow = mysqli_fetch_assoc($totalQuery);
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);

/* -----------------------------
   FETCH RECORDS FOR CURRENT PAGE
------------------------------*/
$result = mysqli_query(
    $conn,
    "SELECT * FROM label_print 
     ORDER BY barcode_id DESC 
     LIMIT $limit OFFSET $offset"
);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Barcode Records</title>
    <link rel="stylesheet" href="label_print_ui_style.css">
</head>

<body>

<h2>All Barcode Records</h2>

<div class="card">
    <strong>Total Records:</strong> <?php echo $totalRecords; ?>
</div>

<div style="margin-bottom:15px;">
    <a href="label_print_ui.php" class="view-all-btn">
        ← Back to Main Page
    </a>
</div>

<div style="overflow-x:auto;">
<table>
    <tr>
        <th>ID</th>
        <th>Barcode No</th>
        <th>Company</th>
        <th>Product</th>
        <th>MRP</th>
        <th>Print Count</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['barcode_id']; ?></td>
            <td><?php echo $row['barcode_no']; ?></td>
            <td><?php echo $row['company']; ?></td>
            <td><?php echo $row['product']; ?></td>
            <td>₹<?php echo number_format($row['mrp'], 2); ?></td>
            <td><?php echo $row['print_count']; ?></td>
        </tr>
    <?php } ?>

</table>
</div>

<!-- PAGINATION -->
<div class="pagination">

    <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" 
           class="<?php echo ($i == $page) ? 'active-page' : ''; ?>">
           <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>">Next</a>
    <?php endif; ?>

</div>

</body>
</html>