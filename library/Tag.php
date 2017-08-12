<?php

namespace App;

use User\Model\User;

/**
 * Class Tag
 * @package App
 */
class Tag extends \Phalcon\Tag
{

    /**
     * Get gravatar url
     *
     * @param string $email
     * @param int $size
     * @return string
     */
    public static function gravatar($email = '', $size = '200')
    {
        $default = urlencode(self::getUrlService()->getStaticBaseUri() . 'assets/img/default-avatar.png');
        return 'https://www.gravatar.com/avatar/' . md5($email) . '?&s=' . $size . '&d=' . $default;
    }
    
    public static function staticUri($url = '')
    {
        return self::getUrlService()->getStaticBaseUri() . $url;
    }
    
    /**
     * Get url by route name and parameters
     *
     * @param $routename
     * @param array $parameters
     * @return string
     */
    public static function url($routename, array $parameters = null)
    {
        if (is_null($parameters)) {
            return self::getUrlService()->get(array(
                'for' => $routename
            ));
        }

        return self::getUrlService()->get(array_merge(
            array(
                'for' => $routename
            ),
            $parameters
        ));
    }

    /**
     * Get url by parameters
     *
     * @param $module
     * @param string $controller
     * @param string $action
     * @return string
     */
    public static function u($module, $controller = null, $action = null)
    {
        /**
         * @todo rewrite with routers
         */
        if ($controller) {
            if ($action) {
                return '/' . $module . '/' . $controller . '/' . $action . '/';
            }

            return '/' . $module . '/' . $controller . '/';
        }

        return self::getUrlService()->get(array(
            'for' => $module
        ));
    }

}
