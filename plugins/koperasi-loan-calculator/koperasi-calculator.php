<?php
/**
 * Koperasi Personal Loan Calculator
 * PHP integration helper class.
 */

class KoperasiLoanCalculator {
    
    public static function register_assets($baseUrl = 'plugins/koperasi-loan-calculator') {
        $baseUrl = rtrim($baseUrl, '/');
        echo "\n<!-- Koperasi Loan Calculator Assets -->\n";
        echo '<link rel="stylesheet" href="' . htmlspecialchars($baseUrl) . '/koperasi-calculator.css">' . "\n";
        echo '<script src="' . htmlspecialchars($baseUrl) . '/koperasi-calculator.js"></script>' . "\n";
    }

    public static function render($elementId = 'koperasi-loan-calculator-widget', $options = []) {
        $dbPresets = self::get_presets_from_db();
        if ($dbPresets !== null) {
            $options['presets'] = $dbPresets;
        }

        $jsonOptions = !empty($options) ? json_encode($options, JSON_PRETTY_PRINT) : '{}';
        
        $html = "\n<div id=\"" . htmlspecialchars($elementId) . "\" class=\"koperasi-calculator-target-container\"></div>\n";
        $html .= "<script>\n";
        $html .= "  document.addEventListener('DOMContentLoaded', function() {\n";
        $html .= "    if (window.KoperasiCalculator) {\n";
        $html .= "      window.KoperasiCalculator.init(" . json_encode($elementId) . ", " . $jsonOptions . ");\n";
        $html .= "    } else {\n";
        $html .= "      console.error('KoperasiCalculator JS not loaded.');\n";
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

            // Return default values if table does not exist
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'koperasi_presets'");
            if ($tableCheck->rowCount() === 0) {
                return null;
            }

            $rows = $pdo->query("SELECT * FROM koperasi_presets")->fetchAll();
            if (empty($rows)) {
                return null;
            }

            $presets = [];
            foreach ($rows as $row) {
                $presets[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'interestRate' => floatval($row['interest_rate']),
                    'minLoan' => intval($row['min_loan']),
                    'maxLoan' => intval($row['max_loan']),
                    'minTenure' => intval($row['min_tenure']),
                    'maxTenure' => intval($row['max_tenure']),
                    'processingFeePercent' => floatval($row['processing_fee_percent']),
                    'insurancePercent' => floatval($row['insurance_percent']),
                    'membershipFee' => floatval($row['membership_fee']),
                    'advancePaymentMonths' => intval($row['advance_payment_months'])
                ];
            }
            return $presets;

        } catch (Exception $ex) {
            return null;
        }
    }
}
?>
