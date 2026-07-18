<?php

/**
 * Ensure public/storage points at storage/app/public so uploaded files
 * (lesson videos, logos, …) are served.
 *
 * On Windows `php artisan storage:link` needs admin/Developer Mode and often
 * silently leaves a plain empty directory, which makes every /storage/* URL
 * 403. This script is idempotent and picks the right link type per OS:
 *   - Windows: a directory junction (no admin required)
 *   - Linux/macOS: a normal symlink
 *
 * Run automatically via Composer's post-autoload-dump, or manually:
 *   php scripts/link-storage.php
 */

$base = dirname(__DIR__);
$link = $base.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'storage';
$target = $base.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public';

// Already linked correctly? The target ships a .gitignore, so if that file is
// reachable *through* public/storage the link is working — nothing to do.
if (is_file($link.DIRECTORY_SEPARATOR.'.gitignore')) {
    echo "public/storage already linked.\n";
    exit(0);
}

$isWindows = PHP_OS_FAMILY === 'Windows';

// Remove whatever stale/broken thing is sitting there (empty dir or dead link).
if (is_link($link) || is_dir($link) || file_exists($link)) {
    if ($isWindows) {
        exec('cmd /c rmdir /S /Q "'.$link.'"');
    } else {
        @unlink($link);
        @rmdir($link);
    }
}

if (file_exists($link)) {
    fwrite(STDERR, "Could not remove existing public/storage (has real files?). Skipping.\n");
    exit(0); // don't fail the composer run
}

if ($isWindows) {
    exec('cmd /c mklink /J "'.$link.'" "'.$target.'" 2>&1', $out, $code);
    echo ($code === 0 ? "Created public/storage junction.\n" : "mklink failed: ".implode(' ', $out)."\n");
    exit(0);
}

echo symlink($target, $link)
    ? "Created public/storage symlink.\n"
    : "Failed to create public/storage symlink.\n";
exit(0);
