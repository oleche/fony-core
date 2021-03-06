<?php

/* API core controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the Core class to be implemented in any controller
 */

namespace Geekcow\FonyCore\Controller;

use Geekcow\FonyCore\CoreModel\ApiForm;
use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

abstract class CoreController
{
    /**
     * @var array
     */
    protected $request;

    //controller vars
    public $pagination_link = "";

    //response information
    public $response;

    //internalDB
    private $api_form;

    protected $allowed_roles;
    protected $form_endpoint;

    public function __construct($with_form = false)
    {
        $config = ConfigurationUtils::getInstance();
        if (!$with_form) {
            $this->api_form = new ApiForm();
        }
    }

    /**
     * @param array $request
     */
    public function setRequest(array $request): void
    {
        $this->request = $request;
    }

    /**
     * @param ApiForm $api_form
     */
    public function setApiForm(ApiForm $api_form): void
    {
        $this->api_form = $api_form;
    }



    public function setAllowedRoles($roles)
    {
        $this->allowed_roles = $roles;
    }

    public function setFormEndpoint($endpoint)
    {
        $this->form_endpoint = $endpoint;
    }

    protected function validateScope($scope)
    {
        if (!AllowCore::isAllowed($scope, $this->allowed_roles)) {
            $this->response = AllowCore::denied($scope);
            return false;
        }
        return true;
    }

    /**
     * Validates the request based on the validated fields in the database.
     *
     * @return BOOLEAN the request complies with the validation
     *
     */
    protected function validateFields($fields, $endpoint, $method)
    {
        $available = array();
        $rvalue = true;
        $this->response['message'] = array();
        if (is_array($fields)) {
            foreach ($fields as $k => $field) {
                $available[] = $k;
            }
        }
        $q_list = (array)$this->api_form->fetch(" endpoint LIKE '$endpoint' AND method LIKE '$method' ");
        if (count($q_list) > 0) {
            $i = 0;
            foreach ($q_list as $q_item) {
                $i++;
                if (isset($q_item->columns)) {
                    if (in_array($q_item->columns['field'], $available)) {
                        //regex validation
                        if (!preg_match($q_item->columns['id_type']['regex'], $fields[$q_item->columns['field']])) {
                            $rvalue = false;
                            $message = array();
                            $message['field'] = $q_item->columns['field'];
                            $message['message'] = 'Do not match validation type: ' . $q_item->columns['id_type']['name'];
                            $message['format'] = $q_item->columns['id_type']['regex'];
                            $this->response['message'][] = $message;
                            $this->response['code'] = 422;
                        }
                        //empty validation
                        if ($q_item->columns['blank']) {
                            if (trim($fields[$q_item->columns['field']]) == '') {
                                $rvalue = false;
                                $message = array();
                                $message['field'] = $q_item->columns['field'];
                                $message['message'] = 'Is empty';
                                $this->response['message'][] = $message;
                                $this->response['code'] = 422;
                            }
                        }
                    } else {
                        if ($q_item->columns['required']) {
                            $rvalue = false;
                            $message = array();
                            $message['field'] = $q_item->columns['field'];
                            $message['message'] = 'Is required';
                            $this->response['message'][] = $message;
                            $this->response['code'] = 422;
                        }
                    }
                } else {
                    $message = array();
                    $message['message'] = 'Fields definition error';
                    $this->response['message'][] = $message;
                    $this->response['code'] = 500;
                }
            }
        } else {
//            $this->response['request'] = $fields;
//            $this->response['message'] = 'Fields definition error';
//            $this->response['code'] = 500;
//            return false;
            return true;
        }
        return $rvalue;
    }
}
