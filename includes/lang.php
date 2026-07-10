<?php
// Frontend language helpers. Admin UI stays English; these helpers are for public-facing copy.

if (!defined('SUPPORTED_LANGUAGES')) {
    define('SUPPORTED_LANGUAGES', ['en', 'ms']);
}

function normalize_lang($lang)
{
    $lang = strtolower(trim((string) $lang));
    return in_array($lang, SUPPORTED_LANGUAGES, true) ? $lang : 'en';
}

function current_lang()
{
    static $currentLang = null;

    if ($currentLang !== null) {
        return $currentLang;
    }

    if (isset($_GET['lang'])) {
        $currentLang = normalize_lang($_GET['lang']);
        if (!headers_sent()) {
            setcookie('site_lang', $currentLang, time() + (86400 * 180), '/');
        }
        return $currentLang;
    }

    $currentLang = normalize_lang($_COOKIE['site_lang'] ?? 'en');
    return $currentLang;
}

function lang_file_strings($lang)
{
    static $strings = [];
    $lang = normalize_lang($lang);

    if (isset($strings[$lang])) {
        return $strings[$lang];
    }

    $path = __DIR__ . '/../lang/' . $lang . '.php';
    $strings[$lang] = file_exists($path) ? require $path : [];
    return $strings[$lang];
}

function translation_cache()
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $cache = [];
    if (!isset($GLOBALS['pdo']) || !($GLOBALS['pdo'] instanceof PDO)) {
        return $cache;
    }

    try {
        $stmt = $GLOBALS['pdo']->query("SELECT translation_key, text_en, text_ms FROM translations");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $cache[$row['translation_key']] = [
                'en' => $row['text_en'] ?? '',
                'ms' => $row['text_ms'] ?? '',
            ];
        }
    } catch (Throwable $e) {
        $cache = [];
    }

    return $cache;
}

function t($key, array $replace = [], $lang = null)
{
    $lang = normalize_lang($lang ?? current_lang());
    $cache = translation_cache();
    $fallbackEn = lang_file_strings('en');
    $fallbackSelected = lang_file_strings($lang);

    $text = '';
    if (isset($cache[$key])) {
        $text = trim((string) ($cache[$key][$lang] ?? ''));
        if ($text === '') {
            $text = trim((string) ($cache[$key]['en'] ?? ''));
        }
    }

    if ($text === '') {
        $text = (string) ($fallbackSelected[$key] ?? $fallbackEn[$key] ?? $key);
    }

    foreach ($replace as $placeholder => $value) {
        $text = str_replace(':' . $placeholder, (string) $value, $text);
    }

    return $text;
}

function e_t($key, array $replace = [], $lang = null)
{
    return htmlspecialchars(t($key, $replace, $lang), ENT_QUOTES, 'UTF-8');
}

function public_base_path()
{
    if (defined('SITE_URL')) {
        $basePath = parse_url(SITE_URL, PHP_URL_PATH);
        return rtrim((string) $basePath, '/');
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    return rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
}

function public_path($slug = '')
{
    $base = public_base_path();
    $slug = trim((string) $slug, '/');

    if ($slug === '') {
        return $base === '' ? '/' : $base . '/';
    }

    return ($base === '' ? '' : $base) . '/' . $slug;
}
function clean_public_url($url)
{
    if (preg_match('/^(https?:|mailto:|tel:|#)/i', $url)) {
        return $url;
    }

    $parts = parse_url($url);
    $path = $parts['path'] ?? '';
    $query = $parts['query'] ?? '';
    $params = [];

    if ($query !== '') {
        parse_str($query, $params);
    }

    $map = [
        'index.php' => '',
        'properties.php' => 'properties',
        'finance.php' => 'loans-financing',
        'calculators.php' => 'calculators',
        'blog.php' => 'blog',
        'contact.php' => 'contact',
        'about.php' => 'about',
        'tanah-lot-selupoh.php' => 'tanah-lot-selupoh',
        'briefing.php' => 'briefing',
        'developer-briefing.php' => 'developer-briefing',
        'briefing-success.php' => 'briefing-success',
        'privacy.php' => 'privacy',
        'disclaimer.php' => 'disclaimer',
    ];

    $baseName = basename($path);

    if ($baseName === 'post-view.php' && !empty($params['slug'])) {
        $slug = rawurlencode($params['slug']);
        unset($params['slug']);
        $cleanPath = public_path('blog/' . $slug);
    } elseif (isset($map[$baseName])) {
        $cleanPath = public_path($map[$baseName]);
    } else {
        return $url;
    }

    $queryString = $params ? http_build_query($params) : '';
    return $cleanPath . ($queryString ? '?' . $queryString : '');
}

function lang_url($url, $lang = null)
{
    $lang = normalize_lang($lang ?? current_lang());
    $url = clean_public_url($url);

    if ($lang === 'en' || preg_match('/^(https?:|mailto:|tel:|#)/i', $url)) {
        return $url;
    }

    $separator = strpos($url, '?') !== false ? '&' : '?';
    return $url . $separator . 'lang=' . rawurlencode($lang);
}

function switch_lang_url($lang)
{
    $lang = normalize_lang($lang);
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '';
    $query = parse_url($requestUri, PHP_URL_QUERY);
    $params = [];

    if ($query) {
        parse_str($query, $params);
    }

    $params['lang'] = $lang;
    $queryString = http_build_query($params);
    $cleanPath = clean_public_url($path);

    return $cleanPath . ($queryString ? '?' . $queryString : '');
}
?>
