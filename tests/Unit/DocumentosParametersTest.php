<?php
declare(strict_types=1);

use App\Modules\Documentos\DocumentosController;
use App\Modules\Documentos\Services\DocumentosService;
use App\Core\Http\Request;
use App\Core\Http\Response;
use PHPUnit\Framework\TestCase;

class DocumentosParametersTest extends TestCase
{
    private DocumentosController $controller;
    private Request $request;
    private DocumentosService $service;

    protected function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->service = $this->createMock(DocumentosService::class);
        $this->controller = new DocumentosController($this->request, $this->service);
    }

    public function testListDocumentsWithValidParameters(): void
    {
        $this->request->expects($this->once())
            ->method('input')
            ->with('usuario_id', 0)
            ->willReturn(123);

        $this->request->expects($this->once())
            ->method('input')
            ->with('tipo', '')
            ->willReturn('factura');

        $this->service->expects($this->once())
            ->method('listDocuments')
            ->with(123, 'factura')
            ->willReturn([
                [
                    'documento_id' => 1,
                    'nombre' => 'factura-001.pdf',
                    'tipo' => 'factura',
                    'usuario_id' => 123,
                    'fecha_creacion' => '2026-04-03 12:00:00'
                ]
            ]);

        // Capture the output
        ob_start();
        $this->controller->listDocuments();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertCount(1, $response['data']);
        $this->assertEquals('factura-001.pdf', $response['data'][0]['nombre']);
    }

    public function testListDocumentsWithInvalidUserId(): void
    {
        $this->request->expects($this->once())
            ->method('input')
            ->with('usuario_id', 0)
            ->willReturn(0);

        $this->request->expects($this->once())
            ->method('input')
            ->with('tipo', '')
            ->willReturn('');

        $this->service->expects($this->once())
            ->method('listDocuments')
            ->with(0, '')
            ->willThrowException(new \Exception('Usuario no autenticado'));

        ob_start();
        $this->controller->listDocuments();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('LIST_DOCUMENTS_ERROR', $response['error']['code']);
        $this->assertEquals('Usuario no autenticado', $response['error']['message']);
    }

    public function testUploadDocumentWithValidParameters(): void
    {
        $this->request->expects($this->exactly(3))
            ->method('input')
            ->willReturnMap([
                ['usuario_id', 0, 123],
                ['nombre', '', 'documento.pdf'],
                ['tipo', '', 'factura'],
                ['contenido_base64', '', 'base64content']
            ]);

        $this->service->expects($this->once())
            ->method('uploadDocument')
            ->with(123, 'documento.pdf', 'factura', 'base64content')
            ->willReturn([
                'documento_id' => 456,
                'nombre' => 'documento.pdf',
                'tipo' => 'factura',
                'usuario_id' => 123,
                'url_descarga' => 'https://example.com/download/documento.pdf',
                'fecha_creacion' => '2026-04-03 12:00:00'
            ]);

        ob_start();
        $this->controller->uploadDocument();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals(456, $response['data']['documento_id']);
        $this->assertEquals('documento.pdf', $response['data']['nombre']);
    }

    public function testDownloadDocumentWithValidId(): void
    {
        $this->request->expects($this->once())
            ->method('input')
            ->with('documento_id', 0)
            ->willReturn(789);

        $this->service->expects($this->once())
            ->method('downloadDocument')
            ->with(789)
            ->willReturn([
                'documento_id' => 789,
                'nombre' => 'factura-001.pdf',
                'tipo' => 'factura',
                'download_url' => 'https://example.com/download/789',
                'usuario_id' => 123
            ]);

        ob_start();
        $this->controller->downloadDocument();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals(789, $response['data']['documento_id']);
        $this->assertEquals('factura-001.pdf', $response['data']['nombre']);
    }

    public function testDeleteDocumentWithValidId(): void
    {
        $this->request->expects($this->once())
            ->method('input')
            ->with('documento_id', 0)
            ->willReturn(999);

        $this->service->expects($this->once())
            ->method('deleteDocument')
            ->with(999)
            ->willReturn([
                'documento_id' => 999,
                'deleted' => true,
                'fecha_eliminacion' => '2026-04-03 12:00:00'
            ]);

        ob_start();
        $this->controller->deleteDocument();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertTrue($response['data']['deleted']);
        $this->assertEquals(999, $response['data']['documento_id']);
    }
}
