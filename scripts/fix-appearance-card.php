<?php
$file = "D:/OSPanel/domains/localhost/81-maktab/resources/views/profile/partials/appearance-card.blade.php";
$c = file_get_contents($file);

$old = '$currentTheme = $user->profile_theme ?? $user->donation_rank;';
$new = '$currentTheme = $user->donation_rank;';

$c = str_replace($old, $new, $c);
file_put_contents($file, $c);
echo "Fixed appearance card\n";
