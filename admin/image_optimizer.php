<?php
/**
 * admin/image_optimizer.php — Lossless-quality image optimiser
 *
 * Compresses uploaded images using PHP GD (always available on cPanel)
 * without visible quality loss. Strategy per format:
 *
 *   JPEG  → Re-encode at quality 82 (visually identical, ~40-60% smaller)
 *           + strip EXIF metadata (privacy + size)
 *   PNG   → Convert to JPEG if opaque; keep PNG with max compression (9)
 *           if transparency present
 *   WEBP  → Re-encode at quality 82
 *   GIF   → Keep as-is (animation support; GD destroys animations)
 *
 * Resize policy:
 *   - Gallery images: max 1600 × 1200 px (web display)
 *   - Event images:   max 1200 × 800 px  (card/banner)
 *   - Never upscales — only downscales if image exceeds max
 *   - Aspect ratio always preserved
 *
 * Usage:
 *   require 'image_optimizer.php';
 *   $result = optimizeAndSave($tmpPath, $mime, $destPath, 'gallery');
 *   // returns ['ok'=>bool, 'saved_bytes'=>int, 'width'=>int, 'height'=>int, 'error'=>string]
 */

function optimizeAndSave(string $tmpPath, string $mime, string $destPath, string $context = 'gallery'): array
{
    // Max dimensions per context
    $maxW = $context === 'event' ? 1200 : 1600;
    $maxH = $context === 'event' ? 800  : 1200;

    $originalSize = filesize($tmpPath);

    // GIFs: just move — don't touch (GD destroys animated GIFs)
    if ($mime === 'image/gif') {
        if (!move_uploaded_file($tmpPath, $destPath) && !rename($tmpPath, $destPath) && !copy($tmpPath, $destPath)) {
            return ['ok' => false, 'error' => 'Could not save GIF file.'];
        }
        return ['ok' => true, 'saved_bytes' => 0, 'width' => 0, 'height' => 0];
    }

    // Load image into GD
    $src = null;
    switch ($mime) {
        case 'image/jpeg': $src = @imagecreatefromjpeg($tmpPath); break;
        case 'image/png':  $src = @imagecreatefrompng($tmpPath);  break;
        case 'image/webp': $src = @imagecreatefromwebp($tmpPath); break;
        default:
            // Unknown type — just copy
            copy($tmpPath, $destPath);
            return ['ok' => true, 'saved_bytes' => 0, 'width' => 0, 'height' => 0];
    }

    if (!$src) {
        // GD couldn't load it — copy as-is
        copy($tmpPath, $destPath);
        return ['ok' => true, 'saved_bytes' => 0, 'width' => 0, 'height' => 0];
    }

    $origW = imagesx($src);
    $origH = imagesy($src);

    // ── Calculate new dimensions (downscale only, preserve ratio) ─────────────
    $newW = $origW;
    $newH = $origH;

    if ($origW > $maxW || $origH > $maxH) {
        $ratioW = $maxW / $origW;
        $ratioH = $maxH / $origH;
        $ratio  = min($ratioW, $ratioH);
        $newW   = (int) round($origW * $ratio);
        $newH   = (int) round($origH * $ratio);
    }

    // ── Resample ───────────────────────────────────────────────────────────────
    if ($newW !== $origW || $newH !== $origH) {
        $dst = imagecreatetruecolor($newW, $newH);

        // Preserve transparency for PNG
        if ($mime === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);
        $src = $dst;
    }

    // ── Check if PNG has transparency ─────────────────────────────────────────
    $hasAlpha = false;
    if ($mime === 'image/png') {
        $hasAlpha = _pngHasTransparency($src);
    }

    // ── Save optimised image ──────────────────────────────────────────────────
    $saved = false;

    if ($mime === 'image/png' && !$hasAlpha) {
        // Opaque PNG → save as JPEG (much smaller, no quality difference for photos)
        // Change extension in destPath
        $destPath = preg_replace('/\.png$/i', '.jpg', $destPath);
        $saved = imagejpeg($src, $destPath, 82);
    } elseif ($mime === 'image/png' && $hasAlpha) {
        // Keep as PNG, use maximum compression
        imagealphablending($src, false);
        imagesavealpha($src, true);
        $saved = imagepng($src, $destPath, 9);
    } elseif ($mime === 'image/webp') {
        $saved = imagewebp($src, $destPath, 82);
    } else {
        // JPEG
        $saved = imagejpeg($src, $destPath, 82);
    }

    imagedestroy($src);

    if (!$saved) {
        return ['ok' => false, 'error' => 'GD could not write the optimised image.'];
    }

    $newSize    = filesize($destPath);
    $savedBytes = max(0, $originalSize - $newSize);

    return [
        'ok'          => true,
        'saved_bytes' => $savedBytes,
        'original'    => $originalSize,
        'final'       => $newSize,
        'width'       => $newW,
        'height'      => $newH,
        'dest'        => $destPath,
    ];
}

/**
 * Check if a GD PNG image resource contains any transparent pixels.
 * Samples a grid of pixels for speed on large images.
 */
function _pngHasTransparency($img): bool
{
    $w = imagesx($img);
    $h = imagesy($img);
    $step = max(1, (int) ($w / 40));

    for ($x = 0; $x < $w; $x += $step) {
        for ($y = 0; $y < $h; $y += $step) {
            $rgba = imagecolorat($img, $x, $y);
            $alpha = ($rgba >> 24) & 0x7F;
            if ($alpha > 0) return true;
        }
    }
    return false;
}

/**
 * Human-readable file size string.
 */
function formatBytes(int $bytes): string
{
    if ($bytes < 1024)        return $bytes . ' B';
    if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / (1024 * 1024), 2) . ' MB';
}
