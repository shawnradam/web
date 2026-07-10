<?php
/**
 * Property & Land Calculator Plugin
 * PHP integration helper class.
 */

class PropertyCalculator {
    
    public static function register_assets($baseUrl = 'plugins/property-calculator') {
        $baseUrl = rtrim($baseUrl, '/');
        echo "\n<!-- Property Calculator Assets -->\n";
        echo '<link rel="stylesheet" href="' . htmlspecialchars($baseUrl) . '/property-calculator.css">' . "\n";
        echo '<script src="' . htmlspecialchars($baseUrl) . '/property-calculator.js"></script>' . "\n";
    }

    public static function render($elementId = 'property-calculator-widget', $options = []) {
        $dbPresets = self::get_presets_from_db();
        if ($dbPresets !== null) {
            $options['presets'] = $dbPresets;
        }

        $jsonOptions = !empty($options) ? json_encode($options, JSON_PRETTY_PRINT) : '{}';
        
        $html = "\n<div id=\"" . htmlspecialchars($elementId) . "\" class=\"property-calculator-target-container\"></div>\n";
        $html .= "<script>\n";
        $html .= "  document.addEventListener('DOMContentLoaded', function() {\n";
        $html .= "    if (window.PropertyCalculator) {\n";
        $html .= "      window.PropertyCalculator.init(" . json_encode($elementId) . ", " . $jsonOptions . ");\n";
        $html .= "    } else {\n";
        $html .= "      console.error('PropertyCalculator JS not loaded.');\n";
        $html .= "    }\n";
        $html .= "  });\n";
        $html .= "</script>\n";
        
        return $html;
    }

    private static function get_presets_from_db() {
        if (!defined('DB_HOST')) {
            $envPath = __DIR__ . '/../../php/env.php';
            if (!file_exists($envPath)) {
                return null;
            }
            require_once $envPath;
        }

        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            $tableCheck = $pdo->query("SHOW TABLES LIKE 'property_calculator_presets'");
            if ($tableCheck->rowCount() === 0) {
                return null;
            }

            $rows = $pdo->query("SELECT * FROM property_calculator_presets WHERE is_active = 1 ORDER BY display_order ASC, name ASC")->fetchAll();
            if (empty($rows)) {
                return null;
            }

            $presets = [];
            foreach ($rows as $row) {
                $presets[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'region' => $row['region'],
                    'interestRateDefault' => floatval($row['interest_rate_default']),
                    'minPrice' => floatval($row['min_price']),
                    'maxPrice' => floatval($row['max_price']),
                    'minTenure' => intval($row['min_tenure']),
                    'maxTenure' => intval($row['max_tenure']),
                    'downPaymentPct' => floatval($row['down_payment_pct']),
                    'premiumRate' => floatval($row['premium_rate']),
                    'notes' => $row['notes']
                ];
            }
            return $presets;

        } catch (Exception $ex) {
            return null;
        }
    }
}
?>
