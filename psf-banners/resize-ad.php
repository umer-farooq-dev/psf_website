<?php
/**
 * Fits PSF's own advert to the theme's hero size.
 *
 * Target 2000 x 770 (2.6:1) — 2x the real hero slot (~1010 x 386, style.css).
 * The advert is close to that ratio already, so it is scaled on width with
 * Lanczos and the small vertical excess is trimmed, biased to the top where
 * there is margin rather than the contact strip at the bottom.
 *
 * Save the advert next to this file as psf-ad.<jpg|png|webp>, then run:
 *   php psf-banners/resize-ad.php
 */

const TARGET_W = 2000;
const TARGET_H = 770;

$dir = __DIR__;
$src = null;
foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
    $candidate = $dir . '/psf-ad.' . $ext;
    if (is_file($candidate)) {
        $src = $candidate;
        break;
    }
}

if (!$src) {
    fwrite(STDERR, "No source found. Save the advert as {$dir}\\psf-ad.jpg (or .png / .webp)\n");
    exit(1);
}

$img = new Imagick($src);
$img->setImageColorspace(Imagick::COLORSPACE_SRGB);
$w = $img->getImageWidth();
$h = $img->getImageHeight();
echo "Source: " . basename($src) . "  {$w}x{$h}  (" . round($w / $h, 2) . ":1)\n";

// Scale so the full width is kept — the advert's content runs edge to edge.
$scale = TARGET_W / $w;
$newH = (int)round($h * $scale);
$img->resizeImage(TARGET_W, $newH, Imagick::FILTER_LANCZOS, 1);

if ($newH > TARGET_H) {
    // trim the excess: two thirds off the top margin, one third off the bottom
    $excess = $newH - TARGET_H;
    $top = (int)round($excess * 0.66);
    $img->cropImage(TARGET_W, TARGET_H, 0, $top);
    echo "Trimmed {$excess}px of height ({$top} from the top)\n";
} elseif ($newH < TARGET_H) {
    // shorter than the slot: pad with the advert's own edge colour
    $pad = new Imagick();
    $edge = $img->getImagePixelColor(2, (int)($newH / 2))->getColorAsString();
    $pad->newImage(TARGET_W, TARGET_H, new ImagickPixel($edge));
    $pad->compositeImage($img, Imagick::COMPOSITE_OVER, 0, (int)round((TARGET_H - $newH) / 2));
    $img = $pad;
    echo "Padded " . (TARGET_H - $newH) . "px of height\n";
}

$img->setImagePage(TARGET_W, TARGET_H, 0, 0);
// a light unsharp pass restores the crispness any upscale costs
$img->unsharpMaskImage(0, 0.7, 0.8, 0.02);

$out = $dir . '/psf-banner-0-original-hd.png';
$img->setImageFormat('png');
$img->setOption('png:compression-level', '9');
$img->writeImage($out);

echo "Written: " . basename($out) . "  " . TARGET_W . "x" . TARGET_H
    . "  " . round(filesize($out) / 1024) . " KB\n";

$img->destroy();
