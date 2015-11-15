<?php namespace Sframe\Html;
use Sutil\Session\Session;

class Csrf
{
    public static function getToken()
    {
        $csrf = Session::get('_csrf');
        if (!$csrf) {
            $csrf = md5(mt_rand(1000, 9999));
            Session::set('_csrf', $csrf);
        }
        return $csrf;
    }

    public static function validate($token)
    {
        return ($token && Session::get('_csrf') == $token);
    }
}