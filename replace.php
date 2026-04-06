<?php
$dir = __DIR__ . '/resources/views/profile/exams';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);

        $newContent = str_replace('admin.exams', 'profile.exams', $content);
        $newContent = str_replace("href=\"{{ route('admin.dashboard') }}\"", "href=\"{{ route('profile.show') }}\"", $newContent);
        $newContent = str_replace("Bosh sahifa", "Profil", $newContent);

        if ($content !== $newContent) {
            file_put_contents($path, $newContent);
            echo "Updated: $path\n";
        }
    }
}
echo "Done.\n";
