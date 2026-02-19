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

<h2>Barcode Print Control</h2>

<form method="post" action="schedule_print.php">
<table border="1" cellpadding="5">
<tr>
    <th>Select</th>
    <th>ID</th>
    <th>Company</th>
    <th>Product</th>
    <th>Barcode</th>
    <th>Quantity</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><input type="checkbox" name="ids[]" value="<?= $row['barcode_id'] ?>"></td>
    <td><?= $row['barcode_id'] ?></td>
    <td><?= htmlspecialchars($row['company']) ?></td>
    <td><?= htmlspecialchars($row['product']) ?></td>
    <td><?= htmlspecialchars($row['barcode_no']) ?></td>
    <td><input type="number" name="qty[<?= $row['barcode_id'] ?>]" value="1" min="1"></td>
</tr>
<?php endwhile; ?>
</table>

<br>
<h3>OR Print By Range</h3>
Start ID: <input type="number" name="range_start">
End ID: <input type="number" name="range_end">
Quantity each: <input type="number" name="range_qty" value="1" min="1">

<br><br>
<button type="submit">Schedule Print</button>
</form>
