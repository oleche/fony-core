# fony-php
Core classes for Fony PHP API Framework

## Usage
You can add fony core as a dependency of your API by declaring a base API class like the following example:

```PHP
<?php
require 'vendor/autoload.php';
define('MY_DOC_ROOT', __DIR__);
define('MY_ASSET_ROOT', __DIR__);

use {PROJECTNAMESPACE}\router;

// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
  $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
  $API = new Router($_REQUEST['request'], $_SERVER['HTTP_ORIGIN'], '{PROJECTCONFIGFILE}');
  echo $API->processAPI();
} catch (\Exception $e) {
  echo json_encode(Array('error' => $e->getMessage()));
}

?>

``` 

Your `Router` class should be an implementation of the `FonyApi` class provided here.

## How does the `Router` works
The way the Router class works comes as following
- The constructor sweeps over the endpoint used. So an action class (aka controller) can be prestaged.
- The endpoint will execute a method called the same name. ie. If the endpoint is `/api/user/:id` the endpoint we are going to execute is `user`, so a `user()` function needs to be defined.

This is an example of a `Router` class:

```PHP
<?php

namespace {PROJECTNAMESPACE};

use Geekcow\FonyCore\Controller\GenericController;
use Geekcow\FonyCore\FonyApi;
use Geekcow\FonyCore\Utils\SessionUtils;
use {PROJECTNAMESPACE}\model\TestModel;

class Router extends FonyApi
{
    public function __construct($request, $origin, $config_file)
    {
        parent::__construct($request, $origin, $config_file);

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

    //WELCOME MESSAGE
    protected function welcome()
    {
        if ($this->method == 'GET') {
            return "WELCOME TO FONY PHP";
        } else {
            return "Invalid Method";
        }
    }

    /**
     * Executes the authentication endpoint.
     *
     * @return JSON Authenticated response with token
     *
     */
    protected function authenticate()
    {
        switch ($this->method) {
            case 'POST':
                $this->action->doPost($_SERVER['HTTP_Authorization'], $_POST, $this->verb);
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
     * Executes the token validation endpoint.
     *
     * @return JSON Authenticated response with token
     *
     */
    protected function validate()
    {
        switch ($this->method) {
            case 'POST':
                $this->action->doPost($_SERVER['HTTP_Authorization'], $_POST);
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
     * Executes the user endpoint.
     *
     * @return JSON User response
     *
     */
    protected function user()
    {
        return $this->executesCall();
    }

    /**
     * Executes the client endpoint.
     *
     * @return JSON Client response
     *
     */
    protected function client()
    {
        return $this->executesCall();
    }

    /**
     * Executes the scope endpoint.
     *
     * @return JSON Scope response
     *
     */
    protected function scope()
    {
        return $this->executesCall();
    }

}

```  

## Session handling
By default, Fony uses Oauth2 authentication, so it relies on the configuration file required by installing Fony:
- List of parameters TBD
 
Alternatively you can define your own authentication mechanism (like the [fony-auth]() project), where you can create an Authentication class as an implementation of the `AuthenticatorInterface` and initialize it in the Router constructor:
```PHP
    $sessionInstance = SessionUtils::getInstance(new CustomAuthenticatorClass());
```