<?php
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserMiddleware
{
    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */

     /**
     * @var AccessTokenRepositoryInterface
     */
    private $accessTokenRepository;

    /**
     * @var \League\OAuth2\Server\CryptKey
     */
    protected $publicKey;

    public function __construct(AccessTokenRepositoryInterface $accessTokenRepository)
    {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->publicKeyPath = 'file://' . __DIR__ . '/../public.key';
        
    }
    public function __invoke($request, $response, $next)
    {
	    if (empty($request->getHeader('Authorization'))) {
	        $response = $response->withStatus(400);
	        $response->getBody()->write(json_encode(['message' => 'Token not found']));
	        return $response;
	    }
	    //Get token for accessing this route
	    $token = $request->getHeader('Authorization')[0];
	    //validar o token
        $jwt = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $token));
        try {
            // Attempt to parse and validate the JWT
            $token = (new Parser())->parse($jwt);
            try {
                if ($token->verify(new Sha256(), $this->publicKeyPath) === false) {
                    throw OAuthServerException::accessDenied('Access token could not be verified');
                }
            } catch (\BadMethodCallException $exception) {
                throw OAuthServerException::accessDenied('Access token is not signed');
            }

            // Ensure access token hasn't expired
            $data = new ValidationData();
            $data->setCurrentTime(time());

            if ($token->validate($data) === false) {
                throw OAuthServerException::accessDenied('Access token is invalid');
            }

            // Check if token has been revoked
            if ($this->accessTokenRepository->isAccessTokenRevoked($token->getClaim('jti'))) {
                throw OAuthServerException::accessDenied('Access token has been revoked');
            }
            // Check if this access token belongs to a first_part client
            if (!$this->accessTokenRepository->isFirtsPartAccessToken($token->getClaim('jti'))) {
                throw OAuthServerException::accessDenied('this is not a first part token');
            }

            //verify if Authorzation is first_part
            // Return the request with additional attributes
            $response = $next($request, $response);
        } catch (\InvalidArgumentException $exception) {
            // JWT couldn't be parsed so return the request as is
            throw OAuthServerException::accessDenied($exception->getMessage());
        } catch (\RuntimeException $exception) {
            //JWR couldn't be parsed so return the request as is
            throw OAuthServerException::accessDenied('Error while decoding to JSON');
        }
	    return $response;
	}
}
