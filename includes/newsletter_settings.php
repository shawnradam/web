<?php
if (!function_exists('sr_newsletter_default_settings')) {
    function sr_newsletter_default_settings(): array
    {
        return [
            'newsletter_footer_kicker' => 'Property Notes',
            'newsletter_footer_title' => 'Get useful property, land, and loan updates.',
            'newsletter_footer_description' => 'Simple updates for Sabah property decisions, loan readiness, land lot checks, and website announcements.',
            'newsletter_footer_label' => 'Email Address',
            'newsletter_footer_placeholder' => 'you@example.com',
            'newsletter_footer_button' => 'Subscribe',
            'newsletter_footer_note' => 'No spam. Only practical updates about property, financing, land, and website news.',
            'newsletter_popup_title' => 'Stay Updated',
            'newsletter_popup_description' => 'Get practical Sabah property and financing updates directly to your inbox.',
            'newsletter_popup_placeholder' => 'your.email@example.com',
            'newsletter_popup_button' => 'Subscribe Free',
            'newsletter_success_message' => 'Thank you for subscribing.',
            'newsletter_duplicate_message' => 'You are already subscribed.',
        ];
    }
}

if (!function_exists('sr_newsletter_settings')) {
    function sr_newsletter_settings(?PDO $pdo = null): array
    {
        $settings = sr_newsletter_default_settings();

        try {
            if (!$pdo) {
                require_once __DIR__ . '/../admin/db_connect.php';
            }

            if (isset($pdo)) {
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'newsletter_%'");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (array_key_exists($row['setting_key'], $settings) && trim((string) $row['setting_value']) !== '') {
                        $settings[$row['setting_key']] = $row['setting_value'];
                    }
                }
            }
        } catch (Throwable $e) {
            // Keep defaults when settings are unavailable.
        }

        return $settings;
    }
}