<?php
use CampuseroOAuth2\AppContainer as AppContainer;
use CampuseroOAuth2\App\Resource\ClientApi;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;


// Routes
$app = AppContainer::getInstance();

$app->post('/client', function (ServerRequestInterface $request, ResponseInterface $response) use ($app) {
        
    try {
        //return $server->respondToAccessTokenRequest($request, $response);
        $clientApi = new ClientApi();
        $response_body = $clientApi->registerClientApi($request, $response);
        return $response->withStatus($response_body['http_code'])->withJson($response_body['response']);
    } catch (\Exception $exception) {
        $body = new Stream('php://temp', 'r+');
        $body->write($exception->getMessage());
        return $response->withStatus(500)->withBody($body);
    }
});
