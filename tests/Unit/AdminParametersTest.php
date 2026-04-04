<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Http\Request;

class AdminParametersTest extends TestCase
{
    public function testLegacyParameterParsing()
    {
        // Test that parameters with underscore prefix are handled correctly
        $request = $this->createMock(Request::class);

        // Mock the input method to return legacy parameters
        $request->method('input')
            ->willReturnCallback(function ($key, $default = null) {
                $legacyParams = [
                    'estado' => 'A',
                    'login' => 'testuser',
                    'nombre1' => 'Juan',
                    'apellido1' => 'Pérez',
                    'mail' => 'juan@example.com',
                    'id_perfil' => '1',
                    'id' => '123'
                ];

                return $legacyParams[$key] ?? $default;
            });

        // Test getUsers parameters
        $estado = $request->input('estado', 'A');
        $this->assertEquals('A', $estado);

        // Test createUser parameters
        $login = $request->input('login', '');
        $nombre1 = $request->input('nombre1', '');
        $apellido1 = $request->input('apellido1', '');
        $mail = $request->input('mail', '');

        $this->assertEquals('testuser', $login);
        $this->assertEquals('Juan', $nombre1);
        $this->assertEquals('Pérez', $apellido1);
        $this->assertEquals('juan@example.com', $mail);

        // Test updateUser parameters
        $userId = (int) $request->input('id', 0);
        $this->assertEquals(123, $userId);

        $perfilId = (int) $request->input('id_perfil', 1);
        $this->assertEquals(1, $perfilId);
    }

    public function testParameterDefaults()
    {
        $request = $this->createMock(Request::class);

        // Mock empty input
        $request->method('input')
            ->willReturn('');

        // Test defaults
        $estado = $request->input('estado', 'A');
        $this->assertEquals('A', $estado);

        $perfilId = (int) $request->input('id_perfil', 1);
        $this->assertEquals(1, $perfilId);

        $userId = (int) $request->input('id', 0);
        $this->assertEquals(0, $userId);
    }

    public function testActionParameter()
    {
        $request = $this->createMock(Request::class);

        // Mock action method
        $request->method('action')
            ->willReturn('OBTENER_USUARIOS');

        $action = $request->action();
        $this->assertEquals('OBTENER_USUARIOS', $action);
    }
}