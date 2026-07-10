<?php
// admin/setup.php
require_once 'db_connect.php';

try {
    // 1. Users Table (Admins)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(255) DEFAULT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'editor') DEFAULT 'admin',
        google2fa_enabled TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Settings Table (Footer, Ads, Sidebar, etc.)
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // 3. Menus System
    $pdo->exec("CREATE TABLE IF NOT EXISTS menus (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        position VARCHAR(50) UNIQUE, -- e.g., 'header', 'footer'
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_id INT NOT NULL,
        label VARCHAR(100) NOT NULL,
        link VARCHAR(255) NOT NULL,
        parent_id INT DEFAULT 0,
        sort_order INT DEFAULT 0,
        FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
    )");

    // 4. Notifications
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message VARCHAR(255) NOT NULL,
        type VARCHAR(50) DEFAULT 'info', -- info, success, warning
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 5. Content: Categories & Tags
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

    // 6. Content: Posts (Blog)
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        summary TEXT, -- Short info (~30 words)
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

    // Fix existing users table - add missing columns if they don't exist
    try { $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) DEFAULT NULL AFTER username"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN google2fa_enabled TINYINT(1) DEFAULT 0 AFTER role"); } catch (PDOException $e) {}

    // 8. Login Attempts (Rate Limiting)
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        username VARCHAR(100) DEFAULT NULL,
        success TINYINT(1) DEFAULT 0,
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_time (ip_address, attempt_time)
    )");

    // 9. Verification Codes (2FA)
    $pdo->exec("CREATE TABLE IF NOT EXISTS verification_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        code VARCHAR(6) NOT NULL,
        purpose VARCHAR(50) DEFAULT 'login',
        verified TINYINT(1) DEFAULT 0,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_code (user_id, code),
        INDEX idx_expires (expires_at)
    )");

    // 10. User Sessions
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_token VARCHAR(64) NOT NULL UNIQUE,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent TEXT DEFAULT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_token (session_token),
        INDEX idx_expires (expires_at)
    )");

    // 7. Page Views (Analytics)
    $pdo->exec("CREATE TABLE IF NOT EXISTS page_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visitor_hash VARCHAR(64) NOT NULL,
        page_url VARCHAR(500) NOT NULL,
        country VARCHAR(100) DEFAULT 'Unknown',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_visitor (visitor_hash),
        INDEX idx_date (created_at),
        INDEX idx_page (page_url(255))
    )");

    // Feedback Submissions
    $pdo->exec("CREATE TABLE IF NOT EXISTS feedback_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 8. Content: Pages (Static)
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

    // Create Default Admin User if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    if ($stmt->fetchColumn() == 0) {
        $pass = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin', $pass]);
        echo "Default admin user created (User: admin, Pass: password)<br>";
    }

    // Insert Default Settings
    $defaults = [
        'site_footer' => '&copy; 2026 Shawn Radam. All rights reserved.',
        'blog_sidebar' => '<h3>About Me</h3><p>Private Advisor...</p>',
        'ad_code_header' => '<!-- Ad Code Here -->',
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaults as $key => $val) {
        $stmt->execute([$key, $val]);
    }

    echo "Database installation complete successfully.";

} catch (PDOException $e) {
    echo "Setup failed: " . $e->getMessage();
}
?>