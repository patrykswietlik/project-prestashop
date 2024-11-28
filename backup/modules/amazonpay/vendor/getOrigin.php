<?php

class AmazonPayVendorOrigin
{

    public static function get()
    {
        if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
            return $_SERVER['HTTP_ORIGIN'];
        }
        else if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            return $_SERVER['HTTP_REFERER'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

}