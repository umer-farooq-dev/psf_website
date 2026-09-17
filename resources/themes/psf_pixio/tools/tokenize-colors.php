<?php
/**
 * PSF — turns the Pixio template's fixed colours into CSS variables.
 *
 * The template ships its brand red (#CC0D39), its black "secondary" and its
 * cream backgrounds as literal values, many of them inside the compiled
 * Bootstrap. Left as they are they would ignore the admin colours. This
 * rewrites them to var(--psf-*) tokens, which the layout defines from the
 * panel (Paramètres PSF → Design).
 *
 * Idempotent: running it again on an already tokenised file changes nothing.
 *
 *   php resources/themes/psf_pixio/tools/tokenize-colors.php
 */

$file = __DIR__ . '/../public/assets/css/style.css';
$css = file_get_contents($file);
if ($css === false) {
    fwrite(STDERR, "style.css not found\n");
    exit(1);
}

$before = $css;
$count = 0;

$rules = [
    // brand primary and its shades
    '/#cc0d39\b/i'                              => 'var(--psf-primary)',
    '/#ad0b30\b/i'                              => 'var(--psf-primary-hover)',
    '/#f11b4d\b/i'                              => 'var(--psf-primary-hover)',
    '/#3c0411\b/i'                              => 'var(--psf-primary-dark)',
    '/rgba\(\s*204\s*,\s*13\s*,\s*57\s*,/i'     => 'rgba(var(--psf-primary-rgb),',
    '/(--bs-(?:primary|link-color|btn-focus-shadow)-rgb:\s*)204\s*,\s*13\s*,\s*57/i' => '$1var(--psf-primary-rgb)',

    // page backgrounds
    '/#fffaf3\b/i'                              => 'var(--psf-light)',
    '/#feeb9d\b/i'                              => 'var(--psf-light-dark)',

    // body text and borders (Paramètres PSF → design colours)
    '/#5e626f\b/i'                              => 'var(--psf-body-text)',
    '/#d7d7d7\b/i'                              => 'var(--psf-border)',

    // keyboard focus ring
    '/outline: 2px solid red !important;/'      => 'outline: 2px solid var(--psf-primary) !important;',
];

foreach ($rules as $pattern => $replacement) {
    $css = preg_replace($pattern, $replacement, $css, -1, $n);
    $count += $n;
}

// Names typed in the panel keep the case they were typed in: "capitalize"
// turns "PPR 90° elbow, 25 mm" into "PPR 90° Elbow, 25 Mm" and French
// "Chauffe-eau" into "Chauffe-Eau".
$css = preg_replace('/text-transform:\s*capitalize\s*(;|\})/i', 'text-transform: none$1', $css, -1, $n);
$count += $n;

// Secondary (the template's black buttons). "#000" is far too common to
// replace everywhere, so only the declarations that define "secondary".
$secondaryRules = [
    '/(--bs-secondary:\s*)#000\b/'           => '$1var(--psf-secondary)',
    '/(--bs-secondary-rgb:\s*)0\s*,\s*0\s*,\s*0/' => '$1var(--psf-secondary-rgb)',
    '/(\n\s*--secondary:\s*)#000\b/'         => '$1var(--psf-secondary)',
    '/(\n\s*--title:\s*)#000\b/'             => '$1var(--psf-title)',
];
foreach ($secondaryRules as $pattern => $replacement) {
    $css = preg_replace($pattern, $replacement, $css, -1, $n);
    $count += $n;
}

// Inside the compiled .btn-secondary / .btn-outline-secondary blocks, every
// black is the secondary colour.
$css = preg_replace_callback(
    '/\.btn-(?:outline-)?secondary\s*\{[^}]*\}/',
    function ($m) use (&$count) {
        $block = preg_replace('/:\s*(?:#000|black)\s*;/', ': var(--psf-secondary);', $m[0], -1, $n1);
        $block = preg_replace('/(--bs-btn-focus-shadow-rgb:\s*)(?:0\s*,\s*0\s*,\s*0|38\s*,\s*38\s*,\s*38)/', '$1var(--psf-secondary-rgb)', $block, -1, $n2);
        $count += $n1 + $n2;
        return $block;
    },
    $css
);

// The template's own :root must now point at the panel tokens, never at a colour.
$css = preg_replace('/(\n\s*--primary:\s*)var\(--psf-primary\)/', '$1var(--psf-primary)', $css);

if ($css === $before) {
    echo "Nothing to change (already tokenised).\n";
    exit(0);
}

file_put_contents($file, $css);
echo "Tokenised {$count} colour values in style.css\n";
