<?php
// admin/db_connect.php
// Database connection using environment configuration

// Load environment configuration
require_once __DIR__ . '/../php/env.php';

$host = DB_HOST;
$db = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;
$charset = DB_CHARSET;

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Connect to MySQL server first
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    // Dynamic database auto-installer to prevent HTTP 500 errors on host
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(255) DEFAULT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'editor') DEFAULT 'admin',
        google2fa_enabled TINYINT(1) DEFAULT 0,
        google2fa_secret VARCHAR(255) DEFAULT NULL,
        display_name VARCHAR(100) DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        avatar_url VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Fix existing users table - add missing columns if they don't exist
    try { $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) DEFAULT NULL AFTER username"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN google2fa_enabled TINYINT(1) DEFAULT 0 AFTER role"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN google2fa_secret VARCHAR(255) DEFAULT NULL AFTER google2fa_enabled"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN display_name VARCHAR(100) DEFAULT NULL AFTER google2fa_secret"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL AFTER display_name"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL AFTER bio"); } catch (PDOException $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_sections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        label VARCHAR(100) NOT NULL,
        url VARCHAR(255) DEFAULT '#',
        description TEXT,
        image_url VARCHAR(255),
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Drop old menu_items if it doesn't match the new structure
    try {
        $checkCol = $pdo->query("SHOW COLUMNS FROM menu_items LIKE 'section_id'");
        if ($checkCol->rowCount() === 0) {
            $pdo->exec("DROP TABLE IF EXISTS menu_items");
        }
    } catch (PDOException $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_id INT NOT NULL,
        label VARCHAR(100) NOT NULL,
        url VARCHAR(255) NOT NULL,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (section_id) REFERENCES menu_sections(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS executive_profile_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) DEFAULT NULL,
        title VARCHAR(100) DEFAULT NULL,
        portrait_url VARCHAR(255) DEFAULT NULL,
        expertise_tag_1 VARCHAR(50) DEFAULT NULL,
        expertise_tag_2 VARCHAR(50) DEFAULT NULL,
        expertise_tag_3 VARCHAR(50) DEFAULT NULL,
        years_experience VARCHAR(50) DEFAULT NULL,
        deals_closed VARCHAR(50) DEFAULT NULL,
        rating VARCHAR(50) DEFAULT NULL,
        primary_button_text VARCHAR(100) DEFAULT NULL,
        primary_button_link VARCHAR(255) DEFAULT NULL,
        secondary_button_text VARCHAR(100) DEFAULT NULL,
        secondary_button_link VARCHAR(255) DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Fix existing executive_profile_settings table - add missing columns if they don't exist
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN full_name VARCHAR(100) DEFAULT NULL AFTER id"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN title VARCHAR(100) DEFAULT NULL AFTER full_name"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN portrait_url VARCHAR(255) DEFAULT NULL AFTER title"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN expertise_tag_1 VARCHAR(50) DEFAULT NULL AFTER portrait_url"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN expertise_tag_2 VARCHAR(50) DEFAULT NULL AFTER expertise_tag_1"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN expertise_tag_3 VARCHAR(50) DEFAULT NULL AFTER expertise_tag_2"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN years_experience VARCHAR(50) DEFAULT NULL AFTER expertise_tag_3"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN deals_closed VARCHAR(50) DEFAULT NULL AFTER years_experience"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN rating VARCHAR(50) DEFAULT NULL AFTER deals_closed"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN primary_button_text VARCHAR(100) DEFAULT NULL AFTER rating"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN primary_button_link VARCHAR(255) DEFAULT NULL AFTER primary_button_text"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN secondary_button_text VARCHAR(100) DEFAULT NULL AFTER primary_button_link"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN secondary_button_link VARCHAR(255) DEFAULT NULL AFTER secondary_button_text"); } catch (PDOException $e) {}

    // Alter column types if they already exist as strict INT/DECIMAL
    try { $pdo->exec("ALTER TABLE executive_profile_settings MODIFY COLUMN years_experience VARCHAR(50) DEFAULT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings MODIFY COLUMN deals_closed VARCHAR(50) DEFAULT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings MODIFY COLUMN rating VARCHAR(50) DEFAULT NULL"); } catch (PDOException $e) {}

    // Digital Business Card - new contact fields
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN phone_primary VARCHAR(30) DEFAULT NULL AFTER secondary_button_link"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN phone_secondary VARCHAR(30) DEFAULT NULL AFTER phone_primary"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN whatsapp_number VARCHAR(30) DEFAULT NULL AFTER phone_secondary"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN email_address VARCHAR(255) DEFAULT NULL AFTER whatsapp_number"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN company_name VARCHAR(150) DEFAULT NULL AFTER email_address"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN address_line TEXT DEFAULT NULL AFTER company_name"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN website_url VARCHAR(255) DEFAULT NULL AFTER address_line"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN linkedin_url VARCHAR(255) DEFAULT NULL AFTER website_url"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN instagram_url VARCHAR(255) DEFAULT NULL AFTER linkedin_url"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN facebook_url VARCHAR(255) DEFAULT NULL AFTER instagram_url"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN card_bio TEXT DEFAULT NULL AFTER facebook_url"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE executive_profile_settings ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER card_bio"); } catch (PDOException $e) {}


    $pdo->exec("CREATE TABLE IF NOT EXISTS about_page (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_title VARCHAR(255) NOT NULL DEFAULT 'About Shawn Radam',
        page_subtitle VARCHAR(255) DEFAULT 'Professional Advisory Services',
        hero_label VARCHAR(100) DEFAULT 'Personal Advisor',
        profile_name VARCHAR(150) DEFAULT 'Shawn Radam',
        profile_title VARCHAR(150) DEFAULT 'Personal Advisor',
        portrait_url VARCHAR(255) DEFAULT NULL,
        intro_text TEXT,
        cta_heading VARCHAR(255) DEFAULT 'Ready to discuss your goals?',
        cta_button_text VARCHAR(100) DEFAULT 'Get in Touch',
        cta_button_link VARCHAR(255) DEFAULT 'contact.php',
        seo_title VARCHAR(255) DEFAULT 'About | Shawn Radam',
        seo_desc VARCHAR(255) DEFAULT 'Learn more about Shawn Radam and his professional advisory background.',
        is_published TINYINT(1) DEFAULT 1,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS about_expertise_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        description TEXT,
        icon_key VARCHAR(50) DEFAULT 'briefcase',
        accent_color VARCHAR(30) DEFAULT 'blue',
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS landing_pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(150) NOT NULL UNIQUE,
        page_title VARCHAR(255) NOT NULL,
        eyebrow VARCHAR(120) DEFAULT NULL,
        hero_title VARCHAR(255) NOT NULL,
        hero_subtitle TEXT,
        location VARCHAR(180) DEFAULT NULL,
        lot_size VARCHAR(80) DEFAULT NULL,
        price VARCHAR(80) DEFAULT NULL,
        availability VARCHAR(160) DEFAULT NULL,
        image_url VARCHAR(255) DEFAULT NULL,
        intro_text TEXT,
        highlights TEXT,
        facilities TEXT,
        map_notes TEXT,
        cta_heading VARCHAR(255) DEFAULT NULL,
        cta_text VARCHAR(120) DEFAULT NULL,
        cta_link VARCHAR(255) DEFAULT NULL,
        seo_title VARCHAR(255) DEFAULT NULL,
        seo_desc VARCHAR(255) DEFAULT NULL,
        is_published TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    try { $pdo->exec("ALTER TABLE landing_pages ADD COLUMN eyebrow VARCHAR(120) DEFAULT NULL AFTER page_title"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE landing_pages ADD COLUMN map_notes TEXT DEFAULT NULL AFTER facilities"); } catch (PDOException $e) {}

    $landingCount = (int) $pdo->query("SELECT COUNT(*) FROM landing_pages WHERE slug = 'tanah-lot-selupoh'")->fetchColumn();
    if ($landingCount === 0) {
        $stmt = $pdo->prepare("INSERT INTO landing_pages (slug, page_title, eyebrow, hero_title, hero_subtitle, location, lot_size, price, availability, image_url, intro_text, highlights, facilities, map_notes, cta_heading, cta_text, cta_link, seo_title, seo_desc, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'tanah-lot-selupoh',
            'Tanah Lot Selupoh Tuaran',
            'Tawaran Istimewa',
            'Tanah Lot Luas 6000 sqft di Kg. Selupoh, Tuaran',
            'Lot strategik berhampiran Sungai Tuaran, sesuai untuk bina rumah idaman, rumah rehat atau homestay.',
            'Kg. Selupoh, Tuaran',
            '6000 sqft',
            'RM89,900',
            'Hanya tinggal 2 lot sahaja',
            'assets/landing/tanah-lot-selupoh-ads-whatsapp.jpg',
            'Bayangkan memiliki tanah seluas 6000 sqft berhampiran Sungai Tuaran. Kawasan bebas banjir, akses mudah, dan fasiliti harian berada dekat tanpa perlu pening.',
            "Kawasan bebas banjir\nAkses mudah\nSesuai untuk bina rumah atau homestay\nPemandangan cantik berhampiran Sungai Tuaran\nPotensi pelaburan masa hadapan",
            "CKS Supermarket Tuaran\nSolaris Commercial Centre\nLinangkit Culture Village\nCR Badminton Hall\nServay Express Tuaran\nTuaran Market",
            'Lokasi berdekatan Jalan Topokon Labuaya, Kg. Selupoh, Sungai Tuaran dan kemudahan utama sekitar Tuaran.',
            'Jangan tunggu lagi, lot terhad',
            'Hubungi Sekarang',
            'contact.php',
            'Tanah Lot Selupoh Tuaran | 6000 sqft',
            'Tanah lot 6000 sqft di Kg. Selupoh, Tuaran dengan harga RM89,900 dan lokasi strategik berhampiran fasiliti utama.',
            1
        ]);
    }
    try {
        $pdo->exec("UPDATE landing_pages SET image_url = 'assets/landing/tanah-lot-selupoh-ads-whatsapp.jpg' WHERE slug = 'tanah-lot-selupoh' AND (image_url IS NULL OR image_url = '' OR image_url = 'assets/landing/tanah-lot-selupoh-ads.jpg')");
    } catch (PDOException $e) {}
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        username VARCHAR(100) DEFAULT NULL,
        success TINYINT(1) DEFAULT 0,
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS verification_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        code VARCHAR(6) NOT NULL,
        purpose VARCHAR(50) DEFAULT 'login',
        verified TINYINT(1) DEFAULT 0,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_token VARCHAR(64) NOT NULL UNIQUE,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent TEXT DEFAULT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS page_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visitor_hash VARCHAR(64) NOT NULL,
        page_url VARCHAR(500) NOT NULL,
        country VARCHAR(100) DEFAULT 'Unknown',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS feedback_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        source VARCHAR(60) DEFAULT 'website',
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    try { $pdo->exec("ALTER TABLE newsletter_subscribers ADD COLUMN source VARCHAR(60) DEFAULT 'website' AFTER email"); } catch (PDOException $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS briefing_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        pillar VARCHAR(80) NOT NULL,
        details TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content MEDIUMTEXT,
        template VARCHAR(50) DEFAULT 'default',
        status ENUM('draft', 'published') DEFAULT 'draft',
        seo_title VARCHAR(255),
        seo_desc VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        summary TEXT,
        content MEDIUMTEXT,
        image_url VARCHAR(255),
        author_id INT,
        category_id INT,
        views INT DEFAULT 0,
        status ENUM('draft', 'published') DEFAULT 'draft',
        is_nofollow TINYINT(1) DEFAULT 0,
        seo_title VARCHAR(255),
        seo_desc VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS post_tags (
        post_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (post_id, tag_id),
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS koperasi_presets (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        interest_rate DECIMAL(5,2) NOT NULL,
        min_loan DECIMAL(12,2) NOT NULL,
        max_loan DECIMAL(12,2) NOT NULL,
        min_tenure INT NOT NULL,
        max_tenure INT NOT NULL,
        processing_fee_percent DECIMAL(5,2) NOT NULL,
        insurance_percent DECIMAL(5,2) NOT NULL,
        membership_fee DECIMAL(10,2) NOT NULL,
        advance_payment_months INT NOT NULL,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS property_calculator_presets (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        category ENUM('stamp_duty','mortgage','rental_yield','land_premium') NOT NULL,
        region ENUM('kl','sabah') NOT NULL,
        interest_rate_default DECIMAL(5,2) DEFAULT 4.50,
        min_price DECIMAL(15,2) DEFAULT 0.00,
        max_price DECIMAL(15,2) DEFAULT 0.00,
        min_tenure INT DEFAULT 5,
        max_tenure INT DEFAULT 35,
        down_payment_pct DECIMAL(5,2) DEFAULT 10.00,
        premium_rate DECIMAL(5,2) DEFAULT 0.00,
        notes TEXT,
        is_active TINYINT(1) DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS digital_business_cards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        card_slug VARCHAR(100) NOT NULL UNIQUE,
        full_name VARCHAR(150) NOT NULL,
        title VARCHAR(150) DEFAULT NULL,
        company_name VARCHAR(150) DEFAULT NULL,
        portrait_url VARCHAR(255) DEFAULT NULL,
        card_bio TEXT DEFAULT NULL,
        phone_primary VARCHAR(30) DEFAULT NULL,
        phone_secondary VARCHAR(30) DEFAULT NULL,
        whatsapp_number VARCHAR(50) DEFAULT NULL,
        email_address VARCHAR(255) DEFAULT NULL,
        address_line TEXT DEFAULT NULL,
        website_url VARCHAR(255) DEFAULT NULL,
        linkedin_url VARCHAR(255) DEFAULT NULL,
        instagram_url VARCHAR(255) DEFAULT NULL,
        facebook_url VARCHAR(255) DEFAULT NULL,
        expertise_tag_1 VARCHAR(80) DEFAULT NULL,
        expertise_tag_2 VARCHAR(80) DEFAULT NULL,
        expertise_tag_3 VARCHAR(80) DEFAULT NULL,
        years_experience VARCHAR(20) DEFAULT NULL,
        deals_closed VARCHAR(20) DEFAULT NULL,
        rating VARCHAR(10) DEFAULT NULL,
        primary_button_text VARCHAR(100) DEFAULT NULL,
        primary_button_link VARCHAR(255) DEFAULT NULL,
        secondary_button_text VARCHAR(100) DEFAULT NULL,
        secondary_button_link VARCHAR(255) DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        is_default TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS translations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        translation_key VARCHAR(190) NOT NULL UNIQUE,
        section VARCHAR(100) DEFAULT NULL,
        text_en TEXT,
        text_ms TEXT,
        notes TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_translations_section (section)
    )");

    // Fix existing translations table - add missing columns if they do not exist.
    try { $pdo->exec("ALTER TABLE translations ADD COLUMN section VARCHAR(100) DEFAULT NULL AFTER translation_key"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE translations ADD COLUMN notes TEXT DEFAULT NULL AFTER text_ms"); } catch (PDOException $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS header_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT,
        image_url VARCHAR(255),
        link_url VARCHAR(255),
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        image_url VARCHAR(255),
        link_url VARCHAR(255),
        description TEXT,
        position VARCHAR(50),
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Fix existing header_notifications table - add missing columns if they don't exist
    try { $pdo->exec("ALTER TABLE header_notifications ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER message"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE header_notifications ADD COLUMN link_url VARCHAR(255) DEFAULT NULL AFTER image_url"); } catch (PDOException $e) {}

    // Fix existing ads table - add missing columns if they don't exist
    try { $pdo->exec("ALTER TABLE ads ADD COLUMN position VARCHAR(50) DEFAULT NULL AFTER description"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE ads ADD COLUMN sort_order INT DEFAULT 0 AFTER is_active"); } catch (PDOException $e) {}

    // Seed default site settings if none exist
    $checkSite = $pdo->query("SELECT COUNT(*) FROM site_settings");
    if ($checkSite->fetchColumn() == 0) {
        $siteDefaults = [
            ['maintenance_mode', '0'],
            ['maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.'],
            ['maintenance_end_time', ''],
            ['security_passcode', '123456']
        ];
        $insertSite = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($siteDefaults as $setting) {
            $insertSite->execute($setting);
        }
    }

    // Ensure show_floating_calculator setting exists
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('show_floating_calculator', '1')");
        $stmt->execute();
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('floating_calculator_text', 'Personal Loan Calculator')");
        $stmt->execute();
    } catch (Exception $e) {}


    // Seed default About page content if none exists
    $checkAbout = $pdo->query("SELECT COUNT(*) FROM about_page");
    if ($checkAbout->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO about_page (page_title, page_subtitle, hero_label, profile_name, profile_title, intro_text, cta_heading, cta_button_text, cta_button_link, seo_title, seo_desc, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'About Shawn Radam',
            'Professional Advisory Services',
            'Personal Advisor',
            'Shawn Radam',
            'Personal Advisor',
            'Experienced professional providing expert advisory services in real estate, finance, and business development. Committed to helping clients achieve their goals through strategic guidance and personalized solutions.',
            'Ready to discuss your goals?',
            'Get in Touch',
            'contact.php',
            'About | Shawn Radam',
            'Learn more about Shawn Radam and his professional advisory background.',
            1
        ]);
    }

    $checkAboutItems = $pdo->query("SELECT COUNT(*) FROM about_expertise_items");
    if ($checkAboutItems->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO about_expertise_items (title, description, icon_key, accent_color, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $items = [
            ['Real Estate', 'Property investment and development consulting', 'home', 'blue', 1, 1],
            ['Finance', 'Financial planning and investment strategies', 'currency', 'green', 2, 1],
            ['Business', 'Strategic business development and consulting', 'briefcase', 'purple', 3, 1]
        ];
        foreach ($items as $item) {
            $stmt->execute($item);
        }
    }
    // Seed default admin user if none exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    if ($stmt->fetchColumn() == 0) {
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (username, password_hash, email, role) VALUES ('admin', '$hashed', 'admin@shawnradam.com', 'admin')");
    }

    // Seed default settings if none exist
    $defaults = [
        'site_footer' => '&copy; 2026 Shawn Radam. All rights reserved.',
        'blog_sidebar' => '<h3>About Me</h3><p>Private Advisor...</p>',
        'ad_code_header' => '<!-- Ad Code Here -->',
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaults as $key => $val) {
        $stmt->execute([$key, $val]);
    }

    // Seed default menu sections if none exist
    $checkMenu = $pdo->query("SELECT COUNT(*) FROM menu_sections");
    if ($checkMenu->fetchColumn() == 0) {
        $sections = [
            [1, 'Home', 'index.php', 'Welcome to my advisory services', 1],
            [2, 'Properties', 'properties.php', 'Sabah land assets and valuation', 2],
            [3, 'Loans Financing', 'finance.php', 'Professional lending and eligibility check', 3],
            [4, 'Blog', 'blog.php', 'Latest insights and articles', 4],
            [5, 'Contact', 'contact.php', 'Get in touch with me', 5]
        ];
        $insertStmt = $pdo->prepare("INSERT INTO menu_sections (id, label, url, description, display_order) VALUES (?, ?, ?, ?, ?)");
        foreach ($sections as $section) {
            $insertStmt->execute($section);
        }
    }


    // Ensure About menu section exists
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_sections WHERE LOWER(label) = 'about' OR url IN ('about.php', 'about')");
        $stmt->execute();
        if ((int) $stmt->fetchColumn() === 0) {
            $maxOrder = (int) $pdo->query("SELECT COALESCE(MAX(display_order), 0) FROM menu_sections")->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO menu_sections (label, url, description, display_order, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute(['About', 'about.php', 'Learn more about Shawn Radam', $maxOrder + 1]);
        }
    } catch (Exception $e) {}
    // Seed default koperasi presets if none exist
    $checkKop = $pdo->query("SELECT COUNT(*) FROM koperasi_presets");
    if ($checkKop->fetchColumn() == 0) {
        $kopPresets = [
            ['coopbank_pertama', 'Co-opbank Pertama', 3.85, 1000, 200000, 1, 10, 4.5, 2.5, 50, 2],
            ['koperasi_tentera', 'Koperasi Tentera', 4.25, 2000, 150000, 1, 10, 3.0, 2.0, 30, 1],
            ['yir', 'Yayasan Ihsan Rakyat', 6.50, 1000, 150000, 1, 10, 5.0, 3.5, 0, 2],
            ['custom', 'Custom Koperasi', 4.50, 1000, 250000, 1, 10, 5.0, 3.0, 50, 2]
        ];
        $insertKop = $pdo->prepare("INSERT INTO koperasi_presets (id, name, interest_rate, min_loan, max_loan, min_tenure, max_tenure, processing_fee_percent, insurance_percent, membership_fee, advance_payment_months) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($kopPresets as $preset) {
            $insertKop->execute($preset);
        }
    }

    // Seed default property presets if none exist
    $checkProp = $pdo->query("SELECT COUNT(*) FROM property_calculator_presets");
    if ($checkProp->fetchColumn() == 0) {
        $propPresets = [
            ['kl_stamp_duty', 'KL Stamp Duty & Legal Fees Default', 'stamp_duty', 'kl', 4.50, 0.00, 0.00, 5, 35, 10.00, 0.00, 'Standard Semenanjung MOT & Loan Stamp Duty rates'],
            ['kl_mortgage', 'KL Mortgage Loan Default', 'mortgage', 'kl', 4.50, 10000.00, 2000000.00, 5, 35, 10.00, 0.00, 'Standard mortgage financing terms'],
            ['kl_rental_yield', 'KL Rental Yield Default', 'rental_yield', 'kl', 0.00, 0.00, 0.00, 0, 0, 0.00, 0.00, 'Standard maintenance rate at 1.00% per year'],
            ['sabah_stamp_duty', 'Sabah Stamp Duty Default', 'stamp_duty', 'sabah', 4.50, 0.00, 0.00, 5, 35, 10.00, 0.00, 'Sabah MOT and Loan Stamp Duty'],
            ['sabah_land_premium', 'Sabah Land Premium Conversion Default', 'land_premium', 'sabah', 0.00, 0.00, 0.00, 0, 0, 0.00, 20.00, 'General conversion rate estimates for JTU Sabah'],
            ['sabah_mortgage', 'Sabah Mortgage Loan Default', 'mortgage', 'sabah', 4.50, 10000.00, 2000000.00, 5, 35, 10.00, 0.00, 'Sabah property mortgage rates']
        ];
        $insertProp = $pdo->prepare("INSERT INTO property_calculator_presets (id, name, category, region, interest_rate_default, min_price, max_price, min_tenure, max_tenure, down_payment_pct, premium_rate, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($propPresets as $preset) {
            $insertProp->execute($preset);
        }
    }

    // Seed default digital business card if none exists
    $checkCard = $pdo->query("SELECT COUNT(*) FROM digital_business_cards");
    if ($checkCard->fetchColumn() == 0) {
        $profileData = null;
        try {
            $profileData = $pdo->query("SELECT * FROM executive_profile_settings LIMIT 1")->fetch();
        } catch (Exception $e) {}

        if ($profileData) {
            $insertCard = $pdo->prepare("INSERT INTO digital_business_cards (card_slug, full_name, title, company_name, portrait_url, card_bio, phone_primary, phone_secondary, whatsapp_number, email_address, address_line, website_url, linkedin_url, instagram_url, facebook_url, expertise_tag_1, expertise_tag_2, expertise_tag_3, years_experience, deals_closed, rating, primary_button_text, primary_button_link, secondary_button_text, secondary_button_link, is_active, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insertCard->execute([
                'shawn_radam',
                $profileData['full_name'] ?? 'Shawn Radam',
                $profileData['title'] ?? 'Private Advisory',
                $profileData['company_name'] ?? 'Shawn Radam Advisory',
                $profileData['portrait_url'] ?? '',
                $profileData['card_bio'] ?? 'Personal Advisor specialising in Asset Acquisition, Structured Lending & Travel Logistics.',
                $profileData['phone_primary'] ?? '012 8338 639',
                $profileData['phone_secondary'] ?? '011 1633 9399',
                $profileData['whatsapp_number'] ?? '601283386392',
                $profileData['email_address'] ?? 'admin@shawnradam.com',
                $profileData['address_line'] ?? 'Kota Kinabalu, Sabah, Malaysia',
                $profileData['website_url'] ?? 'https://shawnradam.com',
                $profileData['linkedin_url'] ?? '',
                $profileData['instagram_url'] ?? '',
                $profileData['facebook_url'] ?? '',
                $profileData['expertise_tag_1'] ?? 'Asset Acquisition',
                $profileData['expertise_tag_2'] ?? 'Structured Lending',
                $profileData['expertise_tag_3'] ?? 'Travel Logistics',
                $profileData['years_experience'] ?? '12+',
                $profileData['deals_closed'] ?? '250+',
                $profileData['rating'] ?? '5.0',
                $profileData['primary_button_text'] ?? 'Request Briefing',
                $profileData['primary_button_link'] ?? 'developer-briefing.php',
                $profileData['secondary_button_text'] ?? 'Get in Touch',
                $profileData['secondary_button_link'] ?? 'contact.php',
                1,
                1
            ]);
        } else {
            $insertCard = $pdo->prepare("INSERT INTO digital_business_cards (card_slug, full_name, title, company_name, card_bio, phone_primary, phone_secondary, whatsapp_number, email_address, address_line, website_url, expertise_tag_1, expertise_tag_2, expertise_tag_3, years_experience, deals_closed, rating, primary_button_text, primary_button_link, secondary_button_text, secondary_button_link, is_active, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insertCard->execute([
                'shawn_radam',
                'Shawn Radam',
                'Private Advisory',
                'Shawn Radam Advisory',
                'Personal Advisor specialising in Asset Acquisition, Structured Lending & Travel Logistics.',
                '012 8338 639',
                '011 1633 9399',
                '601283386392',
                'admin@shawnradam.com',
                'Kota Kinabalu, Sabah, Malaysia',
                'https://shawnradam.com',
                'Asset Acquisition',
                'Structured Lending',
                'Travel Logistics',
                '12+',
                '250+',
                '5.0',
                'Request Briefing',
                'developer-briefing.php',
                'Get in Touch',
                'contact.php',
                1,
                1
            ]);
        }
    }

} catch (\PDOException $e) {
    if (ENVIRONMENT === 'development') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please contact support.");
    }
}
?>
