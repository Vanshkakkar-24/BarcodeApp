<?php

/* -------------------------------------------------------
   CONNECT TO PRINTER
--------------------------------------------------------*/

$printerPath = "\\\\Stupefy\\TSC TTP-244 Pro"; // keep your printer path
$printer = fopen($printerPath, "w");

if (!$printer) {
    die("Printer not connected.");
}

/* -------------------------------------------------------
   PRINT LOOP
--------------------------------------------------------*/

for ($i = 0; $i < 3; $i++) {
    $tspl = "SIZE 100 mm,50 mm\n";
    $tspl .= "GAP 2 mm,0 mm\n";
    $tspl .= "DENSITY 8\n";
    $tspl .= "SPEED 4\n";
    $tspl .= "DIRECTION 1\n";
    $tspl .= "CLS\n";

    $tspl .= "TEXT 20,30,\"3\",0,1,1,\"SG Game Studios\"\n";
    $tspl .= "TEXT 265,30,\"1\",0,1,1,\"TM\"\n";
    $tspl .= "TEXT 20,61,\"3\",0,1,1,\"Item: Test Product\"\n";
    $tspl .= "TEXT 20,92,\"3\",0,1,1,\"MRP: Rs 2699\"\n";
    $tspl .= "TEXT 20,124,\"3\",0,1,1,\"MFG & PKD: Dec 2025\"\n";
    $tspl .= "TEXT 20,155,\"3\",0,1,1,\"Serial No.: TEST122025854963218\"\n";
    $tspl .= "TEXT 20,188,\"3\",0,1,1,\"Marketed by: SG Game Studios (India)\"\n";
    $tspl .= "TEXT 20,220,\"3\",0,1,1,\"Website: www.sggamestudios.in\"\n";
    $tspl .= "TEXT 20,250,\"3\",0,1,1,\"Customer Care: support@sggamestudios.in\"\n";

    $tspl .= "BARCODE 20,290,\"128\",60,1,0,2,2,\"TEST122025854963218\"\n";

    $qr_url = "http://sggamestudios.in/TEST122025854963218";
    $tspl .= "QRCODE 650,40,L,4,A,0,M2,S7,\"https://sggamestudios.in\"\n";

    $tspl .= "PRINT 1,1\n";

    fwrite($printer, $tspl);
}


/* -------------------------------------------------------
   CLEANUP
--------------------------------------------------------*/

fclose($printer);

header("Location: index.php");
exit;
?>