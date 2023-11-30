<?php

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;

use Clegginabox\PDFMerger\PDFMerger;

class JsonBodyParserMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strstr($contentType, 'application/json')) {
            $contents = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            }
        }

        return $handler->handle($request);
    }
}

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->post('/', function (Request $request, Response $response, $args) {

    $unique_id = microtime(true) * 10000;

    mkdir('tmp/' . $unique_id, 0777, true);

    $dir = 'tmp/' . $unique_id . '/';

    $result["success"] = false;

    // Удалить временные файлы
    array_map('unlink', glob("{$dir}*"));

    $parsedBody = $request->getParsedBody();
    
    $files_to_merge = [];
    // Сохранить файлы
    foreach ($parsedBody as &$pdf_file_data) {
        $b64 = $pdf_file_data['base64'];
        $fname = $pdf_file_data['name'];

        $bin = '';
        $bin = base64_decode($b64, true);

        if (strpos($bin, '%PDF') !== 0) {
            $result["message"] = "Неверный PDF файл!";
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        }

        file_put_contents($dir . $fname, $bin);
        $files_to_merge[] = $dir . $fname;
    }

    $pdf = new PDFMerger;
    foreach ($files_to_merge as &$file_to_merge) {
        $pdf->addPDF($file_to_merge, 'all');
    }
    $pdf->merge('file', $dir . 'result.pdf', 'P');

    $result_b64 = base64_encode(file_get_contents($dir . 'result.pdf'));

    array_map('unlink', glob("{$dir}*"));
    rmdir('tmp/' . $unique_id);

    $result["success"] = true;
    $result["base64"] = $result_b64;
    $result["name"] = 'result.pdf';
    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("API для слияния PDF файлов принимает только POST запросы!");
    return $response;
});

$app->run();
