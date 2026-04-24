<?php
if (!defined('SITE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'taghoh.web-en-royans.fr';
    define('SITE_URL', $scheme . '://' . $host);
}
