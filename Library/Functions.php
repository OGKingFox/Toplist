<?php

class Functions {

    public static function replace($search, $replace, $string) {
        return str_replace($search, $replace, $string);
    }

    public static function filter($item_list, $key, $value) {
        foreach ($item_list as $item) {
            if ($item[$key] == $value) {
                return $item;
            }
        }
        return null;
    }

    public static function mapArrayAsString($array, $key) {
        return implode(',', array_map(function ($item) use ($key) {
            return $item[$key];
        }, $array));
    }

    public static function startsWith($haystack, $needle) {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    public static function endsWith($haystack, $needle) {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * get access token from header
     */
    public static function getBearerToken() {
        $headers = self::getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Gets the authorization header.
     * @return string|null
     */
    public static function getAuthorizationHeader() {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
}