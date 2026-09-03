<?php
/**
 * PSF hero banners, built in the style of PSF's own advertising:
 * diamond photo tiles on the right, heavy italic display type with one word
 * in yellow, and a white contact strip along the bottom.
 *
 * 2000 x 770 px — 2x the theme's real hero slot (~1010 x 386, style.css).
 * Everything important sits LEFT: the theme crops from the right
 * (object-position: left center).
 *
 * The diamonds are drawn photo-ready: drop JPG/PNG files named
 * tile-1.jpg .. tile-5.jpg next to this script and they are composited in.
 */

const W = 2000;
const H = 770;
const S = 2;                  // supersample, downscaled at the end
const BAR = 118;              // height of the white contact strip
const OUT = __DIR__ . '/out';

$FONT_BLACKI = 'C:/Windows/Fonts/seguibli.ttf';
$FONT_BLACK  = 'C:/Windows/Fonts/seguibl.ttf';
$FONT_BOLD   = 'C:/Windows/Fonts/segoeuib.ttf';
$FONT_REG    = 'C:/Windows/Fonts/segoeui.ttf';

if (!is_dir(OUT)) {
    mkdir(OUT, 0777, true);
}

function rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function alloc($im, string $hex, int $a = 0)
{
    [$r, $g, $b] = rgb($hex);
    return imagecolorallocatealpha($im, $r, $g, $b, $a);
}

/** Diagonal gradient: dark blue at top-left, bright cyan at bottom-right. */
function diagonalGradient($im, string $from, string $to, int $height): void
{
    [$r1, $g1, $b1] = rgb($from);
    [$r2, $g2, $b2] = rgb($to);
    $w = W * S;
    $h = $height * S;
    $max = $w + $h;

    for ($x = 0; $x < $w; $x += 2) {
        for ($y = 0; $y < $h; $y += 2) {
            // stepping by 2 then filling a 2x2 block keeps this fast enough
            $t = ($x + $y) / $max;
            $e = $t * $t * (3 - 2 * $t);
            $c = imagecolorallocate(
                $im,
                (int)round($r1 + ($r2 - $r1) * $e),
                (int)round($g1 + ($g2 - $g1) * $e),
                (int)round($b1 + ($b2 - $b1) * $e)
            );
            imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $c);
            imagecolordeallocate($im, $c);
        }
    }
}

/** One diamond (a square on its point), optionally holding a photo. */
function diamond($im, int $cx, int $cy, int $d, ?string $photo, string $tint): void
{
    $cxs = $cx * S; $cys = $cy * S; $ds = $d * S;
    $pts = [$cxs, $cys - $ds, $cxs + $ds, $cys, $cxs, $cys + $ds, $cxs - $ds, $cys];

    if ($photo && is_file($photo)) {
        // clip the photo to the diamond by drawing it on a masked layer
        $src = @imagecreatefromstring(file_get_contents($photo));
        if ($src) {
            $side = $ds * 2;
            $tile = imagecreatetruecolor($side, $side);
            $sw = imagesx($src);
            $sh = imagesy($src);
            $scale = max($side / $sw, $side / $sh);
            $nw = (int)round($sw * $scale);
            $nh = (int)round($sh * $scale);
            imagecopyresampled($tile, $src, (int)(($side - $nw) / 2), (int)(($side - $nh) / 2), 0, 0, $nw, $nh, $sw, $sh);

            // punch everything outside the diamond out of the tile
            $mask = imagecreatetruecolor($side, $side);
            imagesavealpha($mask, true);
            imagealphablending($mask, false);
            imagefilledrectangle($mask, 0, 0, $side, $side, imagecolorallocatealpha($mask, 0, 0, 0, 127));
            imagefilledpolygon($mask, [$ds, 0, $side, $ds, $ds, $side, 0, $ds], imagecolorallocate($mask, 255, 255, 255));

            for ($x = 0; $x < $side; $x++) {
                for ($y = 0; $y < $side; $y++) {
                    if (((imagecolorat($mask, $x, $y) >> 24) & 0x7F) === 127) {
                        imagesetpixel($tile, $x, $y, imagecolorallocatealpha($tile, 0, 0, 0, 127));
                    }
                }
            }
            imagesavealpha($tile, true);
            imagealphablending($im, true);
            imagecopy($im, $tile, $cxs - $ds, $cys - $ds, 0, 0, $side, $side);

            imagedestroy($src);
            imagedestroy($tile);
            imagedestroy($mask);
        }
    } else {
        imagefilledpolygon($im, $pts, alloc($im, $tint, 88));
    }

    // white edge, the way the tiles are separated in PSF's own adverts
    imagesetthickness($im, 5 * S);
    imagepolygon($im, $pts, alloc($im, '#ffffff', 40));
    imagesetthickness($im, 1);
}

