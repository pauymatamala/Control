<?php
// Autoloader para php-webdriver
spl_autoload_register(function ($class) {
    // Verificar si la clase está en el namespace de Facebook\WebDriver
    $prefix = 'Facebook\\WebDriver\\';
    $base_dir = __DIR__ . '/php-webdriver/lib/';
    
    // Verificar si la clase usa el namespace esperado
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Obtener el nombre relativo de la clase
    $relative_class = substr($class, $len);
    
    // Reemplazar namespace separators con directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Si el archivo existe, incluirlo
    if (file_exists($file)) {
        require $file;
    }
});
?>