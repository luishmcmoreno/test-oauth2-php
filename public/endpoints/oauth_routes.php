<?php
use CampuseroOAuth2\AppContainer as AppContainer;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use CampuseroOAuth2\Entities\UserEntity;
use Zend\Diactoros\Stream;

// Routes
$app = AppContainer::getInstance();
$app->get('/index', function (Request $request, Response $response, array $args) {
    // Sample log message
    print_r(urldecode('http://foo/bar?code=def502005413b34d219d496d9f2c6386f66fb8c5bbba9af3224027d71870ae9a0228d1f9e7b71ad7d695a26f7277168bb3a764fc005afdaf26db301deb53d87fb07371baf3bebb1202655b952da649255cdd109b0d4d43b4bb4a90d8fd5f14ddd3e76105e240c62f402a8301dd730a5afe684700ddfd9c26f1325590c3c7be5ec7833ce292bb8bb4552ceb76ca978a24a089082c39bedd0236e8c124aea38c07b1c59c907b5cda7f40dd4806ffa1ee30224978b01dd3e094813201bd09e6e51090f74640b5d242cbf81c1ca17cb84bc812b051d7e84a8d8753e4986443dc3ecdca13990a49ee238e5383cc253d0944a36ed6195e18a75996e9648f795274fe71b032b880f2b6392a136e14b94912a96e1abfb314ea598f40ac1a4b28e5becdcff1eac21a9e92ba4e6c782a0639732d005ad7d49131cfcc5cc0abb23d1883b8298260399804cf16c511652f72867de852aaff6938bfb1d044095755aad77bc238f130'));
    die();
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/authorize', function (ServerRequestInterface $request, ResponseInterface $response) use ($app) {
    /* @var \League\OAuth2\Server\AuthorizationServer $server */
    $server = $app->getContainer()->get(AuthorizationServer::class);
    try {
        // Validate the HTTP request and return an AuthorizationRequest object.
        // The auth request object can be serialized into a user's session
        $authRequest = $server->validateAuthorizationRequest($request);
        
        // Once the user has logged in set the user on the AuthorizationRequest
        $authRequest->setUser(new UserEntity());
        // Once the user has approved or denied the client update the status
        // (true = approved, false = denied)
        $authRequest->setAuthorizationApproved(true);
        // Return the HTTP redirect response
        return $server->completeAuthorizationRequest($authRequest, $response);
    } catch (OAuthServerException $exception) {
        return $exception->generateHttpResponse($response);
    } catch (\Exception $exception) {
        $body = new Stream('php://temp', 'r+');
        $body->write($exception->getMessage());
        return $response->withStatus(500)->withBody($body);
    }
});

$app->post('/token', function (ServerRequestInterface $request, ResponseInterface $response) use ($app) {
    /* @var \League\OAuth2\Server\AuthorizationServer $server */
    $server = $app->getContainer()->get(AuthorizationServer::class);
    try {
        return $server->respondToAccessTokenRequest($request, $response);
    } catch (OAuthServerException $exception) {
        return $exception->generateHttpResponse($response);
    } catch (\Exception $exception) {
        $body = new Stream('php://temp', 'r+');
        $body->write($exception->getMessage());
        return $response->withStatus(500)->withBody($body);
    }
});
