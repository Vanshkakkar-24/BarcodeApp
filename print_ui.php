<?php
$config = require 'config.php';
$conn = new mysqli(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

$result = $conn->query("SELECT * FROM barcode_print ORDER BY barcode_id ASC");
?>

<h2>Barcode Print Panel</h2>

<form method="post" action="print.php">

<table border="1" cellpadding="8">
<tr>
    <th>Select</th>
    <th>ID</th>
    <th>Company</th>
    <th>Product</th>
    <th>Barcode</th>
    <th>Total Printed</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td>
        <input type="checkbox" name="ids[]" value="<?= $row['barcode_id'] ?>">
    </td>
    <td><?= $row['barcode_id'] ?></td>
    <td><?= htmlspecialchars($row['company']) ?></td>
    <td><?= htmlspecialchars($row['product']) ?></td>
    <td><?= htmlspecialchars($row['barcode_no']) ?></td>
    <td><?= $row['print_count'] ?></td>
</tr>
<?php endwhile; ?>

</table>

<br>
<button type="submit">Print Selected</button>
</form>
