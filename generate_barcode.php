<!DOCTYPE html>
<html>

<head>
    <title>SGGS Barcode Generator</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="index_style.css">

</head>

<body>

    <div class="container">
        <h2>SG Game Studios - Barcode Generator</h2>

        <form id="barcodeForm">

            <div class="form-group">
                <label>Item Name</label>
                <select name="item_name" required>
                    <option value="">Select Item</option>
                    <option value="MX11 Pro">MX11 Pro</option>
                    <option value="AX11 Pro">AX11 Pro</option>
                    <option value="PS11 Pro">PS11 Pro</option>
                </select>
            </div>

            <div class="form-group">
                <label>Month</label>
                <select name="month" required>
                    <option value="">Select Month</option>
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        echo "<option value='$m'>$m</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Year</label>
                <select name="year" required>
                    <?php
                    for ($y = 2024; $y <= 2035; $y++) {
                        $selected = ($y == 2026) ? "selected" : "";
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>MRP (₹)</label>
                <input type="number" name="mrp" step="0.01" min="0" required placeholder="Enter product MRP">
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="qty" min="1" max="500" required placeholder="Enter quantity">
            </div>

            <div class="btn-group">
                <button type="submit" class="generate-btn">
                    Generate Barcode
                </button>
            </div>

            <div class="btn-group">
                <a href="label_print_ui" class="link-btn print-btn">
                    Print Barcodes
                </a>

                <a href="test_print" class="link-btn test-btn">
                    Test Barcodes
                </a>
            </div>

        </form>

        <div id="result"></div>
    </div>

    <script>
        document.getElementById("barcodeForm").addEventListener("submit", function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            fetch("generate.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    document.getElementById("result").innerHTML = data;
                });
        });
    </script>

</body>
</html>