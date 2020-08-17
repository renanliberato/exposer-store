<?php

namespace RenanLiberato\Exposer\Persistors;

use RenanLiberato\ExposerStore\Store\Store;

class CookiePersistor implements PersistorInterface
{
    /**
     * @var string
     */
    private $cookieName;

    /**
     * @var string
     */
    private $jwtKey;

    /**
     * @param string $cookieName
     * @param string $jwtKey
     */
    public function __construct($cookieName, $jwtKey)
    {
        $this->cookieName = $cookieName;
        $this->jwtKey = $jwtKey;
    }

    /**
     * @param array $state
     * @return void
     */
    public function persistState($state)
    {
        $jwt = \Firebase\JWT\JWT::encode($state, $this->jwtKey);

        setcookie($this->cookieName, $jwt);
    }

    /**
     * @return void
     */
    public function getPersistedState()
    {
        if (isset($_COOKIE[$this->cookieName])) {
            return json_decode(json_encode((array)\Firebase\JWT\JWT::decode($_COOKIE[$this->cookieName], $this->jwtKey, ['HS256'])), true);
        }

        return [];
    }
}