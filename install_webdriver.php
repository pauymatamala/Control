<?php
// Script para descargar php-webdriver/webdriver manualmente
echo "Descargando php-webdriver/webdriver desde GitHub...\n";

// Configurar contexto para deshabilitar SSL
$context = stream_context_create([
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: PHP\r\n"
    ]
]);

// Crear directorio vendor si no existe
if (!is_dir('vendor')) {
    mkdir('vendor', 0755, true);
}

if (!is_dir('vendor/php-webdriver')) {
    mkdir('vendor/php-webdriver', 0755, true);
}

// URL del archivo ZIP de la última versión
$zipUrl = "https://github.com/php-webdriver/php-webdriver/archive/refs/heads/main.zip";
$zipFile = "vendor/php-webdriver-main.zip";

echo "Descargando desde: $zipUrl\n";
$zipContent = file_get_contents($zipUrl, false, $context);

if ($zipContent !== false) {
    file_put_contents($zipFile, $zipContent);
    echo "Archivo descargado exitosamente.\n";
    
    // Extraer el ZIP
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo('vendor/');
        $zip->close();
        echo "Archivos extraídos.\n";
        
        // Mover archivos a la ubicación correcta
        if (is_dir('vendor/php-webdriver-main')) {
            if (is_dir('vendor/php-webdriver/webdriver')) {
                // Eliminar directorio existente
                function removeDirectory($dir) {
                    if (is_dir($dir)) {
                        $objects = scandir($dir);
                        foreach ($objects as $object) {
                            if ($object != "." && $object != "..") {
                                if (is_dir($dir."/".$object)) {
                                    removeDirectory($dir."/".$object);
                                } else {
                                    unlink($dir."/".$object);
                                }
                            }
                        }
                        rmdir($dir);
                    }
                }
                removeDirectory('vendor/php-webdriver/webdriver');
            }
            
            rename('vendor/php-webdriver-main', 'vendor/php-webdriver/webdriver');
            echo "Archivos movidos a vendor/php-webdriver/webdriver/\n";
        }
        
        // Limpiar archivo ZIP
        unlink($zipFile);
        
        // Crear autoloader básico
        $autoloadContent = '<?php
// Autoloader básico para php-webdriver
spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $prefix = "Facebook\\WebDriver\\";
    $base_dir = __DIR__ . "/php-webdriver/webdriver/lib/";
    
    // Verificar si la clase usa el namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Obtener el nombre relativo de la clase
    $relative_class = substr($class, $len);
    
    // Reemplazar namespace separators con directory separators
    $file = $base_dir . str_replace("\\\\", "/", $relative_class) . ".php";
    
    // Si el archivo existe, incluirlo
    if (file_exists($file)) {
        require $file;
    }
});
?>';
        
        file_put_contents('vendor/autoload.php', $autoloadContent);
        echo "Autoloader creado en vendor/autoload.php\n";
        echo "¡php-webdriver/webdriver instalado exitosamente!\n";
        echo "Puedes usar: require_once 'vendor/autoload.php'; en tu código.\n";
        
    } else {
        echo "Error al extraer el archivo ZIP.\n";
    }
} else {
    echo "Error al descargar el archivo.\n";
}
?>
