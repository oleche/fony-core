<?php

/* API core backbone
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the main class where each http request gets processed and replied
 */

namespace Geekcow\FonyCore;

use Exception;
use Geekcow\FonyCore\Utils\UriUtils;

/**
 * CORE API Class
 *
 * Orchestrates the functionality of the API
 *
 */
class FonyApi
{
    /**
     * Property: response_code
     * The response of the api call, established by the controller result
     */
    protected $response_code = 202;

    /**
     * Property: headers
     * The response of the api call, established by the controller result
     */
    protected $headers = array();

    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';
    /**
     * Property: endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $endpoint = '';
    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = '';
    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>
     */
    protected $args = array();
    /**
     * Property: file
     * Stores the input of the PUT request
     */
    protected $file = null;


    /**
     * @var FonyRouter
     */
    protected $router;
    /**
     * @var array|string
     */
    protected $request;

    /**
     * Constructor of the API core. Allows CORS, assemble and pre-process data
     *
     * @param string $URI
     * @param FonyRouter $router
     * @param null $origin
     * @throws Exception when an unexpected header for the request method appears.
     *                   The only methods allowed here are POST, GET, PUT, DELETE and OPTIONS
     */
    public function __construct(string $URI, FonyRouter $router, $origin = null)
    {
        $this->processHeaders($origin);

        $this->args = explode('/', rtrim($URI, '/'));
        $this->endpoint = array_shift($this->args);
        $this->endpoint = $this->dashesToCamelCase($this->endpoint);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }

        $this->method = $this->curateMethod();

        $this->router = $router;

        $this->curateRequest();
    }

    private function processHeaders($origin)
    {
        if (array_key_exists('HTTP_ACCESS_CONTROL_REQUEST_HEADERS', $_SERVER)) {
            header(
                'Access-Control-Allow-Headers: '
                . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']
            );
        } else {
            header('Access-Control-Allow-Headers: *');
        }
        if (is_null($origin)) {
            header("Access-Control-Allow-Origin: *");
        } else {
            header("Access-Control-Allow-Origin: $origin");
        }
        header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
        header("Content-Type: application/json; charset=utf-8");
        header("Access-Control-Expose-Headers: Location, Link");
    }

    private function curateMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $method = 'DELETE';
            } else {
                if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                    $method = 'PUT';
                } else {
                    throw new Exception("Unexpected Header");
                }
            }
        }

        return $method;
    }

    private function curateRequest()
    {
        switch ($this->method) {
            case 'POST':
                $this->request = $this->cleanInputs($_POST);
                break;
            case 'GET':
            case 'OPTIONS':
                $this->request = $this->cleanInputs($_GET);
                break;
            case 'DELETE':
            case 'PUT':
                $this->request = $this->cleanInputs($_GET);
                $this->file = file_get_contents("php://input", FILE_USE_INCLUDE_PATH);
                break;
            default:
                $this->response('Invalid Method', 405);
                break;
        }
    }

    private function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    private function cleanInputs($data)
    {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function response($data, $status = 200)
    {
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS");

        foreach ($this->headers as $header) {
            header($header);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            202 => 'OK',
            304 => 'Not Modified',
            401 => 'Unauthorized',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            415 => 'Unsupported Media Type',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }

    public static function returnMessage($message, $type)
    {
        $response = array();
        $response['type'] = $type;
        $response['message'] = $message;
        return $response;
    }

    /**
     * Executes the defined method registered in the child object and returns its response
     *
     * @return false|JSON|string
     */
    public function processAPI()
    {
        $this->router->method = $this->method;
        $this->router->args = $this->args;
        $this->router->verb = $this->verb;
        $this->router->file = $this->file;

        $this->router->prestageEndpoints($this->endpoint, $this->request);

        if (
            ($this->endpoint != "prestageEndpoints") &&
            (int)method_exists($this->router, $this->endpoint) > 0 &&
            is_callable(array($this->router, $this->endpoint))
        ) {
            $this->headers = $this->router->headers;
            return $this->response($this->router->{$this->endpoint}($this->args), $this->router->getResponseCode());
        }
        return $this->response("No Endpoint: $this->endpoint", 404);
    }
}
