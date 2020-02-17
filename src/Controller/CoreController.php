<?php
/* API core controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the Core class to be implemented in any controller
 */
namespace Geekcow\FonyCore\Controller;

use Geekcow\FonyCore\CoreModel\ApiForm;

abstract class CoreController
{
	protected $ipp;
	protected $app_secret;
	protected $site_url;

	protected $request;

	private $connection;

  //controller vars
  public $pagination_link = "";
  public $page;
  public $per_page;

	//response information
	public $err;
	public $response;

	//internalDB
	private $api_form;

	protected $file_url;

  public function __construct($configfile = MY_DOC_ROOT . "/core/config.ini"){

		$config = parse_ini_file($configfile);

		$this->app_secret = $config['app_secret'];

		$this->ipp = $config['ipp'];
		$this->file_url = $config['file_url'];
		$this->site_url = $config['site_url'];

		$this->api_form = new ApiForm();
	}

	public function setRequest($request){
		$this->request = $request;
	}

	/**
   * Validates the existence of a file.
   *
   * @return BOOLEAN is the file present or not
   *
   */
	protected function validate_upload($file){
		if (is_null($file)){
			$this->response['message'][] = "File unavailable";
			$this->response['code'] = 422;
			return false;
		}else{
			return true;
		}
	}

	/**
   * Validates the request based on the validated fields in the database.
   *
   * @return BOOLEAN the request complies with the validation
   *
   */
	protected function validate_fields($fields, $endpoint, $method){
		$available = array();
		$rvalue = true;
		$this->response['message'] = array();
		if (is_array($fields)){
			foreach ($fields as $k => $field) {
				$available[] = $k;
			}
		}
		$q_list = $this->api_form->fetch(" endpoint LIKE '$endpoint' AND method LIKE '$method' ");
		if (count($q_list) > 0){
      $i = 0;
			foreach ($q_list as $q_item) {
				$i++;
				if (in_array($q_item->columns['field'], $available)){
					//regex validation
					if ( !preg_match( $q_item->columns['id_type']['regex'], $fields[$q_item->columns['field']] ) ) {
						$rvalue = false;
						$message = array();
						$message['field'] = $q_item->columns['field'];
						$message['message'] = 'Do not match validation type: '.$q_item->columns['id_type']['name'];
						$message['format'] = $q_item->columns['id_type']['regex'];
						$this->response['message'][] = $message;
						$this->response['code'] = 422;
					}
					//empty validation
					if ($q_item->columns['blank']){
						if (trim($fields[$q_item->columns['field']]) == ''){
							$rvalue = false;
							$message = array();
							$message['field'] = $q_item->columns['field'];
							$message['message'] = 'Is empty';
							$this->response['message'][] = $message;
							$this->response['code'] = 422;
						}
					}
				}else{
					if ($q_item->columns['required']){
						$rvalue = false;
						$message = array();
						$message['field'] = $q_item->columns['field'];
						$message['message'] = 'Is required';
						$this->response['message'][] = $message;
						$this->response['code'] = 422;
					}
				}
			}
		}else{
			$this->response['request'] = $_POST;
			$this->response['message'] = 'Fields definition error';
			$this->response['code'] = 500;
			return false;
		}
		return $rvalue;
	}
}

?>
