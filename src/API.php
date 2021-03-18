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
abstract class API
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
     * Property: action
     * Linked to the action controller of the resource
     */
    protected $action;

    /**
     * Property: core_action
     * Linked to the action controller of the core components
     */
    protected $core_action;


    /**
     * Constructor of the API core. Allows CORS, assemble and pre-process data
     *
     * @param Request $where Where something interesting takes place
     *
     * @throws Exception when an unexpected header for the request method appears.
     *                   The only methods allowed here are POST, GET, PUT, DELETE and OPTIONS
     */
    public function __construct($URI, $origin)
    {
        $this->processHeaders($origin);

        $this->args = explode('/', rtrim($URI, '/'));
        $this->endpoint = array_shift($this->args);
        $this->endpoint = $this->dashesToCamelCase($this->endpoint);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }

        $this->method = $this->curateMethod();

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
        header("Access-Control-Allow-Origin: *");
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

    function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
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
     * @return JSON object
     */
    public function processAPI()
    {
        if ((int)method_exists($this, $this->endpoint) > 0) {
            return $this->response($this->{$this->endpoint}($this->args), $this->response_code);
        }
        return $this->response("No Endpoint: $this->endpoint", 404);
    }

    /**
     * Executes a standard processing of the allowed http methods based on the GenericController structure.
     *
     * @return OBJECT processed object from the controller
     *
     */
    protected function executesCall($coreAction = false)
    {
        if ($coreAction) {
            $this->action = $this->core_action;
        }

        $this->action->setFormEndpoint(UriUtils::processUri($this->endpoint, $this->args, $this->verb));

        switch ($this->method) {
            case 'POST':
                $this->action->doPost($this->args, $this->verb);
                $this->response_code = $this->action->response['code'];
                return $this->action->response;
                break;
            case 'GET':
                $this->action->doGet($this->args, $this->verb);
                $this->addHeader($this->action->pagination_link);
                $this->response_code = $this->action->response['code'];
                return $this->action->response;
                break;
            case 'PUT':
                parse_str($this->file, $_POST);
                $this->action->doPut($this->args, $this->verb, $this->file);
                $this->response_code = $this->action->response['code'];
                return $this->action->response;
                break;
            case 'DELETE':
                $this->action->doDelete($this->args, $this->verb);
                $this->response_code = $this->action->response['code'];
                return $this->action->response;
                break;
            case 'OPTIONS':
                exit(0);
                break;
            default:
                $this->response_code = 405;
                return "Invalid Method";
                break;
        }
    }

    public function addHeader($header)
    {
        $this->headers[] = $header;
    }
}
