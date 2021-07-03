# Koddn PHP Router

Koddn Php router is php class that can easily handle routes in a PHP application, supports multiple callbacks, redirects and send responses to client in JSON.

Website: [Koddn Technologies](https://koddn.com)

Link to Docs: [Koddn Php Router](https://koddn.com/php-router)

Social Media Koddn:
[Facebook](https://www.facebook.com/koddn/), [Twitter](https://twitter.com/koddn), [Instagram](https://www.instagram.com/koddn.technologies/)

Social Media Developer:
[Facebook](https://www.facebook.com/HarpalSingh11/), [Twitter](https://twitter.com/Harpalsingh_11), [Instagram](https://www.instagram.com/harpal.singh11/)

# Features

* Manages Routing, get, post, put, delete, any - others
* Redirect pages
* Multiple callbacks
* Next function
* Matches paters
* Send Responses, JSON, TEXT
* Set header status
* Express like router in PHP
* Fast route in PHP
* Can be used as Auth Validator

# Install

To install with composer:

```cli
composer require koddn/php-router
```

# Usage

Get the Koddn Php Router

```php
// composer auto loader
require __DIR__ . '/vendor/autoload.php';

use KODDN\ROUTER;

// match get request
ROUTER::get('/',function($req,$res,$next){
    // ..do something 
    $res->send("Welcome to Koddn Php Router");
});

```

## Usage - method 2

Simply add src/KODDN_ROUTER.php file in your project;

```php
// composer auto loader
require __DIR__ . '/src/KODDN_ROUTER.php';

// match get request
ROUTER::get('/',function($req,$res,$next){
    // ..do something 
    $res->send("Thanks");
});

```

## Patterns

You can use patterns to match the request

* :paramName => named URL segments that are used to capture the values specified at their position in the URL, begin
  with colon
*
    * => will capture anything

In the below example we are capturing user id from the url

```php
// URL => /api/user/111

ROUTER::get('/api/user/:id',function($req,$res){
    
    $id=$req['params']['id'];
    // $req['params]=>['id'='111']
    
});
```

In the example we are capturing using

```php
// URL => /flight/india-usa

ROUTER::get('/flight/:from-:to',function($req,$res){
    
    // $req['params]=['from'=>'india','to'=>'usa'];
    $from =$req['params']['from'];
    $to = $req['params']['to'];
   
});
```

```php
// URL => /post-name-apc/123
ROUTER::get('/*/:postID',function($req,$res){
    
    // $req['params']=['postID'=>'123'];
    $postID =$req['params']['postID'];
    // do something
   
});
```

# Route Handlers

We can use multiple callback functions to handle the route

```php
ROUTER::get('/some-url', function($req,$res,$next){

    // Do something here
    echo "START" ;
    
    // call Next Callback, control goes to next callback function
    $next();
    
},function($req,$res){

   
    echo "END";  // task completed
    
});
```

Also, if we want the edited request to be referenced in next callback then use &$req as request parameter

```php
ROUTER::get('/dashboard', function(&$req,$res,$next){

    // if user authorized
    $req['userID']= "someUserID";
    
    $next();
    
},function(&$req){

   
   // now you can have use the req.userID here as well
    
});

```

# Middleware 
Sample how we can use it as middleware for authentication

Middlewares can be implemented using "use" method

```php
ROUTER::use('/user',function($req,$res,$next){
   
             ROUTER::get('/me', function ($req, $res, $next) {
                           
            // do something here
                        });
        });



// OR
ROUTER::use('/user',function($req,$res,$next){
   
        require __DIR__."/routes/user.php"; //
});

```



```php
ROUTER::post('/login', function(&$req,$res,$next){

    // do the authorize stuff here
    if(!authorize){
    $res->send("invalid");
    }
    $next();
    
},function(&$req,$res){

   
   // do something if authorized
  // grantAccessToSomething
    
});

```

# Redirect

```php
//ROUTER::redirect('/url-to-match', callbackBeforeRedirect, 'redirect to url', $replaceHeaders=false (optional), $redirectCode =301 (optional));
ROUTER::redirect('/url-to-match', function(){/*do some logs*/}, '/new-url', $replaceHeaders =false/*( boolean optional)*/, $redirectCode=301 /*(int optional)*/);
```

# Responses

With Koddn PHP router either you can manually handle responses, or you can use the built-in ones.

```php
ROUTER::post('/about', function($req,$res,$next){
 $res->send("About us");
});
```

## Send JSON data

```php
ROUTER::post('/api/user', function($req,$res,$next){
    $userData=['name'=>"Harpal Singh", 'id'=>11];
     $res->json($userData);
});
```

## Set Header Status Codes

```php
ROUTER::post('/api/user', function($req,$res,$next){
  
     $res->setStatus(404)->send('Not Found');
});
```

### Clear cookies

```php
ROUTER::post('/api/user', function($req,$res,$next){
     // clear all cookies
     $res->clearCookies();
     
     // clear specific cookies
     $res->clearCookies('nameOfCookie');
     
     // do something
     
});
```

### Clear cookies

```php
ROUTER::post('/api/user', function($req,$res,$next){
    // clear Sessions
     $res->clearSession();
     
     // do something
     
});
```

### End request

It is similar to die();

```php
ROUTER::post('/api/user', function($req,$res,$next){
    // clear Sessions
     $res->end();
      // do something
     
});
```

### Redirect using Response

```php
//ROUTER::redirect('redirect to url', $replaceHeaders=false (optional), $redirectCode =301 (optional));
ROUTER::post('/api/user', function($req,$res,$next){
    // clear Sessions
     $res->redirect('/new-url',$replaceHeaders=false /*(optional)*/, $redirectCode =301  /*(optional)*/);
      // do something
     
});
```

# ALL Functions

```php
ROUTER::any('/url-to-match',function(&$req,$res,$next){}/*, function(&$req,$res,$next){}*/);
ROUTER::post('/url-to-match',function(&$req,$res,$next){}/*, function(&$req,$res,$next){}*/);
ROUTER::get('/url-to-match',function(&$req,$res,$next){}/*, function(&$req,$res,$next){}*/);
ROUTER::put('/url-to-match',function(&$req,$res,$next){}/*, function(&$req,$res,$next){}*/);
ROUTER::delete('/url-to-match',function(&$req,$res,$next){}/*, function(&$req,$res,$next){}*/);
ROUTER::redirect('/url-to-match', function(){/*do some logs*/}, '/new-url', $replaceHeaders =false/*( boolean optional)*/, $redirectCode=301 /*(int optional)*/);
```

```php

ROUTER::post('/url-to-match',function(&$req,$res,$next){
//
//$req = ['$fullUrl' => $fullUrl, 'url' => $url, 'path' => $path, 'params' => $params, 'rPath' => $rPath];

});
```

```php
ROUTER::post('/url-to-match',function(&$req,$res,$next){
//send
$res->send('Some Text');
//json
$res->json(['id'=>11,'name'=>'Harpal Singh']);
//redirect using response
$res->redirect('/new-url',$replaceHeaders=false /*(optional)*/, $redirectCode =301  /*(optional)*/);
// Exit
$res->end();
// clear all cookies
$res->clearCookies();
// clear specific cookies
$res->clearCookies();
//set Status
$res->setStatus(404)->send('Not Found');
});
```