<?php
declare(strict_types=1);

/*
EMAF205 Landing
A minimal FTP-first landing page for offers, workshops, launches and small projects.

Created by EmaF205
Generative AI professor in Milan
https://linktr.ee/emaf205
Contact: emagumroad@gmail.com

Runtime files:
- index.php
- page.txt
- style.txt
- custom.css optional
*/

const EMAF_MAX_FILE_SIZE = 1048576;

function emaf_read_file(string $path): string {
    if (!is_file($path) || !is_readable($path)) {
        return '';
    }
    $size = filesize($path);
    if ($size === false || $size > EMAF_MAX_FILE_SIZE) {
        return '';
    }
    $data = file_get_contents($path);
    return is_string($data) ? $data : '';
}

function emaf_clean_key(string $key): string {
    $key = strtoupper(trim($key));
    return preg_replace('/[^A-Z0-9_]/', '', $key) ?? '';
}

function emaf_parse_page(string $raw): array {
    $data = [
        'fields' => [],
        'points' => [],
        'steps' => [],
        'details' => [],
    ];

    $currentType = null;
    $currentIndex = null;

    $lines = preg_split('/\R/', $raw) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, ':');
        if ($pos === false) {
            continue;
        }

        $key = emaf_clean_key(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        if ($key === '') {
            continue;
        }

        if ($key === 'POINT') {
            $data['points'][] = ['ID' => $value, 'TITLE' => '', 'TEXT' => ''];
            $currentType = 'points';
            $currentIndex = count($data['points']) - 1;
            continue;
        }

        if ($key === 'STEP') {
            $data['steps'][] = ['ID' => $value, 'TITLE' => '', 'TEXT' => ''];
            $currentType = 'steps';
            $currentIndex = count($data['steps']) - 1;
            continue;
        }

        if ($key === 'DETAIL') {
            $data['details'][] = ['ID' => $value, 'TITLE' => '', 'TEXT' => ''];
            $currentType = 'details';
            $currentIndex = count($data['details']) - 1;
            continue;
        }

        if (($key === 'TITLE' || $key === 'TEXT') && $currentType !== null && $currentIndex !== null) {
            $data[$currentType][$currentIndex][$key] = $value;
            continue;
        }

        $data['fields'][$key] = $value;
        $currentType = null;
        $currentIndex = null;
    }

    return $data;
}

function emaf_parse_style(string $raw): array {
    $style = [];
    $lines = preg_split('/\R/', $raw) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, ':');
        if ($pos === false) {
            continue;
        }
        $key = emaf_clean_key(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if ($key !== '') {
            $style[$key] = $value;
        }
    }
    return $style;
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function field(array $fields, string $key, string $fallback = ''): string {
    $value = $fields[$key] ?? '';
    $value = is_string($value) ? trim($value) : '';
    return $value !== '' ? $value : $fallback;
}

function css_color(array $style, string $key, string $fallback): string {
    $value = trim((string)($style[$key] ?? ''));
    if ($value === '') {
        return $fallback;
    }
    if (preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
        return $value;
    }
    if (in_array(strtolower($value), ['transparent', 'black', 'white'], true)) {
        return strtolower($value);
    }
    return $fallback;
}

function css_font(array $style): string {
    $font = strtolower(trim((string)($style['FONT'] ?? '')));
    if ($font === 'sans') {
        return "Arial, Helvetica, sans-serif";
    }
    return "Georgia, 'Times New Roman', serif";
}

function safe_url(string $url): string {
    $url = trim($url);
    if ($url === '') {
        return '#';
    }

    $lower = strtolower(preg_replace('/\s+/', '', $url) ?? $url);
    if (
        str_starts_with($lower, 'javascript:') ||
        str_starts_with($lower, 'data:') ||
        str_starts_with($lower, 'vbscript:')
    ) {
        return '#';
    }

    if (preg_match('/^(https?:\/\/|mailto:|tel:|\/|#|\.\.?\/)/i', $url)) {
        return $url;
    }

    return '#';
}

function safe_target(string $target): string {
    $target = trim($target);
    return in_array($target, ['_self', '_blank', '_parent', '_top'], true) ? $target : '_self';
}

function has_items(array $items): bool {
    foreach ($items as $item) {
        if (trim((string)($item['TITLE'] ?? '')) !== '' || trim((string)($item['TEXT'] ?? '')) !== '') {
            return true;
        }
    }
    return false;
}

function render_button(string $text, string $url, string $target): void {
    $text = trim($text);
    if ($text === '') {
        return;
    }
    $safeTarget = safe_target($target);
    $rel = $safeTarget === '_blank' ? ' rel="noopener noreferrer"' : '';
    echo '<a class="button" href="' . e(safe_url($url)) . '" target="' . e($safeTarget) . '"' . $rel . '>' . e($text) . '</a>';
}

