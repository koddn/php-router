<?php
/**
 * @Package: KODDN PHP Router - simple router created for custom CMS with multiple callbacks using next()
 * @Class  : ROUTER
 * @Author : Harpal Singh / @harpal.singh11 <singh.harpalkhl@gmail.com>
 * @Web    : https://koddn.com
 * @URL    : https://github.com/koddn/php-router
 * @Licence: The MIT License (MIT) - Copyright (c) - http://opensource.org/licenses/MIT
 */


final class ROUTER_RESPONSE{
    function end(){
        die();
    }
    function json($data){
        header('Content-Type: application/json');
        echo json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        $this->end();
    }
    function send($data){
        http_response_code(200);
        echo $data;
        $this->end();
    }
    function setStatus($statusCode){
        http_response_code($statusCode);
        return $this;
    }
    function clearSession(){
        session_destroy();
        return $this;
    }
    function clearCookies($specificName=null){
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                if($specificName===$name) {
                    setcookie($name, '', time() - 1110001);
                    setcookie($name, '', time() - 1110001, '/');

                }
                else{
                    setcookie($name, '', time() - 1110001);
                    setcookie($name, '', time() - 1110001, '/');
                }
            }

        }
        return $this;
    }
    function redirect( string $redirectTo, $replace = false, int $rCode = 301)
    {

            header("Location: $redirectTo", $replace,$rCode);
            die();
    }
}
class ROUTER
{
    // all the callbacks
    static private $RESPONSE;
    static private  $callBacks;

    static private  $callBackCounter;
    // request
    static private  $request;

    // integrate over callbacks when called

    static function NEXT()
    {
   
        self::$callBackCounter--;
        if (self::$callBackCounter >= 0) {

            self::$callBacks[self::$callBackCounter](self::$request, self::$RESPONSE,"ROUTER::NEXT");
        }
    }




// handles the request
    static function controller(string $query, ...$callbacks) // Returns False if not Matched
    {
        self::$callBacks = array_reverse($callbacks);
        self::$callBackCounter = count($callbacks);
        // striping query parameters
        $url = explode('?', $_SERVER["REQUEST_URI"], 2)[0];
        $params = [];
        $routeVariables = "/(\*|(\:[a-zA-Z]+))/";

        if (preg_match($routeVariables, $query)) {

            $catch_routes = ['/\/(?!$)/', '/\*/', '/(\:[a-zA-Z]+)/', '/\/$/'];
            $final_regex = ['\/', '(?:(?:.)*)', '([^\/]+(?:\/*))', '\/{0,1}'];
            $regex_query = preg_replace($catch_routes, $final_regex, $query);
            $isMatched = preg_match("/$regex_query/", $url, $matches);

            // if !matched return or match != url return false
            if (!$isMatched || $matches[0] != $url) {

                return false;
            }


            preg_match_all("/:([a-zA-Z]+)/", $query, $param_matches);
            // extracting params
            foreach ($param_matches[1] as $index => $param) {
                $params[$param] = rtrim($matches[$index + 1], '/');
                if (preg_match('/((.+)\/{2,}(.*))|(?:\/)(.+)/', $matches[$index + 1])) {
                    return FALSE;
                }
            }
        } else {
            $count = strlen($query);
            if (!($url === $query || ($url . "/" === $query && $url[strlen($url) - 1] !== '/') || (substr($query, -$count) !== '/' && $url === $query . '/' && $query[$count - 1] != '/'))) {
                return false;
            }
        }

        self::$RESPONSE=new ROUTER_RESPONSE();
        $protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
        $domain = $_SERVER['HTTP_HOST'];
        $fullUrl = $protocol . $domain . $_SERVER["REQUEST_URI"];
        $path = $url;
        $rPath = preg_replace('/^\/(.*?)(\/)*$/', '$1', $url);
        $url = $protocol . $domain . $url;
        self::$request = ['$fullUrl' => $fullUrl, 'url' => $url, 'path' => $path, 'params' => $params, 'rPath' => $rPath];

        // Calling callbacks
        self::NEXT();

        die(); // Die When Everything is Done


    }

    // redirect

    static function redirect(string $query, $callback, string $redirectTo, $replace = false, int $rCode = 301)
    {
        return self::controller($query, function () use ($callback,$redirectTo, $replace, $rCode) {
            $callback();
            header("Location: $redirectTo", $replace,$rCode);
        });
    }
    // Return boolean FALSE if not matched
    static function any( $query, ...$callbacks)
    {

        return self::controller($query, ...$callbacks);
    }
    // get Request
    static function get( $query, ...$callbacks)
    {
        $method=getenv('REQUEST_METHOD', true) ?: getenv('REQUEST_METHOD');
        if ($method !== 'GET') return false;
        return self::controller($query, ...$callbacks);
    }
    // post Request
    static function post( $query, ...$callbacks)
    {
        $method=getenv('REQUEST_METHOD', true) ?: getenv('REQUEST_METHOD');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        return self::controller($query, ...$callbacks);
    }

    // delete request
    static function delete( $query, ...$callbacks)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') return false;
        return self::controller($query, ...$callbacks);
    }

    // put request
    static function put( $query, ...$callbacks)
    {
        $method=getenv('REQUEST_METHOD', true) ?: getenv('REQUEST_METHOD');
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') return false;
        return self::controller($query, ...$callbacks);
    }

    // patch request
    static function patch( $query, ...$callbacks)
    {
        $method=getenv('REQUEST_METHOD', true) ?: getenv('REQUEST_METHOD');
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') return false;
        return self::controller($query, ...$callbacks);
    }
}