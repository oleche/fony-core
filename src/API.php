<?php
/* API core backbone
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the main class where each http request gets processed and replied
 */
namespace Geekcow\FonyCore;

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
  protected $args = Array();
  /**
  * Property: file
  * Stores the input of the PUT request
  */
  protected $file = Null;

  /**
  * Property: action
  * Linked to the action controller of the resource
  */
  protected $action;


  /**
   * Constructor: __construct
   * Allow for CORS, assemble and pre-process the data
   */
  public function __construct($request) {
    $this->_processHeaders();

    $this->args = explode('/', rtrim($request, '/'));
    $this->endpoint = array_shift($this->args);
    if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
      $this->verb = array_shift($this->args);
    }

    $this->method = $this->_curateMethod();

    $this->_curateRequest();
  }

  public function processAPI() {
    if ((int)method_exists($this, $this->endpoint) > 0) {
      return $this->_response($this->{$this->endpoint}($this->args), $this->response_code);
    }
    return $this->_response("No Endpoint: $this->endpoint", 404);
  }

	public function add_header($header){
		$this->headers[] = $header;
	}

	public static function return_message($message, $type){
		$response = array();
		$response['type'] = $type;
		$response['message'] = $message;
		return $response;
	}

  private function _response($data, $status = 200) {
    header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
	  header("Access-Control-Allow-Origin: *");
	  header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS");

		foreach ($this->headers as $header) {
			header($header);
		}

    return json_encode($data,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  }

  private function _processHeaders(){
    if(array_key_exists('HTTP_ACCESS_CONTROL_REQUEST_HEADERS', $_SERVER)) {
      header('Access-Control-Allow-Headers: '
             . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
    } else {
      header('Access-Control-Allow-Headers: *');
    }
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Expose-Headers: Location, Link");
  }

  private function _curateRequest(){
    switch($this->method) {
      case 'POST':
        $this->request = $this->_cleanInputs($_POST);
        break;
      case 'GET':
      case 'OPTIONS':
        $this->request = $this->_cleanInputs($_GET);
        break;
      case 'DELETE':
      case 'PUT':
        $this->request = $this->_cleanInputs($_GET);
        $this->file = file_get_contents("php://input", FILE_USE_INCLUDE_PATH);
        break;
      default:
        $this->_response('Invalid Method', 405);
        break;
      }
  }

  private function _curateMethod(){
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
      if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
        $method = 'DELETE';
      } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
        $method = 'PUT';
      } else {
        throw new Exception("Unexpected Header");
      }
    }

    return $method;
  }

  private function _cleanInputs($data) {
    $clean_input = Array();
    if (is_array($data)) {
      foreach ($data as $k => $v) {
        $clean_input[$k] = $this->_cleanInputs($v);
      }
    } else {
      $clean_input = trim(strip_tags($data));
    }
    return $clean_input;
  }

  private function _requestStatus($code) {
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
    return ($status[$code])?$status[$code]:$status[500];
  }

  protected function doDerivedCall(){
    switch ($this->method) {
      case 'PUT':
        parse_str($this->file,$_POST);
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

  protected function doRegulaCall(){
    switch ($this->method) {
      case 'POST':
        $this->action->doPost($this->args, $this->verb);
        $this->response_code = $this->action->response['code'];
        return $this->action->response;
        break;
      case 'GET':
        $this->action->doGet($this->args, $this->verb);
        $this->add_header($this->action->pagination_link);
        $this->response_code = $this->action->response['code'];
        return $this->action->response;
        break;
      case 'PUT':
        parse_str($this->file,$_POST);
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
}

?>
