<?php
spl_autoload_register(function ($class) {
    if (strpos($class, 'Facebook\\WebDriver\\') === 0) {
        $path = __DIR__ . '/vendor/php-webdriver/lib/' . str_replace('\\', '/', substr($class, 18)) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});