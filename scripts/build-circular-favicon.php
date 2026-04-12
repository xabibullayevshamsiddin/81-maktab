<?php

/** php scripts/build-circular-favicon.php — logo JPG dan dumaloq PNG favicon */
$root = dirname(__DIR__);
$src = $root.'/public/temp/img/photo_2026-02-06_11-05-24-2.jpg';
$sizes = [16, 32, 48, 180];

if (! is_file($src)) {
    fwrite(STDERR, "Source missing: {$src}\n");
    exit(1);
}

foreach ($sizes as $size) {
    $out = $root.'/public/temp/img/favicon-'.$size.'.png';
    buildCircularPng($src, $out, $size);
    echo "Wrote {$out}\n";
}

function buildCircularPng(string $jpegPath, string $pngPath, int $size): void
{
    $src = imagecreatefromjpeg($jpegPath);
    if ($src === false) {
        throw new RuntimeException('JPEG ochilmadi');
    }

    $sw = imagesx($src);
    $sh = imagesy($src);

    $dest = imagecreatetruecolor($size, $size);
    imagesavealpha($dest, true);
    imagealphablending($dest, true);

    $scale = max($size / $sw, $size / $sh);
    $nw = (int) round($sw * $scale);
    $nh = (int) round($sh * $scale);
    $offx = (int) (($size - $nw) / 2);
    $offy = (int) (($size - $nh) / 2);

    imagecopyresampled($dest, $src, $offx, $offy, 0, 0, $nw, $nh, $sw, $sh);
    imagedestroy($src);

    imagealphablending($dest, false);
    $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
    $cx = ($size - 1) / 2;
    $cy = ($size - 1) / 2;
    $r = $size / 2;

    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            if (hypot($x - $cx, $y - $cy) > $r) {
                imagesetpixel($dest, $x, $y, $transparent);
            }
        }
    }

    imagepng($dest, $pngPath, 9);
    imagedestroy($dest);
}
