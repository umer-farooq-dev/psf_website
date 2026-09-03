<?php
/**
 * PSF hero banners.
 *
 * Output: 2000 x 770 px — that is 2x the theme's real hero slot
 * (~1010 x 386, style.css), so it stays sharp on HD screens and is barely
 * cropped. Everything important sits on the LEFT because the theme uses
 * object-position: left center, so the right edge is cropped first.
 *
 * Drawn at 2x internally and scaled down, which antialiases the circles
 * and the type.
 */

const W = 2000;
const H = 770;
const S = 2;                 // supersample factor
const OUT = __DIR__ . '/out';

$FONT_BLACK  = 'C:/Windows/Fonts/seguibl.ttf';   // Segoe UI Black
$FONT_BLACKI = 'C:/Windows/Fonts/seguibli.ttf';  // Segoe UI Black Italic
$FONT_BOLD   = 'C:/Windows/Fonts/segoeuib.ttf';  // Segoe UI Bold
$FONT_REG    = 'C:/Windows/Fonts/segoeui.ttf';   // Segoe UI

foreach ([$FONT_BLACK, $FONT_BLACKI, $FONT_BOLD, $FONT_REG] as $f) {
    if (!is_file($f)) {
        fwrite(STDERR, "Missing font: $f\n");
        exit(1);
    }
}

if (!is_dir(OUT)) {
    mkdir(OUT, 0777, true);
}

/** #rrggbb -> [r,g,b] */
function rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function alloc($im, string $hex, int $alpha = 0)
{
    [$r, $g, $b] = rgb($hex);
    return imagecolorallocatealpha($im, $r, $g, $b, $alpha);
}

/** Left-to-right gradient with a slight diagonal lift. */
function gradient($im, string $from, string $to): void
{
    [$r1, $g1, $b1] = rgb($from);
    [$r2, $g2, $b2] = rgb($to);
    $w = W * S;
    $h = H * S;

    for ($x = 0; $x < $w; $x++) {
        $t = $x / max(1, $w - 1);
        $e = $t * $t * (3 - 2 * $t);           // smoothstep, softer than linear
        $c = imagecolorallocate(
            $im,
            (int)round($r1 + ($r2 - $r1) * $e),
            (int)round($g1 + ($g2 - $g1) * $e),
            (int)round($b1 + ($b2 - $b1) * $e)
        );
        imagefilledrectangle($im, $x, 0, $x, $h, $c);
        imagecolordeallocate($im, $c);
    }
}

/**
 * A bundle of pipes seen end-on — the motif from PSF's own advertising.
 *
 * Each pipe is a true ring: the bore is punched out so the background shows
 * through, which is what makes it read as a pipe rather than a bubble.
 * Drawn on its own layer with blending off, then composited once.
 *
 * @param array<int, array{0:int,1:int,2:int}> $pipes  [cx, cy, outer radius]
 */
function pipeBundle($im, array $pipes, string $wall): void
{
    $layer = imagecreatetruecolor(W * S, H * S);
    imagesavealpha($layer, true);
    imagealphablending($layer, false);
    imagefilledrectangle($layer, 0, 0, W * S, H * S, imagecolorallocatealpha($layer, 0, 0, 0, 127));

    [$r, $g, $b] = rgb($wall);
    $hole = imagecolorallocatealpha($layer, 0, 0, 0, 127);

    foreach ($pipes as [$cx, $cy, $outer]) {
        $cx *= S; $cy *= S; $outer *= S;
        // slightly different wall opacity per pipe gives the bundle some depth
        $wallColor = imagecolorallocatealpha($layer, $r, $g, $b, 88 + ($outer % 3) * 6);
        imagefilledellipse($layer, $cx, $cy, $outer * 2, $outer * 2, $wallColor);
        $inner = (int)round($outer * 0.60);
        imagefilledellipse($layer, $cx, $cy, $inner * 2, $inner * 2, $hole);
    }

    imagealphablending($im, true);
    imagecopy($im, $layer, 0, 0, 0, 0, W * S, H * S);
    imagedestroy($layer);
}

/** Text, positioned by its baseline-left in final (unscaled) coordinates. */
function text($im, string $font, float $size, int $x, int $y, string $hex, string $str, int $alpha = 0, float $angle = 0): array
{
    return imagettftext($im, $size * S, $angle, $x * S, $y * S, alloc($im, $hex, $alpha), $font, $str);
}

/** Rounded pill used for the call to action. */
function pill($im, int $x, int $y, int $w, int $h, int $r, string $hex): void
{
    $x *= S; $y *= S; $w *= S; $h *= S; $r *= S;
    $c = alloc($im, $hex);
    imagefilledrectangle($im, $x + $r, $y, $x + $w - $r, $y + $h, $c);
    imagefilledrectangle($im, $x, $y + $r, $x + $w, $y + $h - $r, $c);
    imagefilledellipse($im, $x + $r, $y + $r, $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $w - $r, $y + $r, $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $r, $y + $h - $r, $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $c);
}

