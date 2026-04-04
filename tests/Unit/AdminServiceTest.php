<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Modules\Admin\Services\AdminService;
use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;

class AdminServiceTest extends TestCase
{
    private AdminService $service;
    private Request $request;
    private AuthContext $authContext;

    protected function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->authContext = $this->createMock(AuthContext::class);

        // Mock successful authentication
        $this->authContext->method('resolve')
            ->willReturn([
                'success' => true,
                'compact_user' => ['id' => 1, 'nombre' => 'Admin User']
            ]);

        $this->service = new AdminService($this->request, $this->authContext);
    }

    public function testGetUsersReturnsArray()
    {
        $result = $this->service->getUsers();

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
        $this->assertArrayHasKey('ID', $result[0]);
        $this->assertArrayHasKey('Login', $result[0]);
    }

    public function testCreateUserValidatesRequiredFields()
    {
        $userData = [
            'login' => 'testuser',
            'nombre1' => 'Test',
            'apellido1' => 'User',
            'mail' => 'test@example.com'
        ];

        $result = $this->service->createUser($userData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('ID', $result);
        $this->assertEquals('testuser', $result['Login']);
    }

    public function testCreateUserThrowsExceptionForMissingFields()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Campos requeridos faltantes');

        $userData = ['login' => 'testuser']; // Missing required fields

        $this->service->createUser($userData);
    }

    public function testUpdateUserValidatesUserId()
    {
        $result = $this->service->updateUser(123, ['estado' => 'I']);

        $this->assertIsArray($result);
        $this->assertEquals(123, $result['ID']);
        $this->assertEquals('I', $result['estado']);
    }

    public function testUpdateUserThrowsExceptionForInvalidId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ID de usuario inválido');

        $this->service->updateUser(0, ['estado' => 'A']);
    }

    public function testGetMenusReturnsArray()
    {
        $result = $this->service->getMenus();

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
        $this->assertArrayHasKey('idmenus', $result[0]);
        $this->assertArrayHasKey('Nombre', $result[0]);
    }
}