<?php

include __DIR__ . '/../vendor/autoload.php';

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use CampuseroOAuth2\Repositories\AccessTokenRepository;
use CampuseroOAuth2\Repositories\AuthCodeRepository;
use CampuseroOAuth2\Repositories\ClientRepository;
use CampuseroOAuth2\Repositories\UserRepository;
use CampuseroOAuth2\Repositories\RefreshTokenRepository;
use CampuseroOAuth2\Repositories\ScopeRepository;



return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],
    AuthorizationServer::class => function () {
        // Init our repositories
        $clientRepository = new ClientRepository();
        $scopeRepository = new ScopeRepository();
        $accessTokenRepository = new AccessTokenRepository();
        $authCodeRepository = new AuthCodeRepository();
        $refreshTokenRepository = new RefreshTokenRepository();
        $privateKeyPath = 'file://' . __DIR__ . '/../private.key';
        // Setup the authorization server
        $server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKeyPath,
            'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'
        );
        // Enable the authentication code grant on the server with a token TTL of 1 hour

        $server->enableGrantType(
            new PasswordGrant(
                new UserRepository(),
                new RefreshTokenRepository()
            )
        );

        $server->enableGrantType(
            new AuthCodeGrant(
                $authCodeRepository,
                $refreshTokenRepository,
                new \DateInterval('PT10M')
            ),
            new \DateInterval('PT1H')
        );

        $grant = new RefreshTokenGrant($refreshTokenRepository);
        $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // The refresh token will expire in 1 month
        $server->enableGrantType(
            $grant,
            new \DateInterval('PT1H')
        );
        return $server;
    },
];
