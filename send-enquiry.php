<?php
// Depri Projects CC: sends the website enquiry form straight to the company inbox.
// Runs on the Afrihost (cPanel) hosting using PHP mail(); no third-party service or activation needed.

$to       = 'info@fhdepriprojects.com';
$from     = 'info@fhdepriprojects.com';
$back     = '/contact.html';

function field($key, $max = 500) {
  $v = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
  return function_exists('mb_substr') ? mb_substr($v, 0, $max) : substr($v, 0, $max);
}
function one_line($v) {
  return str_replace(["\r", "\n"], ' ', $v);  // blocks email header injection
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

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  go($back . '?error=1#enquiry');
}

$subject = 'New website enquiry: ' . ($route !== '' ? $route : 'General enquiry') . ' (' . $name . ')';

$body  = "New enquiry from fhdepriprojects.com\n";
$body .= "------------------------------------\n\n";
$body .= "Enquiry route:  $route\n";
$body .= "Name:           $name\n";
$body .= "Company:        $company\n";
$body .= "Email:          $email\n";
$body .= "Phone:          $phone\n";
$body .= "Product/service: $product\n\n";
$body .= "Message:\n$message\n\n";
$body .= "------------------------------------\n";
$body .= "Reply to this email to respond directly to $name.\n";
$body .= 'Sent ' . date('Y-m-d H:i') . ' from IP ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

$headers  = "From: Depri Projects Website <$from>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$ok = mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers, '-f' . $from);

go($back . ($ok ? '?sent=1#enquiry' : '?error=1#enquiry'));
