<?php
$layout = "D:/OSPanel/domains/localhost/81-maktab/resources/views/components/loyouts/main.blade.php";
$c = file_get_contents($layout);

$old = "auth()->user()->profile_theme ?? auth()->user()->donation_rank";
$new = "auth()->user()->donation_rank";

$c = str_replace($old, $new, $c);
file_put_contents($layout, $c);
echo "Fixed\n";
