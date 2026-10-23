<?php

/* API core backbone
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the main class where each http request gets processed and replied
 */

namespace Geekcow\FonyCore;

use Exception;
use Geekcow\FonyCore\Utils\BinaryResponse;
use Geekcow\FonyCore\Utils\RequestUtils;
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
                $this->addBodyInputs();
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
            $str = lcfirst($str);
        }

        return $str;
    }

    private function cleanInputs($data)
    {
        return RequestUtils::cleanInputs($data);
    }

    /**
     * PHP only parses POST bodies: the fields of PUT/DELETE bodies (JSON or url-encoded) are added over the
     * query string ones. JSON values are kept as sent.
     */
    private function addBodyInputs()
    {
        $media_type = RequestUtils::mediaType();
        if (RequestUtils::isJson($media_type)) {
            $this->request = array_merge($this->request, RequestUtils::parseBody($this->file, $media_type));
        } elseif ($media_type === 'application/x-www-form-urlencoded') {
            $body = RequestUtils::parseBody($this->file, $media_type);
            $this->request = array_merge($this->request, $this->cleanInputs($body));
        }
    }

    private function response($data, $status = 200)
    {
        // Controllers that do not set a response code would otherwise produce an invalid status line
        if (is_null($status) || $status === '') {
            $status = 200;
        }
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS");

        foreach ($this->headers as $header) {
            // controllers without pagination leave an empty Link header
            if (!is_null($header) && $header !== '') {
                header($header);
            }
        }

        if ($data instanceof BinaryResponse) {
            foreach ($data->getHeaders() as $header) {
                header($header);
            }
            return $data->getContent();
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            201 => 'Created',
            202 => 'OK',
            204 => 'No Content',
            304 => 'Not Modified',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            415 => 'Unsupported Media Type',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return $status[$code] ?? $status[500];
    }

    /**
     * The code set by the router, or the one in the response when the router did not set it (endpoints that do
     * not go through FonyRouter::executesCall(), such as routers handling pages in __call())
     */
    private function responseCode($response_body)
    {
        $code = $this->router->getResponseCode();
        if ((is_null($code) || $code === '') && (is_array($response_body) || $response_body instanceof \ArrayAccess)) {
            $code = $response_body['code'] ?? null;
        }
        return $code;
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
            (method_exists($this->router, $this->endpoint) || method_exists($this->router, '__call')) &&
            is_callable(array($this->router, $this->endpoint))
        ) {
            $response_body = $this->router->{$this->endpoint}($this->args);
            $this->headers = $this->router->headers;
            return $this->response($response_body, $this->responseCode($response_body));
        }
        return $this->response("No Endpoint: $this->endpoint", 404);
    }
}
