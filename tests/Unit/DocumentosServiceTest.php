<?php
declare(strict_types=1);

use App\Modules\Documentos\Services\DocumentosService;
use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;
use PHPUnit\Framework\TestCase;

class DocumentosServiceTest extends TestCase
{
    private DocumentosService $service;
    private Request $request;
    private AuthContext $authContext;

    protected function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->authContext = $this->createMock(AuthContext::class);
        $this->service = new DocumentosService($this->request, $this->authContext);
    }

    public function testListDocumentsWithValidAuth(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => true, 'user_id' => 123]);

        $result = $this->service->listDocuments(123, 'factura');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['documento_id']);
        $this->assertEquals('factura-001.pdf', $result[0]['nombre']);
        $this->assertEquals('factura', $result[0]['tipo']);
        $this->assertEquals(123, $result[0]['usuario_id']);
        $this->assertArrayHasKey('fecha_creacion', $result[0]);
    }

    public function testListDocumentsWithInvalidAuth(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => false]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usuario no autenticado');

        $this->service->listDocuments(0, '');
    }

    public function testUploadDocumentWithValidData(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => true, 'user_id' => 123]);

        $result = $this->service->uploadDocument(123, 'test.pdf', 'factura', 'base64data');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('documento_id', $result);
        $this->assertArrayHasKey('nombre', $result);
        $this->assertArrayHasKey('tipo', $result);
        $this->assertArrayHasKey('usuario_id', $result);
        $this->assertArrayHasKey('url_descarga', $result);
        $this->assertArrayHasKey('fecha_creacion', $result);
        $this->assertEquals('test.pdf', $result['nombre']);
        $this->assertEquals('factura', $result['tipo']);
        $this->assertEquals(123, $result['usuario_id']);
    }

    public function testUploadDocumentWithInvalidAuth(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => false]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usuario no autenticado');

        $this->service->uploadDocument(0, '', '', '');
    }

    public function testUploadDocumentWithMissingData(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => true, 'user_id' => 123]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Faltan datos requeridos para subir el documento');

        $this->service->uploadDocument(123, '', 'factura', '');
    }

    public function testDownloadDocumentWithValidId(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => true, 'user_id' => 123]);

        $result = $this->service->downloadDocument(456);

        $this->assertIsArray($result);
        $this->assertEquals(456, $result['documento_id']);
        $this->assertEquals('factura-001.pdf', $result['nombre']);
        $this->assertArrayHasKey('download_url', $result);
        $this->assertArrayHasKey('usuario_id', $result);
    }

    public function testDownloadDocumentWithInvalidAuth(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => false]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usuario no autenticado');

        $this->service->downloadDocument(456);
    }

    public function testDownloadDocumentWithInvalidId(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => true, 'user_id' => 123]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ID de documento inválido');

        $this->service->downloadDocument(0);
    }

    public function testDeleteDocumentWithValidId(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => true, 'user_id' => 123]);

        $result = $this->service->deleteDocument(789);

        $this->assertIsArray($result);
        $this->assertEquals(789, $result['documento_id']);
        $this->assertTrue($result['deleted']);
        $this->assertArrayHasKey('fecha_eliminacion', $result);
    }

    public function testDeleteDocumentWithInvalidAuth(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => false]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usuario no autenticado');

        $this->service->deleteDocument(789);
    }

    public function testDeleteDocumentWithInvalidId(): void
    {
        $this->authContext->expects($this->once())
            ->method('resolve')
            ->with($this->request)
            ->willReturn(['success' => true, 'user_id' => 123]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ID de documento inválido');

        $this->service->deleteDocument(0);
    }
}