/** Display line: a soft drop shadow behind, then the fill on top. */
function displayLine($im, string $font, float $size, int $x, int $y, string $fill, string $text): void
{
    imagettftext($im, $size * S, 0, $x * S + 7, $y * S + 7, alloc($im, '#062546', 70), $font, $text);
    imagettftext($im, $size * S, 0, $x * S, $y * S, alloc($im, $fill), $font, $text);
}

function textAt($im, string $font, float $size, int $x, int $y, string $hex, string $str): void
{
    imagettftext($im, $size * S, 0, $x * S, $y * S, alloc($im, $hex), $font, $str);
}

function widthOf(string $font, float $size, string $str): int
{
    $b = imagettfbbox($size * S, 0, $font, $str);
    return (int)round((max($b[2], $b[4]) - min($b[0], $b[6])) / S);
}

function pill($im, int $x, int $y, int $w, int $h, string $hex): void
{
    $r = (int)round($h / 2);
    $x *= S; $y *= S; $w *= S; $h *= S; $r *= S;
    $c = alloc($im, $hex);
    imagefilledrectangle($im, $x + $r, $y, $x + $w - $r, $y + $h, $c);
    imagefilledellipse($im, $x + $r, $y + $r, $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $w - $r, $y + $r, $r * 2, $r * 2, $c);
}

/** The white strip along the bottom, as on PSF's adverts. */
function contactBar($im): void
{
    global $FONT_BOLD, $FONT_REG;

    $top = H - BAR;
    imagefilledrectangle($im, 0, $top * S, W * S, H * S, alloc($im, '#ffffff'));
    imagefilledrectangle($im, 0, $top * S, W * S, ($top + 5) * S, alloc($im, '#f5c518'));

    // phone + e-mail
    imagefilledellipse($im, 132 * S, ($top + 60) * S, 52 * S, 52 * S, alloc($im, '#f5c518'));
    textAt($im, $FONT_BOLD, 15, 178, $top + 46, '#0a2340', 'Téléphone & adresse e-mail');
    textAt($im, $FONT_BOLD, 21, 178, $top + 82, '#1478c8', '+226 78 24 48 89 / 70 29 17 96');
    textAt($im, $FONT_REG, 17, 620, $top + 82, '#4a5a6b', 'psfcontactbf@gmail.com');

    // location
    imagefilledellipse($im, 1080 * S, ($top + 60) * S, 52 * S, 52 * S, alloc($im, '#f5c518'));
    textAt($im, $FONT_BOLD, 15, 1126, $top + 46, '#0a2340', 'Situation géographique');
    textAt($im, $FONT_BOLD, 21, 1126, $top + 82, '#1478c8', 'Ouagadougou, Boins Yaaré');
}

/**
 * @param array{eyebrow:string,lines:array<int,array{0:string,1:string}>,sub:string,badge:?string,from:string,to:string,tiles:array} $spec
 */