function render_items(array $items, string $sectionClass, string $itemClass, string $label): void {
    if (!has_items($items)) {
        return;
    }
    echo '<section class="' . e($sectionClass) . '" aria-label="' . e($label) . '">';
    foreach ($items as $item) {
        $title = trim((string)($item['TITLE'] ?? ''));
        $text = trim((string)($item['TEXT'] ?? ''));
        if ($title === '' && $text === '') {
            continue;
        }
        echo '<article class="' . e($itemClass) . '">';
        if ($title !== '') {
            echo '<h3>' . e($title) . '</h3>';
        }
        if ($text !== '') {
            echo '<p>' . e($text) . '</p>';
        }
        echo '</article>';
    }
    echo '</section>';
}

$defaultPage = <<<'TXT'
SITE_TITLE: EMAF205 Landing
SITE_DESCRIPTION: A minimal FTP-first landing page.

EYEBROW: Milan · Simple · Independent
HERO_TITLE: Launch a clear landing page without a database.
HERO_TEXT: Edit one text file, adjust a few style settings and upload everything by FTP.

CTA_TEXT: Contact
CTA_URL: mailto:emagumroad@gmail.com
CTA_TARGET: _self

POINT: simple
TITLE: Edit text, not code
TEXT: Change the landing content from page.txt.

POINT: focused
TITLE: One strong page
TEXT: A fixed structure designed for clear offers and small launches.

POINT: portable
TITLE: Upload by FTP
TEXT: No database, no admin panel and no build step.

STEP: edit
TITLE: Edit page.txt
TEXT: Write your headline, call to action, points, steps and details.

STEP: style
TITLE: Adjust style.txt
TEXT: Choose colors and font family without touching the HTML.

STEP: upload
TITLE: Upload the files
TEXT: Put the files on your server and open the page in a browser.

DETAIL: files
TITLE: Files
TEXT: index.php, page.txt, style.txt and optional custom.css.

DETAIL: usage
TITLE: Usage
TEXT: Workshops, services, offers, downloads, events and small projects.

DETAIL: creator
TITLE: Creator
TEXT: Created by EmaF205, generative AI professor in Milan.

FINAL_TITLE: Ready to publish something clear?
FINAL_TEXT: Replace the sample text with your real offer and upload the files.
FINAL_CTA_TEXT: Start editing
FINAL_CTA_URL: page.txt
FINAL_CTA_TARGET: _self

FOOTER_TEXT: EmaF205 · Milan · emagumroad@gmail.com
TXT;

$defaultStyle = <<<'TXT'
BACKGROUND_COLOR: #ffffff
TEXT_COLOR: #111111
MUTED_TEXT_COLOR: #666666
FONT: serif
BUTTON_BACKGROUND: transparent
BUTTON_TEXT_COLOR: #111111
BUTTON_BORDER_COLOR: #111111
LINE_COLOR: #111111
TXT;

$pageRaw = emaf_read_file(__DIR__ . '/page.txt');
if ($pageRaw === '') {
    $pageRaw = $defaultPage;
}
$styleRaw = emaf_read_file(__DIR__ . '/style.txt');
if ($styleRaw === '') {
    $styleRaw = $defaultStyle;
}

$page = emaf_parse_page($pageRaw);
$fields = $page['fields'];
$style = emaf_parse_style($styleRaw);

$title = field($fields, 'SITE_TITLE', 'EMAF205 Landing');
$description = field($fields, 'SITE_DESCRIPTION', 'A minimal FTP-first landing page.');

$bg = css_color($style, 'BACKGROUND_COLOR', '#ffffff');
$fg = css_color($style, 'TEXT_COLOR', '#111111');
$muted = css_color($style, 'MUTED_TEXT_COLOR', '#666666');
$buttonBg = css_color($style, 'BUTTON_BACKGROUND', 'transparent');
$buttonText = css_color($style, 'BUTTON_TEXT_COLOR', $fg);
$buttonBorder = css_color($style, 'BUTTON_BORDER_COLOR', $fg);
$line = css_color($style, 'LINE_COLOR', $fg);
$font = css_font($style);

