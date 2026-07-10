<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
// includes/download_vcard.php
require_once '../admin/db_connect.php';

if (isset($_GET['id'])) {
    // If ID specific, though we usually just have one active card
    $stmt = $pdo->prepare("SELECT * FROM digital_card_settings WHERE id = ?");
    $stmt->execute([$_GET['id']]);
} else {
    // Get active card
    $stmt = $pdo->query("SELECT * FROM digital_card_settings WHERE is_active = 1 LIMIT 1");
}

$card = $stmt->fetch();

if (!$card) {
    die("Digital Business Card not found or inactive.");
}

// Build vCard Content
$vcard = "BEGIN:VCARD\r\n";
$vcard .= "VERSION:3.0\r\n";
$vcard .= "N:" . $card['full_name'] . "\r\n";
$vcard .= "FN:" . $card['full_name'] . "\r\n";
if (!empty($card['title']))
    $vcard .= "TITLE:" . $card['title'] . "\r\n";
if (!empty($card['email']))
    $vcard .= "EMAIL;TYPE=INTERNET,WORK:" . $card['email'] . "\r\n";
if (!empty($card['phone']))
    $vcard .= "TEL;TYPE=CELL:" . $card['phone'] . "\r\n";
if (!empty($card['whatsapp_number']))
    $vcard .= "TEL;TYPE=WHATSAPP:" . $card['whatsapp_number'] . "\r\n";
if (!empty($card['website_url']))
    $vcard .= "URL;TYPE=WORK:" . $card['website_url'] . "\r\n";
if (!empty($card['linkedin_url']))
    $vcard .= "URL;TYPE=LINKEDIN:" . $card['linkedin_url'] . "\r\n";
if (!empty($card['bio']))
    $vcard .= "NOTE:" . str_replace(["\r", "\n"], " ", $card['bio']) . "\r\n";

// Handle Avatar (Optional: embed base64) - keeping simple for now
// if (!empty($card['avatar_url'])) {
//     $path = '../' . $card['avatar_url'];
//     if (file_exists($path)) {
//         $type = strtoupper(pathinfo($path, PATHINFO_EXTENSION));
//         $b64 = base64_encode(file_get_contents($path));
//         $vcard .= "PHOTO;ENCODING=b;TYPE=" . $type . ":" . $b64 . "\r\n";
//     }
// }

$vcard .= "END:VCARD\r\n";

// Force Download
header('Content-Type: text/x-vcard; charset=utf-8');
header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $card['full_name']) . '_contact.vcf"');
header('Content-Length: ' . strlen($vcard));
header('Connection: close');

echo $vcard;
?>