# fony-php
Core classes for Fony PHP API Framework

## Usage
You can add fony core as a dependency of your API by declaring a base API class like the following example:

```PHP
<?php
require 'vendor/autoload.php';
define('MY_DOC_ROOT', __DIR__);
define('MY_ASSET_ROOT', __DIR__);

use Geekcow\FonyCore\FonyApi;
use {PROJECTNAMESPACE}\router;

// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
  $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
  $router = new Router('{PROJECTCONFIGFILE}');
  $API = new FonyApi($_REQUEST['request'], $router);
  // if you want to have the origin set:
  // $API = new FonyApi($_REQUEST['request'], $router, $_SERVER['HTTP_ORIGIN']);
  echo $API->processAPI();
} catch (\Exception $e) {
  echo json_encode(Array('error' => $e->getMessage()));
}

?>

``` 

Your `Router` class should be an implementation of the `FonyApi` class provided here.

## How does the `Router` works
The way the Router class works comes as following
- The constructor sweeps over the endpoint requested. So an action class (aka controller) can be prestaged.
- The endpoint will execute a method called the with same name of the request. ie. If the endpoint is `/api/user/:id` the endpoint we are going to execute is `user`, so a `user()` function needs to be defined.
- Additionally to it, if the endpoint is written with dashes, then the endpoint should be written with camel case. 

This is an example of a `Router` class:

```PHP
<?php

namespace {PROJECTNAMESPACE};

use Geekcow\FonyCore\Controller\GenericController;
use Geekcow\FonyCore\FonyRouter;
use Geekcow\FonyCore\Utils\SessionUtils;
use {PROJECTNAMESPACE}\model\TestModel;

class Router extends FonyRouter
{
    public function __construct($config_file)
    {
        parent::__construct($config_file);
    }
    
    public function prestageEndpoints($endpoint, $request){
        parent::prestageEndpoints($endpoint, $request);

        switch ($this->endpoint) {
            case 'generic-controller-endpoint':
                $this->action = new GenericController();
                $this->action->setRequest($request);
                $this->action->setModel(new TestModel());
                $this->action->setFilter(['fields', 'that', 'you', 'do not', 'want', 'to', 'show']);
                $this->setAllowedRoles(Allow::CUSTOMROLE());
                break;
            case 'generic-actionable-controller-endpoint':
                $this->action = new GenericActionController();
                $this->action->setRequest($request);
                $this->action->setModel(new TestModel());
                $this->action->setFilter(['fields', 'that', 'you', 'do not', 'want', 'to', 'show']);
                $this->setAllowedRoles(Allow::CUSTOMROLE());
                break;
        }
    }
    
    /**
     * Shows a welcome message
     *
     * @return string
     *
     */
    //WELCOME MESSAGE
    public function welcome()
    {
        if ($this->method == 'GET') {
            return "WELCOME TO FONY PHP";
        } else {
            return "Invalid Method";
        }
    }

    /**
     * Executes only a POST operation.
     *
     * @return JSON Authenticated response with token
     *
     */
    public function genericControllerEndpoint()
    {
        switch ($this->method) {
            case 'POST':
                $this->action->doPost($this->args, $this->verb);
                $this->response_code = $this->action->response['code'];
                return $this->action->response;
                break;
            case 'OPTIONS':
                exit(0);
                break;
            default:
                $this->response_code = 405;
                return "Invalid method";
                break;
        }
    }

    /**
     * Executes an endpoint using the default behavior.
     *
     * @return JSON response
     *
     */
    public function genericActionableControllerEndpoint()
    {
        return $this->executesCall();
    }
}

```  

## Session handling
By default, Fony uses Oauth2 authentication, so it relies on the configuration file required by installing Fony:
- List of parameters TBD
 
Alternatively you can define your own authentication mechanism (like the [fony-auth](https://github.com/oleche/fony-auth) project), where you can create an Authentication class as an implementation of the `AuthenticatorInterface` and initialize it in the Router constructor:
```PHP
    $sessionInstance = SessionUtils::getInstance(new CustomAuthenticatorClass());
```

## Example
Soon an example repository will be available