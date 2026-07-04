<?php
$c = file_get_contents("D:/OSPanel/domains/localhost/81-maktab/resources/views/login/login.blade.php");
$bom = substr($c, 0, 3);
echo "First 50 bytes hex: " . bin2hex(substr($c, 0, 50)) . PHP_EOL;
echo "Has BOM: " . ($bom === "\xef\xbb\xbf" ? "YES" : "NO") . PHP_EOL;
echo "File size: " . strlen($c) . " bytes\n";
echo "First line: " . strtok($c, "\n") . PHP_EOL;
