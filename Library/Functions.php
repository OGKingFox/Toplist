<?php

class Functions {

    private static $purifier;

    public static function getDaysOfMonth() {
        $daysArr = [];
        $daysInMonth = date('t');

        for ($i = 1; $i < $daysInMonth + 1; $i++) {
            $day = date("Y-m-".$i);
            $daysArr[$day] = 0;
        }
        return $daysArr;
    }

    public static function getPurifier() {
        $allowed_html = [
            'div[class]','span[style]','a[href|class|target]','img[src|class]','h1','h2','h3','p[class]','strong','em','ul',
            'u','ol','li','table[class]','tr','td','th','thead','tbody'
        ];

        if (!self::$purifier) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set("Core.Encoding", 'utf-8');
            $config->set('AutoFormat.RemoveEmpty', true);
            $config->set("HTML.Allowed", implode(',', $allowed_html));
            $config->set('HTML.AllowedAttributes', 'src, height, width, alt, href, class, style');
            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier;
    }

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

    public static function apiRequest($url, $post=FALSE, $headers=array()) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        $headers[] = 'Accept: application/json';

        if($_SESSION["access_token"])
            $headers[] = 'Authorization: Bearer ' . $_SESSION["access_token"];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response);
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