function banner(array $spec, string $file): void
{
    global $FONT_BLACKI, $FONT_BLACK, $FONT_BOLD, $FONT_REG;

    $im = imagecreatetruecolor(W * S, H * S);
    imagealphablending($im, true);

    $artH = H - BAR;
    diagonalGradient($im, $spec['from'], $spec['to'], $artH);

    // darker panel behind the type, so the display stays readable
    imagefilledpolygon($im, [
        0, 0,
        980 * S, 0,
        810 * S, $artH * S,
        0, $artH * S,
    ], alloc($im, '#062546', 46));

    // --- diamond tiles, right ------------------------------------------
    // Kept to white/pale blue: a yellow tile at low opacity turns muddy over
    // the blue, and yellow is reserved for the type and the badge anyway.
    // The cluster clears the contact strip (art area is H - BAR tall).
    $tiles = $spec['tiles'];
    diamond($im, 1470, 188, 134, $tiles[0] ?? null, '#ffffff');
    diamond($im, 1470, 452, 134, $tiles[1] ?? null, '#cfe8ff');
    diamond($im, 1762, 188, 134, $tiles[2] ?? null, '#cfe8ff');
    diamond($im, 1762, 452, 134, $tiles[3] ?? null, '#ffffff');
    diamond($im, 1616, 320, 104, $tiles[4] ?? null, '#ffffff');

    // --- eyebrow --------------------------------------------------------
    textAt($im, $FONT_BLACK, 19, 112, 118, '#ffffff', mb_strtoupper($spec['eyebrow'], 'UTF-8'));

    // --- display type ----------------------------------------------------
    $y = 236;
    foreach ($spec['lines'] as [$str, $colour]) {
        displayLine($im, $FONT_BLACKI, 66, 108, $y, $colour, $str);
        $y += 92;
    }

    // --- sub line + optional badge ---------------------------------------
    textAt($im, $FONT_REG, 24, 112, $y + 22, '#dbeaff', $spec['sub']);

    if ($spec['badge']) {
        $bw = widthOf($FONT_BLACK, 19, $spec['badge']) + 66;
        pill($im, 112, $y + 52, $bw, 58, '#f5c518');
        textAt($im, $FONT_BLACK, 19, 145, $y + 90, '#0a2340', $spec['badge']);
    }

    contactBar($im);

    $out = imagecreatetruecolor(W, H);
    imagecopyresampled($out, $im, 0, 0, 0, 0, W, H, W * S, H * S);
    imagepng($out, OUT . '/' . $file, 6);

    imagedestroy($im);
    imagedestroy($out);
    echo "  " . $file . "  " . round(filesize(OUT . '/' . $file) / 1024) . " KB\n";
}

// photos are optional: name them tile-1.jpg .. tile-5.jpg beside this script
$tiles = [];
for ($i = 1; $i <= 5; $i++) {
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $p = __DIR__ . "/tile-$i.$ext";
        if (is_file($p)) {
            $tiles[$i - 1] = $p;
            break;
        }
    }
}
echo "Photos found for tiles: " . count($tiles) . " / 5\n";

banner([
    'eyebrow' => '# La qualité par excellence',
    'lines'   => [['PLOMBERIE', '#ffffff'], ['SANITAIRE', '#ffffff'], ['DU FASO', '#f5c518']],
    'sub'     => '',
    'badge'   => null,
    'from'    => '#062546',
    'to'      => '#25b4ec',
    'tiles'   => $tiles,
], 'psf-banner-5-identite.png');

banner([
    'eyebrow' => '# La qualité par excellence',
    'lines'   => [['TUBES PVC · PPR', '#ffffff'], ['PEHD · GORGÉS', '#f5c518']],
    'sub'     => 'Raccords, robinetterie et accessoires sanitaires',
    'badge'   => 'DISPONIBLE EN STOCK',
    'from'    => '#04203a',
    'to'      => '#1fa2e8',
    'tiles'   => $tiles,
], 'psf-banner-6-tubes-stock.png');

echo "Done -> " . OUT . "\n";
