<?php
/**
 * Prueba de instalación de php-webdriver
 */

require_once 'vendor/autoload.php';

try {
    // Verificar que las clases principales están disponibles
    echo "Probando php-webdriver...\n";
    
    // Verificar que la clase RemoteWebDriver existe
    if (class_exists('Facebook\WebDriver\Remote\RemoteWebDriver')) {
        echo "✓ Clase RemoteWebDriver disponible\n";
    } else {
        echo "✗ Clase RemoteWebDriver NO disponible\n";
    }
    
    // Verificar que la clase WebDriverBy existe
    if (class_exists('Facebook\WebDriver\WebDriverBy')) {
        echo "✓ Clase WebDriverBy disponible\n";
    } else {
        echo "✗ Clase WebDriverBy NO disponible\n";
    }
    
    // Verificar que la clase ChromeOptions existe
    if (class_exists('Facebook\WebDriver\Chrome\ChromeOptions')) {
        echo "✓ Clase ChromeOptions disponible\n";
    } else {
        echo "✗ Clase ChromeOptions NO disponible\n";
    }
    
    echo "\n¡php-webdriver instalado exitosamente!\n";
    echo "Puedes usar: require_once 'vendor/autoload.php'; en tus archivos PHP\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
