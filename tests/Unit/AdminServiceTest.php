<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;
use App\Modules\Admin\Services\AdminService;

class AdminServiceAuthContextStub
{
    public function resolve(Request $request): array
    {
        return [
            'success' => true,
            'compact_user' => ['id' => 1, 'nombre' => 'Admin User']
        ];
    }
}

class AdminServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== ADMIN SERVICE TEST ==========\n\n";

        $this->testGetUsersReturnsArray();
        $this->testCreateUserValidatesRequiredFields();
        $this->testCreateUserThrowsExceptionForMissingFields();
        $this->testUpdateUserValidatesUserId();
        $this->testUpdateUserThrowsExceptionForInvalidId();
        $this->testGetMenusReturnsArray();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function service(): AdminService
    {
        return new AdminService(new Request('POST', [], [], [], []), new AdminServiceAuthContextStub());
    }

    private function testGetUsersReturnsArray(): void
    {
        echo "TEST 1: getUsers returns array... ";

        try {
            $result = $this->service()->getUsers();
            if (!is_array($result) || !isset($result[0]['ID'])) {
                throw new \Exception('invalid response');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreateUserValidatesRequiredFields(): void
    {
        echo "TEST 2: createUser creates valid user... ";

        try {
            $result = $this->service()->createUser([
                'login' => 'testuser',
                'nombre1' => 'Test',
                'apellido1' => 'User',
                'mail' => 'test@example.com',
                'id_perfil' => 1
            ]);
            if (($result['Login'] ?? '') !== 'testuser') {
                throw new \Exception('user not created');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreateUserThrowsExceptionForMissingFields(): void
    {
        echo "TEST 3: createUser validates required fields... ";

        try {
            $this->service()->createUser(['login' => 'testuser']);
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'Campos requeridos faltantes')) {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testUpdateUserValidatesUserId(): void
    {
        echo "TEST 4: updateUser returns updated data... ";

        try {
            $result = $this->service()->updateUser(123, ['estado' => 'I']);
            if (($result['ID'] ?? 0) !== 123) {
                throw new \Exception('invalid updated id');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testUpdateUserThrowsExceptionForInvalidId(): void
    {
        echo "TEST 5: updateUser rejects invalid id... ";

        try {
            $this->service()->updateUser(0, ['estado' => 'A']);
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'ID de usuario inválido') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testGetMenusReturnsArray(): void
    {
        echo "TEST 6: getMenus returns array... ";

        try {
            $result = $this->service()->getMenus();
            if (!is_array($result) || !isset($result[0]['idmenus'])) {
                throw new \Exception('invalid menus response');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function printSummary(): void
    {
        $total = $this->passCount + $this->failCount;
        echo "\n========== RESULT ==========\n";
        echo "PASSED: {$this->passCount}/{$total}\n";
        echo "FAILED: {$this->failCount}/{$total}\n";
        echo "===========================\n\n";
    }
}

(new AdminServiceTest())->run();
