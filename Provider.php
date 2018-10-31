<?php

namespace SocialiteProviders\Identity;

use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider implements ProviderInterface
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'IDENTITY';

    /**
     * {@inheritdoc}
     */
    protected function getBaseUrl()
    {
        $port = is_null($this->getServerPort()) ? '' : ':'.$this->getServerPort();
        $subdirectory = is_null($this->getServerDirectory()) ? '' : '/'.$this->getServerDirectory();
        return 'https://'.$this->getServerHost().$port.$subdirectory;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            $this->getBaseUrl().'/authorize', $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        // return $this->getBaseUrl().'/introspect?scope=openid';
        return $this->getBaseUrl().'/token?scope=openid';
    }

    // public function getAccessTokenResponse($code)
    // {
    //     $username = \Config::get('services.identity.client_id');
    //     $password = \Config::get('services.identity.client_secret');
    //     $response = $this->getHttpClient()->post($this->getTokenUrl(), [
    //         'headers' => ['Content-Type' => 'application/x-www-form-urlencoded','Authorization' => 'Bearer '.$username.$password],
    //         'body' => 'token='.$code,
    //     ]);
    //     // return json_decode($response->getBody(), true);
    //     if($response->getStatusCode() < 400){
    //         return ['access_token'=>$code];
    //     } else {
    //         return false;
    //     }
    // }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            $this->getBaseUrl().'/userinfo?schema=openid', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        if(isset($user['given_name'])){
            $name = $user['given_name'];
        }
        if(isset($user['preferred_username'])){
            $name = $user['preferred_username'];
        }
        return (new User())->setRaw($user)->map([
            'id'       => $user['userid'],
            'nickname' => $user['sub'],
            'name'     => $name,
            'role' => $user['role'],
            'email'    => $user['email']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getServerHost()
    {
        // return $this->getConfig('identity_host', null);
        return \Config::get('services.identity.host');
    }

    /**
     * {@inheritdoc}
     */
    protected function getServerPort()
    {
        // return $this->getConfig('identity_port', null);
        return \Config::get('services.identity.port');
    }

    /**
     * {@inheritdoc}
     */
    protected function getServerDirectory()
    {
        // return $this->getConfig('identity_directory', null);
        return \Config::get('services.identity.directory');
    }
}
