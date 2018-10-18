<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CampuseroOAuth2\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use CampuseroOAuth2\Entities\ClientEntity;
use CampuseroOAuth2\App\Service;
use CampuseroOAuth2\App\Resource\ClientApi;
use \PDO;

class ClientRepository extends Service implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null, $mustValidateSecret = true) {
        $clientApi =  new ClientApi();

        $clients = $clientApi->getClientApiService()->getAllClientApis($grantType);

        /*
        $clients = [
            'myawesomeapp' => [
                'secret'          => password_hash('abc123', PASSWORD_BCRYPT),
                'name'            => 'My Awesome App',
                'redirect_uri'    => 'http://foo/bar',
                'is_confidential' => true,
            ],
        ];*/

        // Check if client is registered
        if (array_key_exists($clientIdentifier, $clients) === false) {
            return;
        }

        if (
            $mustValidateSecret === true
            && $clients[$clientIdentifier]['is_confidential'] === true
            && password_verify($clientSecret, $clients[$clientIdentifier]['secret']) === false
        ) {
            
            return;
        }

        $client = new ClientEntity();
        $client->setIdentifier($clientIdentifier);
        $client->setName($clients[$clientIdentifier]['name']);
        $client->setRedirectUri($clients[$clientIdentifier]['redirect_uri']);

        return $client;
    }




}


