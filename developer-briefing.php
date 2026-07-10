<?php
// developer-briefing.php - Redirects to the public briefing page.
require_once 'includes/lang.php';
header('Location: ' . lang_url('briefing.php'));
exit;