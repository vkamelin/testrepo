<?php

namespace App;

class Security extends \Phalcon\Security
{
    
    private static $sessionToken = null;
    
    /**
     * {@inheritdoc}
     */
    public function getSessionToken()
    {
        if (self::$sessionToken === null) {
            self::$sessionToken = parent::getSessionToken();
        }
        return self::$sessionToken;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        if (self::$sessionToken === null) {
            $this->getSessionToken(); //don't lose real session token, setup!
        }
        return parent::getToken(); //continues normally
    }
    
}