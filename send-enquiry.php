<?php
// Depri Projects CC: sends the website enquiry form straight to the company inbox.
// Runs on the Afrihost (cPanel) hosting using PHP mail(); no third-party service or activation needed.
// The email is laid out like the form (HTML), with a plain-text copy for older mail apps.

$to   = 'info@fhdepriprojects.com';
$from = 'info@fhdepriprojects.com';
$site = 'https://fhdepriprojects.com';
$back = $site . '/contact.html';

function field($key, $max = 500) {
  $v = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
  return function_exists('mb_substr') ? mb_substr($v, 0, $max) : substr($v, 0, $max);
}
function one_line($v) {
  return str_replace(["\r", "\n"], ' ', $v);  // blocks email header injection
}
function h($v) {
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
function go($url) {
  header('Location: ' . $url, true, 303);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') go($back);

// Spam trap: real visitors never see or fill this field
if (field('website') !== '') go($back . '?sent=1#enquiry');

$route   = one_line(field('route', 100));
$name    = one_line(field('name', 120));
$company = one_line(field('company', 150));
$email   = one_line(field('email', 200));
$phone   = one_line(field('phone', 40));
$product = one_line(field('product', 300));
$message = field('message', 5000);

// Nothing useful filled in at all: just return to the form
if ($name === '' && $message === '' && $email === '') go($back . '#enquiry');

$valid_email = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
$name = $name !== '' ? $name : 'Website visitor';

$route   = $route !== '' ? $route : 'General enquiry';
$sent_at = date('j F Y, H:i');
$subject = "New website enquiry: $route ($name)";

// Rows shown in the email, in the same order as the form
$rows = [
  'Enquiry route'                => $route,
  'Full name'                    => $name,
  'Company'                      => $company,
  'Email'                        => $email,
  'Phone'                        => $phone,
  'Product / service of interest' => $product,
  'Message'                      => $message,
];

// Plain-text version
$text = "New enquiry from fhdepriprojects.com\n\n";
foreach ($rows as $label => $value) {
  $text .= $label . ': ' . ($value !== '' ? $value : '-') . "\n";
}
$text .= "\nReply to this email to respond directly to $name.\nReceived $sent_at\n";

// HTML version, styled like the website form
$cells = '';
foreach ($rows as $label => $value) {
  if ($label === 'Email' && $value !== '') {
    $shown = '<a href="mailto:' . h($value) . '" style="color:#9a6d12">' . h($value) . '</a>';
  } elseif ($label === 'Phone' && $value !== '') {
    $shown = '<a href="tel:' . h(preg_replace('/[^0-9+]/', '', $value)) . '" style="color:#9a6d12">' . h($value) . '</a>';
  } else {
    $shown = $value !== '' ? nl2br(h($value)) : '<span style="color:#9a8f80">Not provided</span>';
  }
  $cells .= '<tr><td style="padding:14px 20px 4px;font:700 11px/1.4 Arial,sans-serif;letter-spacing:.08em;text-transform:uppercase;color:#6f6455">' . h($label) . '</td></tr>'
          . '<tr><td style="padding:0 20px 14px"><div style="border:1px solid #e3dccd;border-radius:8px;padding:10px 12px;background:#ffffff;font:15px/1.5 Arial,sans-serif;color:#2b241c">' . $shown . '</div></td></tr>';
}

$html = '<!doctype html><html><body style="margin:0;padding:24px 12px;background:#faf6ee">'
  . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fdfaf3;border:1px solid #e3dccd;border-radius:12px">'
  . '<tr><td style="padding:22px 20px;border-bottom:3px solid #c9962b">'
  . '<img src="' . $site . '/assets/img/logo-sm.png" width="48" height="48" alt="" style="vertical-align:middle;margin-right:10px">'
  . '<span style="font:700 18px Georgia,serif;color:#1b140c;vertical-align:middle">DEPRI PROJECTS CC</span>'
  . '<div style="font:13px Arial,sans-serif;color:#6f6455;margin-top:8px">New website enquiry &middot; ' . h($sent_at) . '</div></td></tr>'
  . $cells
  . '<tr><td style="padding:16px 20px 22px"><a href="mailto:' . h($email) . '?subject=' . rawurlencode('Re: ' . $subject) . '" '
  . 'style="display:inline-block;background:#c9962b;color:#1b140c;font:700 14px Arial,sans-serif;text-decoration:none;padding:12px 22px;border-radius:999px">Reply to ' . h($name) . '</a>'
  . '<div style="font:12px Arial,sans-serif;color:#9a8f80;margin-top:14px">Sent from the enquiry form on fhdepriprojects.com</div></td></tr>'
  . '</table></body></html>';

$boundary = 'depri_' . md5(uniqid('', true));

$headers  = "From: Depri Projects Website <$from>\r\n";
if ($valid_email) $headers .= "Reply-To: $name <$email>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";

$body  = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
$body .= chunk_split(base64_encode($text)) . "\r\n";
$body .= "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
$body .= chunk_split(base64_encode($html)) . "\r\n";
$body .= "--$boundary--\r\n";

$ok = mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers, '-f' . $from);

go($back . '?sent=1#enquiry');
