<?php

declare(strict_types=1);

use Desarrimage\CatalogRepository;
use Desarrimage\Database;
use Desarrimage\Env;
use Desarrimage\GuideRepository;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response as SlimResponse;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
AppFactory::setSlimHttpDecoratorsAutomaticDetection(false);
$app = AppFactory::create();

$json = static function (array $data, int $status = 200): Response {
    $response = new SlimResponse($status);
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
};

$app->add(static function (Request $request, $handler) use ($json): Response {
    if ($request->getMethod() === 'OPTIONS') {
        $response = $json([], 204);
    } else {
        $response = $handler->handle($request);
    }

    $configuredOrigins = explode(',', Env::get('CORS_ORIGIN', 'http://localhost:5173'));
    $requestOrigin = $request->getHeaderLine('Origin');
    $origin = in_array($requestOrigin, $configuredOrigins, true)
        ? $requestOrigin
        : trim($configuredOrigins[0]);

    return $response
        ->withHeader('Access-Control-Allow-Origin', $origin)
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Accept')
        ->withHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->withHeader('Vary', 'Origin');
});

$app->get('/api/health', static function () use ($json): Response {
    Database::connect()->query('SELECT 1');

    return $json(['status' => 'ok']);
});

$app->get('/api/stats', static function () use ($json): Response {
    return $json(['data' => (new CatalogRepository(Database::connect()))->stats()]);
});

$app->get('/api/categories', static function () use ($json): Response {
    return $json(['data' => (new CatalogRepository(Database::connect()))->categories()]);
});

$app->get('/api/openapi.yaml', static function (): Response {
    $response = new SlimResponse();
    $response->getBody()->write(file_get_contents(dirname(__DIR__) . '/openapi.yaml'));

    return $response
        ->withHeader('Content-Type', 'application/yaml; charset=utf-8')
        ->withHeader('Cache-Control', 'public, max-age=300');
});

$app->get('/api/products', static function (Request $request) use ($json): Response {
    $query = $request->getQueryParams();
    $products = (new CatalogRepository(Database::connect()))->products(
        isset($query['search']) ? trim((string) $query['search']) : null,
        isset($query['category']) ? trim((string) $query['category']) : null,
        isset($query['region']) ? trim((string) $query['region']) : null
    );

    return $json(['data' => $products, 'meta' => ['count' => count($products)]]);
});

$app->get('/api/guides', static function () use ($json): Response {
    $guides = (new GuideRepository(Database::connect()))->summaries();

    return $json(['data' => $guides, 'meta' => ['count' => count($guides)]]);
});

$app->get('/api/guides/{slug}', static function (Request $request, Response $response, array $args) use ($json): Response {
    $guide = (new GuideRepository(Database::connect()))->find($args['slug']);
    if ($guide === null) {
        throw new HttpNotFoundException($request, 'Guide introuvable.');
    }

    return $json(['data' => $guide]);
});

$app->get('/api/guides/{slug}/{section}', static function (Request $request, Response $response, array $args) use ($json): Response {
    if (!in_array($args['section'], ['comparison', 'costs', 'risks'], true)) {
        throw new HttpNotFoundException($request, 'Section de guide introuvable.');
    }
    $guide = (new GuideRepository(Database::connect()))->find($args['slug']);
    if ($guide === null) {
        throw new HttpNotFoundException($request, 'Guide introuvable.');
    }

    return $json(['data' => $guide[$args['section']]]);
});

$app->get('/api/products/{slug}', static function (Request $request, Response $response, array $args) use ($json): Response {
    $product = (new CatalogRepository(Database::connect()))->product($args['slug']);
    if ($product === null) {
        throw new HttpNotFoundException($request, 'Produit introuvable.');
    }

    return $json(['data' => $product]);
});

$errorMiddleware = $app->addErrorMiddleware(Env::get('APP_ENV', 'production') === 'development', true, true);
$errorMiddleware->setDefaultErrorHandler(
    static function (Request $request, Throwable $exception) use ($json): Response {
        $status = $exception instanceof HttpException ? $exception->getCode() : 500;
        if ($status >= 500) {
            error_log($exception->getMessage());
        }

        return $json([
            'error' => $status >= 500 ? 'Le catalogue est momentanément indisponible.' : $exception->getMessage(),
        ], $status);
    }
);

$app->run();
