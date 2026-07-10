<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
// vcard.php - Downloads a .vcf contact file
require_once 'admin/db_connect.php';

$slug = $_GET['slug'] ?? $_GET['id'] ?? '';
$p = null;

try {
    if (!empty($slug)) {
        $stmt = $pdo->prepare("SELECT * FROM digital_business_cards WHERE card_slug = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$slug]);
        $p = $stmt->fetch();
    }
    
    if (!$p) {
        $p = $pdo->query("SELECT * FROM digital_business_cards WHERE is_default = 1 AND is_active = 1 LIMIT 1")->fetch();
    }
    
    if (!$p) {
        $p = $pdo->query("SELECT * FROM digital_business_cards WHERE is_active = 1 LIMIT 1")->fetch();
    }
    
    if (!$p) {
        $p = $pdo->query("SELECT * FROM executive_profile_settings LIMIT 1")->fetch();
    }
} catch (Exception $e) {
    $p = [];
}

$name     = $p['full_name']       ?? 'Shawn Radam';
$title    = $p['title']            ?? 'Private Advisor';
$company  = $p['company_name']     ?? 'Shawn Radam Advisory';
$phone1   = $p['phone_primary']    ?? '0128338639';
$phone2   = $p['phone_secondary']  ?? '01116339399';
$email    = $p['email_address']    ?? '';
$address  = $p['address_line']     ?? 'Kota Kinabalu, Sabah, Malaysia';
$website  = $p['website_url']      ?? 'https://shawnradam.com';
$portrait = $p['portrait_url']     ?? '';
$bio      = $p['card_bio']         ?? 'Personal Advisor specialising in Asset Acquisition, Structured Lending & Travel Logistics.';

// Safe filename
$filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) . '.vcf';

// Build vCard 3.0
$vcf  = "BEGIN:VCARD\r\n";
$vcf .= "VERSION:3.0\r\n";
$vcf .= "FN:{$name}\r\n";

// Split name into first/last
$parts = explode(' ', $name, 2);
$last  = $parts[1] ?? '';
$first = $parts[0] ?? $name;
$vcf .= "N:{$last};{$first};;;\r\n";

$vcf .= "ORG:{$company}\r\n";
$vcf .= "TITLE:{$title}\r\n";
if ($phone1) $vcf .= "TEL;TYPE=CELL,PREF:{$phone1}\r\n";
if ($phone2) $vcf .= "TEL;TYPE=CELL:{$phone2}\r\n";
if ($email)  $vcf .= "EMAIL;TYPE=INTERNET,PREF:{$email}\r\n";
if ($address) $vcf .= "ADR;TYPE=WORK:;;{$address};;;;\r\n";
if ($website) $vcf .= "URL:{$website}\r\n";
if ($bio)     $vcf .= "NOTE:{$bio}\r\n";

// Embed portrait if local & readable
if ($portrait && !str_starts_with($portrait, 'http')) {
    $imgPath = __DIR__ . '/' . ltrim($portrait, '/');
    if (file_exists($imgPath)) {
        $imgData   = base64_encode(file_get_contents($imgPath));
        $extension = strtolower(pathinfo($imgPath, PATHINFO_EXTENSION));
        $mimeType  = $extension === 'png' ? 'PNG' : 'JPEG';
        $vcf .= "PHOTO;ENCODING=b;TYPE={$mimeType}:{$imgData}\r\n";
    }
}

$vcf .= "END:VCARD\r\n";

// Force download
header('Content-Type: text/vcard; charset=utf-8');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Content-Length: ' . strlen($vcf));
header('Cache-Control: no-cache, must-revalidate');
echo $vcf;
exit;