/** Width of a string at a given size, in final coordinates. */
function textWidth(string $font, float $size, string $str): int
{
    $b = imagettfbbox($size * S, 0, $font, $str);
    return (int)round((max($b[2], $b[4]) - min($b[0], $b[6])) / S);
}

/**
 * @param array{eyebrow:string,title:string[],sub:string,cta:string,from:string,to:string,accent:string} $spec
 */
function banner(array $spec, string $file): void
{
    global $FONT_BLACK, $FONT_BLACKI, $FONT_BOLD, $FONT_REG;

    $im = imagecreatetruecolor(W * S, H * S);
    imagealphablending($im, true);

    gradient($im, $spec['from'], $spec['to']);

    // --- pipe bundle, right side -----------------------------------------
    // Echoes PSF's own advertising, which shows bundles of pipe ends.
    // Kept white only: yellow is reserved for the call to action (brief §24).
    $cx = 1600;
    $cy = 385;
    pipeBundle($im, [
        [$cx + 25,  $cy + 45,  178],
        [$cx + 235, $cy - 130, 126],
        [$cx + 190, $cy + 205, 112],
        [$cx - 55,  $cy - 195, 104],
        [$cx - 165, $cy + 165, 88],
        [$cx + 370, $cy + 55,  74],
        [$cx + 95,  $cy - 285, 62],
    ], '#ffffff');

    // --- yellow rule under the heading ----------------------------------
    imagefilledrectangle($im, 110 * S, 452 * S, 258 * S, 461 * S, alloc($im, $spec['accent']));

    // --- left column ------------------------------------------------------
    text($im, $FONT_BOLD, 21, 112, 196, $spec['accent'], mb_strtoupper($spec['eyebrow'], 'UTF-8'));

    // 86px of leading, not 78: accents on capitals (É, À) rise above the cap
    // height and would otherwise touch the line above.
    $y = 296;
    foreach ($spec['title'] as $line) {
        text($im, $FONT_BLACKI, 62, 110, $y, '#ffffff', $line);
        $y += 86;
    }

    text($im, $FONT_REG, 27, 112, 516, '#dbeaff', $spec['sub']);

    // --- call to action ---------------------------------------------------
    $ctaW = textWidth($FONT_BOLD, 24, $spec['cta']) + 76;
    pill($im, 110, 566, $ctaW, 74, 37, $spec['accent']);
    text($im, $FONT_BOLD, 24, 148, 614, '#0a2340', $spec['cta']);

    // --- slogan, bottom left ---------------------------------------------
    text($im, $FONT_REG, 20, 112, 702, '#9dc4f2', 'La qualité par excellence');

    // downscale: this is what antialiases the type and the circles
    $out = imagecreatetruecolor(W, H);
    imagecopyresampled($out, $im, 0, 0, 0, 0, W, H, W * S, H * S);

    imagepng($out, OUT . '/' . $file, 6);
    imagedestroy($im);
    imagedestroy($out);

    echo "  " . $file . "  " . number_format(filesize(OUT . '/' . $file) / 1024, 0) . " KB\n";
}

$banners = [
    [
        'file'    => 'psf-banner-1-accueil.png',
        'eyebrow' => 'Ouagadougou · Boins Yaaré',
        'title'   => ['PLOMBERIE SANITAIRE', 'DU FASO'],
        'sub'     => 'Votre partenaire en plomberie et équipements sanitaires',
        'cta'     => 'VOIR NOS PRODUITS',
        'from'    => '#062546',
        'to'      => '#1478c8',
        'accent'  => '#f5c518',
    ],
    [
        'file'    => 'psf-banner-2-tubes.png',
        'eyebrow' => 'Disponible en stock',
        'title'   => ['TUBES &', 'CANALISATIONS'],
        'sub'     => 'PVC pression · PVC évacuation · PPR · PEHD · Tubes gorgés',
        'cta'     => 'DEMANDER LE PRIX',
        'from'    => '#052a3f',
        'to'      => '#1497c8',
        'accent'  => '#f5c518',
    ],
    [
        'file'    => 'psf-banner-3-robinetterie.png',
        'eyebrow' => 'Robinetterie & douche',
        'title'   => ['MITIGEURS ET', 'COLONNES DE DOUCHE'],
        'sub'     => 'Robinets, mitigeurs, flexibles et accessoires de douche',
        'cta'     => 'COMMANDER SUR WHATSAPP',
        'from'    => '#08325e',
        'to'      => '#1b6fd0',
        'accent'  => '#f5c518',
    ],
    [
        'file'    => 'psf-banner-4-devis.png',
        'eyebrow' => 'Particuliers · Plombiers · Entreprises',
        'title'   => ['UN CHANTIER', 'À ÉQUIPER ?'],
        'sub'     => 'Recevez votre devis rapidement, par WhatsApp ou par e-mail',
        'cta'     => 'DEMANDER UN DEVIS',
        'from'    => '#04203a',
        'to'      => '#0f5fa8',
        'accent'  => '#f5c518',
    ],
];

echo "Generating " . count($banners) . " banners at " . W . "x" . H . " px\n";
foreach ($banners as $b) {
    $file = $b['file'];
    unset($b['file']);
    banner($b, $file);
}
echo "Done -> " . OUT . "\n";
