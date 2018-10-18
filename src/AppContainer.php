<?php

namespace CampuseroOAuth2;
use Slim\App;

class AppContainer
{
    private static $app = null;

    public static function getInstance()
    {
        if (null === self::$app) {
            self::$app = self::makeInstance();
        }

        return self::$app;
    }

    private static function makeInstance()
    {
        $settings = require __DIR__ . '/../src/settings.php';
        $app = new App($settings);
        // do all logic for adding routes etc

        return $app;
    }
}