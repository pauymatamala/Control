<?php
require_once 'C:/xampp/htdocs/Control/vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\NoSuchElementException;

class PrecioDecimalTest {
    private $driver;
    private $baseUrl = "http://localhost/Control";
    
    public function __construct() {
        echo "🔧 Configurando ChromeDriver...\n";
        
        // Iniciar ChromeDriver directamente
        $chromedriverPath = __DIR__ . '/chromedriver.exe';
        
        if (!file_exists($chromedriverPath)) {
            echo "❌ ChromeDriver no encontrado en: $chromedriverPath\n";
            echo "💡 Asegúrate de que chromedriver.exe esté en el directorio actual\n";
            exit(1);
        }
        
        // Iniciar ChromeDriver en segundo plano
        $cmd = "start /B \"\" \"$chromedriverPath\" --port=9515";
        pclose(popen($cmd, 'r'));
        
        // Esperar a que ChromeDriver se inicie
        echo "⏳ Esperando a que ChromeDriver se inicie...\n";
        sleep(3);
        
        // Configurar capacidades de Chrome
        $capabilities = DesiredCapabilities::chrome();
        $chromeOptions = new \Facebook\WebDriver\Chrome\ChromeOptions();
        $chromeOptions->addArguments([
            '--disable-web-security',
            '--disable-features=VizDisplayCompositor',
            '--no-sandbox',
            '--disable-dev-shm-usage'
        ]);
        $capabilities->setCapability(\Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY, $chromeOptions);
        
        try {
            $this->driver = RemoteWebDriver::create('http://localhost:9515', $capabilities);
            echo "✅ ChromeDriver iniciado correctamente\n";
        } catch (Exception $e) {
            echo "❌ Error al conectar con ChromeDriver: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function login() {
        echo "\n🔐 Iniciando sesión...\n";
        
        $this->driver->get($this->baseUrl . "/login.php");
        
        // Rellenar formulario de login (ajusta según tu sistema)
        $this->driver->findElement(WebDriverBy::name('usuario'))->sendKeys('admin');
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys('admin');
        $this->driver->findElement(WebDriverBy::xpath("//button[@type='submit']"))->click();
        
        // Esperar a que cargue la página principal
        $wait = new WebDriverWait($this->driver, 10);
        $wait->until(WebDriverExpectedCondition::titleContains('Sistema'));
        
        echo "✅ Sesión iniciada correctamente\n";
    }
    
    public function testPreciosDecimales() {
        echo "\n🧪 INICIANDO PRUEBAS DE PRECIOS DECIMALES\n";
        echo "=" . str_repeat("=", 50) . "\n";
        
        // Ir a la página de productos
        $this->driver->get($this->baseUrl . "/producto.php");
        
        // Esperar a que cargue la página
        sleep(2);
        
        // Hacer clic en "Agregar Producto"
        echo "📝 Abriendo formulario de nuevo producto...\n";
        $this->driver->findElement(WebDriverBy::xpath("//a[@href='#myModal']"))->click();
        
        // Esperar a que aparezca el modal
        sleep(1);
        
        // Casos de prueba para precios
        $casosPrueba = [
            // [valor, esperado_valido, descripcion]
            ['100', true, 'Precio entero válido'],
            ['99.50', true, 'Precio con 2 decimales válido'],
            ['15.9', true, 'Precio con 1 decimal válido'],
            ['99.999', false, 'Precio con 3 decimales (debe fallar)'],
            ['123456', false, 'Precio con más de 5 dígitos (debe fallar)'],
            ['abc', false, 'Texto no numérico (debe fallar)'],
            ['12.5a', false, 'Precio con caracteres inválidos (debe fallar)'],
            ['', false, 'Campo vacío (debe fallar)']
        ];
        
        foreach ($casosPrueba as $index => $caso) {
            $this->ejecutarCasoPrueba($caso[0], $caso[1], $caso[2], $index + 1);
        }
        
        echo "\n📊 RESUMEN DE PRUEBAS COMPLETADO\n";
    }
    
    private function ejecutarCasoPrueba($valor, $esperadoValido, $descripcion, $numeroCaso) {
        echo "\n--- Caso #{$numeroCaso}: {$descripcion} ---\n";
        echo "💰 Probando valor: '{$valor}'\n";
        
        try {
            // Rellenar campos obligatorios
            $this->driver->findElement(WebDriverBy::id('codigo'))->clear()->sendKeys('TEST' . $numeroCaso);
            $this->driver->findElement(WebDriverBy::id('nombre'))->clear()->sendKeys('Producto Test ' . $numeroCaso);
            $this->driver->findElement(WebDriverBy::id('stock'))->clear()->sendKeys('10');
            
            // Seleccionar categoría (primer option disponible)
            $selectCategoria = $this->driver->findElement(WebDriverBy::id('categoria'));
            $opciones = $selectCategoria->findElements(WebDriverBy::tagName('option'));
            if (count($opciones) > 1) {
                $opciones[1]->click(); // Seleccionar la primera categoría disponible
            }
            
            // Rellenar precio
            $campoPrecio = $this->driver->findElement(WebDriverBy::id('precio'));
            $campoPrecio->clear()->sendKeys($valor);
            
            // Verificar validación HTML5 del lado del cliente
            $esValidoHTML5 = $this->driver->executeScript("return document.getElementById('precio').checkValidity();");
            
            echo "🔍 Validación HTML5: " . ($esValidoHTML5 ? "✅ VÁLIDO" : "❌ INVÁLIDO") . "\n";
            
            if ($esperadoValido === $esValidoHTML5) {
                echo "✅ RESULTADO ESPERADO: La validación funcionó correctamente\n";
            } else {
                echo "❌ RESULTADO INESPERADO: Se esperaba " . ($esperadoValido ? "válido" : "inválido") . " pero fue " . ($esValidoHTML5 ? "válido" : "inválido") . "\n";
            }
            
            // Si es válido según HTML5, intentar enviar el formulario
            if ($esValidoHTML5) {
                echo "📤 Intentando enviar formulario...\n";
                
                $botonGuardar = $this->driver->findElement(WebDriverBy::xpath("//button[contains(text(), 'Guardar')]"));
                $botonGuardar->click();
                
                // Esperar respuesta del servidor
                sleep(2);
                
                // Verificar si aparece mensaje de éxito o error
                try {
                    $mensajeExito = $this->driver->findElement(WebDriverBy::className('alert-success'));
                    echo "✅ SERVIDOR: Producto guardado exitosamente\n";
                } catch (NoSuchElementException $e) {
                    try {
                        $mensajeError = $this->driver->findElement(WebDriverBy::className('alert-danger'));
                        echo "❌ SERVIDOR: Error al guardar - " . $mensajeError->getText() . "\n";
                    } catch (NoSuchElementException $e2) {
                        echo "⚠️  SERVIDOR: Sin respuesta clara del servidor\n";
                    }
                }
                
                // Cerrar modal para siguiente prueba
                sleep(1);
                try {
                    $this->driver->findElement(WebDriverBy::xpath("//button[@data-dismiss='modal']"))->click();
                } catch (Exception $e) {
                    // Si no hay botón de cerrar, recargar página
                    $this->driver->refresh();
                }
                sleep(1);
                
                // Reabrir modal para siguiente prueba
                $this->driver->findElement(WebDriverBy::xpath("//a[@href='#myModal']"))->click();
                sleep(1);
            }
            
        } catch (Exception $e) {
            echo "❌ ERROR durante la prueba: " . $e->getMessage() . "\n";
        }
        
        echo "🔚 Caso #{$numeroCaso} completado\n";
    }
    
    public function cerrar() {
        echo "\n🔧 Cerrando navegador...\n";
        if ($this->driver) {
            $this->driver->quit();
        }
        
        // Terminar proceso de ChromeDriver
        exec('taskkill /F /IM chromedriver.exe 2>nul', $output, $return);
        echo "✅ Pruebas completadas\n";
    }
}

// Ejecutar las pruebas
echo "🚀 INICIANDO PRUEBAS AUTOMATIZADAS DE PRECIOS DECIMALES\n";
echo "=" . str_repeat("=", 60) . "\n";

try {
    $test = new PrecioDecimalTest();
    $test->login();
    $test->testPreciosDecimales();
    $test->cerrar();
} catch (Exception $e) {
    echo "💥 ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "\n📋 INSTRUCCIONES PARA EJECUTAR:\n";
    echo "1. Descargar ChromeDriver desde: https://chromedriver.chromium.org/\n";
    echo "2. Ejecutar: java -jar selenium-server-standalone-X.X.X.jar\n";
    echo "3. Asegurarse de que XAMPP esté ejecutándose\n";
    echo "4. Verificar que la URL base sea correcta: http://localhost/Control\n";
}
?>
