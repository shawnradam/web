<?php
// includes/analytics_tracking.php
// Reusable tracking script for page views

if (!isset($pdo)) {
    require_once __DIR__ . '/../admin/db_connect.php';
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page URL
$current_page = $_SERVER['REQUEST_URI'];

// Create unique visitor hash (IP + User Agent)
$visitor_hash = hash('sha256', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

// Check if this visitor has viewed this page in this session
$session_key = 'viewed_' . md5($current_page);

if (!isset($_SESSION[$session_key])) {
    // Get visitor IP
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Get country from IP (using free ipapi.co service)
    $country = 'Unknown';
    try {
        $geo_data = @file_get_contents("https://ipapi.co/{$ip_address}/json/");
        if ($geo_data) {
            $geo = json_decode($geo_data, true);
            $country = $geo['country_name'] ?? 'Unknown';
        }
    } catch (Exception $e) {
        // Silently fail if geolocation service is unavailable
    }

    // Get user agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Get referrer
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';

    // Insert page view
    try {
        $stmt = $pdo->prepare("INSERT INTO page_views (page_url, visitor_hash, ip_address, country, user_agent, referrer) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$current_page, $visitor_hash, $ip_address, $country, $user_agent, $referrer]);

        // Mark as viewed in session
        $_SESSION[$session_key] = true;
    } catch (PDOException $e) {
        // Silently fail - don't break the page if tracking fails
    }
}
?>