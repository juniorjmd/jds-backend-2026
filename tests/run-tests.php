<?php
declare(strict_types=1);

// Simple test runner for Documentos module
echo "Running Documentos Module Tests...\n\n";

// Include required classes
require_once __DIR__ . '/../app/Core/Http/Request.php';
require_once __DIR__ . '/../app/Core/Http/Response.php';
require_once __DIR__ . '/../app/Modules/Auth/AuthContext.php';
require_once __DIR__ . '/../app/Modules/Documentos/Services/DocumentosService.php';
require_once __DIR__ . '/../app/Modules/Documentos/DocumentosController.php';
require_once __DIR__ . '/../app/Bootstrap/Routes.php';
require_once __DIR__ . '/../app/Core/Routing/Router.php';

// Simple assertion functions
function testAssert($condition, $message = '') {
    if (!$condition) {
        echo "FAIL: $message\n";
        return false;
    }
    return true;
}

function testAssertEquals($expected, $actual, $message = '') {
    if ($expected !== $actual) {
        echo "FAIL: $message (expected: $expected, actual: $actual)\n";
        return false;
    }
    return true;
}

function testAssertArrayHasKey($key, $array, $message = '') {
    if (!array_key_exists($key, $array)) {
        echo "FAIL: $message (key '$key' not found in array)\n";
        return false;
    }
    return true;
}

function testAssertIsArray($value, $message = '') {
    if (!is_array($value)) {
        echo "FAIL: $message (value is not an array)\n";
        return false;
    }
    return true;
}

function testAssertCount($expected, $array, $message = '') {
    if (count($array) !== $expected) {
        echo "FAIL: $message (expected count: $expected, actual: " . count($array) . ")\n";
        return false;
    }
    return true;
}

// Test counter
$testsRun = 0;
$testsPassed = 0;

function runTest($testName, callable $test) {
    global $testsRun, $testsPassed;
    $testsRun++;
    echo "Running: $testName\n";
    try {
        $result = $test();
        if ($result !== false) {
            $testsPassed++;
            echo "PASS\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test Routes::map() functionality
runTest('Routes::map() loads documentos actions', function() {
    $map = \App\Bootstrap\Routes::map();

    testAssert(testAssertIsArray($map), 'Routes map should be array');
    testAssert(testAssertArrayHasKey('LISTAR_DOCUMENTOS', $map), 'Should have LISTAR_DOCUMENTOS');
    testAssert(testAssertArrayHasKey('SUBIR_DOCUMENTO', $map), 'Should have SUBIR_DOCUMENTO');
    testAssert(testAssertArrayHasKey('DESCARGAR_DOCUMENTO', $map), 'Should have DESCARGAR_DOCUMENTO');
    testAssert(testAssertArrayHasKey('BORRAR_DOCUMENTO', $map), 'Should have BORRAR_DOCUMENTO');
    testAssert(testAssertArrayHasKey('PING', $map), 'Should have PING action');
});

// Test Router instantiation
runTest('Router can be instantiated with routes', function() {
    $routes = \App\Bootstrap\Routes::map();
    $router = new \App\Core\Routing\Router($routes);

    testAssert($router instanceof \App\Core\Routing\Router, 'Router should be instantiated');
});

// Integration test - test the full flow with HTTP globals
runTest('DocumentosController - LISTAR_DOCUMENTOS integration test', function() {
    // Set up HTTP globals for testing
    $_SERVER['REQUEST_URI'] = '/api/documentos/listDocuments';
    $_POST['_usuario_id'] = 123;
    $_POST['_tipo'] = 'factura';

    $request = \App\Core\Http\Request::fromGlobals();
    $routes = \App\Bootstrap\Routes::map();
    $router = new \App\Core\Routing\Router($routes);

    // Capture output and suppress headers
    ob_start();
    $result = @$router->dispatch($request); // Suppress warnings
    $output = ob_get_clean();

    // The router returns the result, but controllers echo JSON
    // So we should check the output
    $response = json_decode($output, true);

    testAssert(testAssertIsArray($response), 'Response should be array');
    testAssert($response['success'] === true, 'Response should be successful');
    testAssert(testAssertIsArray($response['data']), 'Data should be array');
    testAssert(testAssertCount(1, $response['data']), 'Should return one document');
    testAssert(testAssertEquals(1, $response['data'][0]['documento_id']), 'Document ID should be 1');
    testAssert(testAssertEquals('factura-001.pdf', $response['data'][0]['nombre']), 'Document name should match');
    testAssert(testAssertEquals('factura', $response['data'][0]['tipo']), 'Document type should match');
    testAssert(testAssertEquals(123, $response['data'][0]['usuario_id']), 'User ID should match');
});

// Clean up globals
unset($_SERVER['REQUEST_URI'], $_POST);

// Summary
echo "Tests completed: $testsPassed / $testsRun passed\n";
if ($testsPassed === $testsRun) {
    echo "All tests passed! ✅\n";
    exit(0);
} else {
    echo "Some tests failed! ❌\n";
    exit(1);
}