$hasCustomCss = is_file(__DIR__ . '/custom.css') && is_readable(__DIR__ . '/custom.css');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<style>
:root{
  --bg:<?= e($bg) ?>;
  --fg:<?= e($fg) ?>;
  --muted:<?= e($muted) ?>;
  --line:<?= e($line) ?>;
  --button-bg:<?= e($buttonBg) ?>;
  --button-text:<?= e($buttonText) ?>;
  --button-border:<?= e($buttonBorder) ?>;
}
*{box-sizing:border-box}
html,body{min-height:100%}
body{
  margin:0;
  background:var(--bg);
  color:var(--fg);
  font-family:<?= e($font) ?>;
  font-weight:400;
  padding:28px 16px;
}
.page{width:min(100%,820px);margin:0 auto}
.hero{
  min-height:72vh;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  text-align:center;
  padding:34px 0 54px;
}
.eyebrow{
  margin:0 0 20px;
  color:var(--muted);
  font-size:13px;
  line-height:1.35;
  text-transform:uppercase;
  letter-spacing:.08em;
}
h1{
  margin:0;
  max-width:760px;
  font-size:clamp(48px,10vw,98px);
  line-height:.9;
  letter-spacing:-.065em;
  font-weight:400;
}
.hero-text{
  max-width:620px;
  margin:26px auto 0;
  color:var(--muted);
  font-size:21px;
  line-height:1.36;
}
.button{
  width:min(100%,340px);
  min-height:58px;
  margin:34px auto 0;
  padding:14px 18px;
  border:1px solid var(--button-border);
  border-radius:0;
  background:var(--button-bg);
  color:var(--button-text);
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  text-decoration:none;
  text-transform:uppercase;
  letter-spacing:.08em;
  font-size:13px;
  line-height:1.15;
  font-weight:400;
}
.button:hover{background:var(--fg);color:var(--bg)}
.points{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:14px;
  padding:0 0 54px;
}
.point-item{
  border:1px solid var(--line);
  padding:22px;
  min-height:190px;
}
h3{
  margin:0;
  font-size:25px;
  line-height:1.08;
  letter-spacing:-.025em;
  font-weight:400;
}
.point-item p,.step-item p,.detail-item p{
  margin:12px 0 0;
  color:var(--muted);
  font-size:15px;
  line-height:1.45;
}
.steps{border-top:1px solid var(--line)}
.step-item{
  display:grid;
  grid-template-columns:1fr 1.4fr;
  gap:28px;
  padding:26px 0;
  border-bottom:1px solid var(--line);
}
.details{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:14px;
  padding:54px 0;
  border-bottom:1px solid var(--line);
}
.final{
  padding:58px 0 24px;
  text-align:center;
}
.final h2{
  margin:0;
  font-size:clamp(38px,8vw,72px);
  line-height:.92;
  letter-spacing:-.055em;
  font-weight:400;
}
.final p{
  max-width:560px;
  margin:20px auto 0;
  color:var(--muted);
  font-size:18px;
  line-height:1.4;
}
footer{
  margin-top:42px;
  color:var(--muted);
  font-size:13px;
  line-height:1.4;
  text-align:center;
}
@media(max-width:720px){
  body{padding:24px 16px}
  .hero{min-height:auto;padding:46px 0 44px}
  h1{font-size:clamp(44px,15vw,72px)}
  .hero-text{font-size:18px}
  .button{width:100%;min-height:56px}
  .points{grid-template-columns:1fr;gap:10px;padding-bottom:42px}
  .point-item{min-height:auto;padding:20px}
  .step-item{grid-template-columns:1fr;gap:8px;padding:23px 0}
  .details{grid-template-columns:1fr;gap:22px;padding:42px 0}
  .final{padding:44px 0 18px}
}
</style>
<?php if ($hasCustomCss): ?>
<link rel="stylesheet" href="custom.css">
<?php endif; ?>
</head>
<body>
<main class="page">
  <header class="hero">
    <?php if (field($fields, 'EYEBROW') !== ''): ?>
      <p class="eyebrow"><?= e(field($fields, 'EYEBROW')) ?></p>
    <?php endif; ?>
    <h1><?= e(field($fields, 'HERO_TITLE', 'Launch a clear landing page without a database.')) ?></h1>
    <?php if (field($fields, 'HERO_TEXT') !== ''): ?>
      <p class="hero-text"><?= e(field($fields, 'HERO_TEXT')) ?></p>
    <?php endif; ?>
    <?php render_button(field($fields, 'CTA_TEXT'), field($fields, 'CTA_URL'), field($fields, 'CTA_TARGET', '_self')); ?>
  </header>

  <?php render_items($page['points'], 'points', 'point-item', 'Key points'); ?>

  <?php render_items($page['steps'], 'steps', 'step-item', 'How it works'); ?>

  <?php render_items($page['details'], 'details', 'detail-item', 'Details'); ?>

  <?php if (field($fields, 'FINAL_TITLE') !== '' || field($fields, 'FINAL_TEXT') !== '' || field($fields, 'FINAL_CTA_TEXT') !== ''): ?>
    <section class="final">
      <?php if (field($fields, 'FINAL_TITLE') !== ''): ?>
        <h2><?= e(field($fields, 'FINAL_TITLE')) ?></h2>
      <?php endif; ?>
      <?php if (field($fields, 'FINAL_TEXT') !== ''): ?>
        <p><?= e(field($fields, 'FINAL_TEXT')) ?></p>
      <?php endif; ?>
      <?php render_button(field($fields, 'FINAL_CTA_TEXT'), field($fields, 'FINAL_CTA_URL'), field($fields, 'FINAL_CTA_TARGET', '_self')); ?>
    </section>
  <?php endif; ?>

  <?php if (field($fields, 'FOOTER_TEXT') !== ''): ?>
    <footer><?= e(field($fields, 'FOOTER_TEXT')) ?></footer>
  <?php endif; ?>
</main>
</body>
</html>
