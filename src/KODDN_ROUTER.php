<?php
/**
 * @Package: KODDN PHP Router - simple router created for custom CMS with multiple callbacks using next()
 * @Class  : ROUTER
 * @Author : Harpal Singh / @harpal.singh11 <singh.harpalkhl@gmail.com>
 * @Web    : https://koddn.com
 * @URL    : https://github.com/koddn/php-router
 * @Licence: The MIT License (MIT) - Copyright (c) - http://opensource.org/licenses/MIT
 */




final class ROUTER_RESPONSE
{
    function json(array $data): self
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        return $this;
    }

    function end(): void
    {
        die();
    }

    function send(string $data): self
    {
        http_response_code(200);
        echo $data;
        return $this;
    }

    function setStatus(int $statusCode): self
    {
        http_response_code($statusCode);
        return $this;
    }

    function clearSession(): self
    {
        session_destroy();
        return $this;
    }

    function clearCookies(array $specificNames = []): self
    {
        $cookies = getenv('HTTP_COOKIE', true) ?: getenv('HTTP_COOKIE');
        if ($cookies) {
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                if (array_key_exists($name, $specificNames)) {
                    setcookie($name, '', time() - 1110001);
                    setcookie($name, '', time() - 1110001, '/');


                } elseif (empty($specificNames)) {
                    setcookie($name, '', time() - 1110001);
                    setcookie($name, '', time() - 1110001, '/');
                }
            }

        }
        return $this;
    }

    function redirect(string $redirectTo, bool $replace = false, int $rCode = 301): void
    {

        header("Location: $redirectTo", $replace, $rCode);

        $this->end();
    }
}
final class ROUTER_REQUEST{
      private string $url = "";
      private string $protocol ="";
      private string $domain = "";
  
      private string $urlFull = "";
      private string $path ="";
      private string $rPath ="";
      private array $params=[];

      private string $body = "";
      private array $bodyJSON = [];

      function setParam($param){
            $this->params=$param;
      }
    function __construct(){
       $this->url = explode('?', getenv('REQUEST_URI', true) ?: getenv('REQUEST_URI'), 2)[0];
       $this->protocol = getenv('HTTPS') ? 'https://' : 'http://';
       $this->domain = getenv('HTTP_HOST');
       $this->urlFull =  $this->protocol .  $this->domain . getenv('REQUEST_URI', true) ?: getenv('REQUEST_URI');
       $this->path =  $this->url;
       $this->rPath = preg_replace('/^\/(.*?)(\/)*$/', '$1',   $this->url);
        
       $this->body = file_get_contents('php://input');
       $this->bodyJSON=json_decode( $this->body,true)??[];
      
    }

        function params(){
            return $this->params;
        }
        function bodyJSON(){
     
            return  $this->bodyJSON;
        }
        function body(){
            return $this->body;
        }
        function url(){
            return $this->protocol. $this->domain. $this->url;
        }
        function path(){
            return $this->path;
        }
        function fullURL(){
            return $this->urlFull;
        }
        function rPATH(){
            return $this->rPath;
        }
        function portocol(){
            return $this->protocol;
        }
        

}
class ROUTER
{
    static  private string $initUrl = "";
    static  private string $protocol ="";
    static  private string $domain = "";
    static  private string $url = "";
    static  private string $urlFull = "";
    static  private string $path ="";
    static  private string $rPath ="";


    static  private string $inputJSON = "";
    static  private array $bodyJSON = [];
  
    // all the callbacks
    static private ROUTER_RESPONSE $RESPONSE;
    static private array $callBacks;
    static private int $callBackCounter;
    static private ROUTER_REQUEST $REQUEST;

    // request

    static function use(string $initUrl, ...$callback):bool
    {
        $temp=self::$initUrl;
        if (!self::useController($initUrl,...$callback)) {
            return false;
        }
        self::$initUrl = $temp;
        return true;
    }

    // integrate over callbacks when called
    //use

    static function redirect(string $query, string $redirectTo, callable $callback = null, bool $replace = false, int $rCode = 301): bool
    {
        self::controller($query, function () use ($callback, $redirectTo, $replace, $rCode) {

            if ($callback) {
                $callback();
            }
            header("Location: $redirectTo", $replace, $rCode);
            die();
        });
        return false;
    }

