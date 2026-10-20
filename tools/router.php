<?php
/**
 * Router for PHP's built-in server, so local development matches the .htaccess
 * rewrites on Hostinger.
 *
 *   php -S localhost:8080 -t . tools/router.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');   // "Care%20with%20Smile.jpeg" is a real file
$path = rtrim($path, '/');

if ($path === '' ) {
    require $root . '/index.php';
    return true;
}

// Serve real files (assets, manifest, robots.txt) straight from disk.
$file = $root . $path;
if (is_file($file)) {
    if (str_ends_with($file, '.php')) {
        require $file;
        return true;
    }
    return false; // let the built-in server handle static content
}

if ($path === '/sitemap.xml') {
    require $root . '/sitemap.php';
    return true;
}

if (preg_match('#^/services/([a-z0-9-]+)$#', $path, $m)) {
    $_GET['slug'] = $m[1];
    require $root . '/service.php';
    return true;
}

if (preg_match('#^/admin(/.*)?$#', $path)) {
    $target = $root . $path . '.php';
    if ($path === '/admin') {
        require $root . '/admin/index.php';
        return true;
    }
    if (is_file($target)) {
        require $target;
        return true;
    }
}

if (preg_match('#^/([a-z0-9-]+)$#', $path, $m) && is_file($root . '/' . $m[1] . '.php')) {
    require $root . '/' . $m[1] . '.php';
    return true;
}

http_response_code(404);
require $root . '/404.php';
return true;
