<?php
// includes/captcha.php - Secure Alphanumeric CAPTCHA Image Generator (Serverless & Local Compatible)
require_once __DIR__ . '/../config/db.php';

if (ob_get_level() > 0) {
    @ob_clean();
}

// Anti-caching HTTP headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// Generate 5-character random alphanumeric string (avoiding ambiguous chars like 0, O, 1, I, L)
$allowed_chars = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
$captcha_length = 5;
$captcha_code = '';
$max_idx = strlen($allowed_chars) - 1;

for ($i = 0; $i < $captcha_length; $i++) {
    $captcha_code .= $allowed_chars[random_int(0, $max_idx)];
}

$type = strtolower(trim($_GET['type'] ?? 'default'));

// Store in session + signed cookie pool for serverless & multi-instance verification
if (function_exists('storeCaptchaCode')) {
    storeCaptchaCode($captcha_code, $type);
} else {
    $_SESSION['student_reg_captcha'] = $captcha_code;
    $_SESSION['captcha_code'] = $captcha_code;
}

// Flush session before rendering output
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$width = 160;
$height = 46;

$text_colors = ['#641914', '#1e293b', '#b45309', '#450f0c'];
$noise_colors = ['#e6c8a0', '#dcd2be', '#c8a08c'];

header('Content-Type: image/svg+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<svg xmlns="http://www.w3.org/2000/svg" width="<?= $width ?>" height="<?= $height ?>" viewBox="0 0 <?= $width ?> <?= $height ?>">
  <rect width="100%" height="100%" fill="#fbf3de" stroke="#d97706" stroke-width="1.5" rx="6"/>
  <?php for ($i = 0; $i < 6; $i++):
    $nc = $noise_colors[array_rand($noise_colors)];
    $x1 = random_int(2, $width - 2);
    $y1 = random_int(2, $height - 2);
    $x2 = random_int(2, $width - 2);
    $y2 = random_int(2, $height - 2);
  ?>
  <line x1="<?= $x1 ?>" y1="<?= $y1 ?>" x2="<?= $x2 ?>" y2="<?= $y2 ?>" stroke="<?= $nc ?>" stroke-width="1.2" opacity="0.85"/>
  <?php endfor; ?>
  <?php for ($i = 0; $i < 45; $i++):
    $nc = $noise_colors[array_rand($noise_colors)];
    $cx = random_int(3, $width - 3);
    $cy = random_int(3, $height - 3);
  ?>
  <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="1.1" fill="<?= $nc ?>" opacity="0.9"/>
  <?php endfor; ?>
  <?php for ($i = 0; $i < $captcha_length; $i++):
    $char = htmlspecialchars($captcha_code[$i], ENT_QUOTES, 'UTF-8');
    $color = $text_colors[$i % count($text_colors)];
    $x = 22 + ($i * 27);
    $y = random_int(28, 33);
    $rot = random_int(-14, 14);
  ?>
  <text x="<?= $x + 1 ?>" y="<?= $y + 1 ?>" font-family="monospace, 'Courier New', sans-serif" font-size="22" font-weight="900" fill="#e6d2b4" transform="rotate(<?= $rot ?> <?= $x ?> <?= $y ?>)"><?= $char ?></text>
  <text x="<?= $x ?>" y="<?= $y ?>" font-family="monospace, 'Courier New', sans-serif" font-size="22" font-weight="900" fill="<?= $color ?>" transform="rotate(<?= $rot ?> <?= $x ?> <?= $y ?>)"><?= $char ?></text>
  <?php endfor; ?>
  <path d="M 8 <?= random_int(16, 30) ?> Q 50 <?= random_int(10, 36) ?> 90 <?= random_int(14, 32) ?> T 152 <?= random_int(16, 30) ?>" fill="none" stroke="#8c3c28" stroke-width="1.3" opacity="0.65"/>
</svg>
<?php
exit;