    private static function useController(string $query,...$callbacks)
    {
        // concatenate initial USE
        $orgin_query=$query;
        $query = self::$initUrl . $query;

        $url = explode('?', getenv('REQUEST_URI', true) ?: getenv('REQUEST_URI'), 2)[0];
        $params = [];


        $catch_routes = ['/\//', '/\*/', '/(\:[a-zA-Z]+)/'];
        $final_regex = ['\/', '(?:(?:.)*)', '([^\/]+(?:\/{0,1}))'];

        if ($url!='' && $url[strlen($url) - 1] === '/') {
            // if / is at the end of the URL
            $catch_routes = [
                '/\/(?!$)/',  // match / but not in End
                '/\*/', // all
                '/(\:[a-zA-Z]+)/', // :paramName
                '/\/$/' // match / in the end
            ];
            $final_regex = [
                '\/',
                '(?:(?:.)*)',
              '([^\/]+(?:\/{0,1}))',//  '([^\/]+(?:\/*))',
                '\/{0,1}'
            ];
        }
        $regex_query = preg_replace($catch_routes, $final_regex, $query);
        $isMatched = preg_match("/^$regex_query/", $url, $matches);

        // if !matched return or match != url return false

        if (!$isMatched || (strlen($query) === strlen($url) && $matches[0] != $url)) {
            return false;
        }

        preg_match_all("/:([a-zA-Z]+)/", $query, $param_matches);
        // extracting params
        foreach ($param_matches[1] as $index => $param) {

            $params[$param] = rtrim($matches[$index + 1], '/');
/*
            if (preg_match('/((.+)\/{2,}(.*))|(?:\/)(.+)/', $matches[$index + 1])) {
                //   return FALSE;
            }
*/
        }
        self::$initUrl .= $orgin_query;

        self::setNext($url, $params, ...$callbacks);
        return true;
    }


    private static function controller(string $query, ...$callbacks): bool// Returns False if not Matched
    {
        $query = self::$initUrl . $query;

        // striping query parameters
        $url = explode('?', getenv('REQUEST_URI', true) ?: getenv('REQUEST_URI'), 2)[0];
     //   echo "\n<br>Query = $query URL= $url #\n";
        $params = [];
        $routeVariables = "/(\*|(\:[a-zA-Z]+))/";
        if (preg_match($routeVariables, $query)) {
            $catch_routes = ['/\/(?!$)/', '/\*/', '/(\:[a-zA-Z]+)/', '/\/$/'];
            $final_regex = ['\/', '(?:(?:.)*)', '([^\/]+(?:\/{0,1}))', '\/{0,1}'];
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

               /* if (preg_match('/((.+)\/{2,}(.*))|(?:\/)(.+)/', $matches[$index + 1])) {
                   // return FALSE;
                }
               */
            }

        } else {
            $count = strlen($query);
            if (!($url === $query || ($url . "/" === $query && $url[strlen($url) - 1] !== '/') || (substr($query, -$count) !== '/' && $url === $query . '/' && $query[$count - 1] != '/'))) {
                return false;
            }
        }
        self::setNext($url, $params, ...$callbacks);

        return true;
    }

    private static function setNext(string $url, array $params, ...$callbacks): void
    {

        self::$callBacks = array_reverse($callbacks);
        self::$callBackCounter = count($callbacks);

        self::$RESPONSE = self::$RESPONSE ?? new ROUTER_RESPONSE();
        self::$REQUEST = self::$REQUEST ?? new ROUTER_REQUEST();


        self::$REQUEST->setParam($params);
        // Calling callbacks

        self::NEXT();

    }

// handles the request
    static function NEXT(): void
    {

        self::$callBackCounter--;
        if (self::$callBackCounter >= 0) {

            if(self::$callBackCounter==0){
                self::$callBacks[self::$callBackCounter](self::$REQUEST, self::$RESPONSE, function(){});
            }
            else{
                self::$callBacks[self::$callBackCounter](self::$REQUEST, self::$RESPONSE,function(){ROUTER::NEXT();});
            }

        }
    }

    // redirect

    static function any(string $query, ...$callbacks): bool
    {
        return self::controller($query, ...$callbacks);
    }

    // Return boolean FALSE if not matched

    static function get(string $query, ...$callbacks): bool
    {
        $method = getenv('REQUEST_METHOD', true) ? getenv('REQUEST_METHOD', true) : getenv('REQUEST_METHOD');
        if ($method !== 'GET') return false;
        return self::controller($query, ...$callbacks);
    }

    // get Request

    static function post(string $query, ...$callbacks): bool
    {
        $method = getenv('REQUEST_METHOD', true) ? getenv('REQUEST_METHOD', true) : getenv('REQUEST_METHOD');
        if ($method !== 'POST') return false;
        return self::controller($query, ...$callbacks);
    }

    // post Request

    static function delete(string $query, ...$callbacks): bool
    {
        $method = getenv('REQUEST_METHOD', true) ? getenv('REQUEST_METHOD', true) : getenv('REQUEST_METHOD');
        if ($method !== 'DELETE') return false;
        return self::controller($query, ...$callbacks);
    }

    // delete request

    static function put(string $query, ...$callbacks): bool
    {
        $method = getenv('REQUEST_METHOD', true) ? getenv('REQUEST_METHOD', true) : getenv('REQUEST_METHOD');
        if ($method !== 'PUT') return false;
        return self::controller($query, ...$callbacks);
    }

    // put request

    static function patch(string $query, ...$callbacks): bool
    {
        $method = getenv('REQUEST_METHOD', true) ? getenv('REQUEST_METHOD', true) : getenv('REQUEST_METHOD');
        if ($method !== 'PATCH') return false;
        return self::controller($query, ...$callbacks);
    }

}