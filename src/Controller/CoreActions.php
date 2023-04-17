<?php

/* API user controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: User controller
 */

namespace Geekcow\FonyCore\Controller;

use Geekcow\Dbcore\Entity;
use Geekcow\FonyCore\CoreModel\ApiForm;
use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\Utils\SessionUtils;

class CoreActions
{
    public $response;
    public $pagination_link;
    public $total_items;
    /**
     * @var SessionUtils
     */
    protected $session;
    protected $usernameKey;
    protected $allowed_roles;
    protected $file;
    protected $filter;
    protected $request;
    protected $form_endpoint;

    /**
     * @var Entity
     */
    protected $model;

    //internalDB
    private $api_form;

    public function __construct()
    {
        $this->api_form = new ApiForm();
        $this->response = array();
        $this->pagination_link = null;
        $this->filter = array();
        $this->request = array();
        $this->form_endpoint = "";
        $this->total_items = 0;
    }

    public function setSession($session)
    {
        $this->session = $session;
    }

    public function setRoles($roles)
    {
        $this->allowed_roles = $roles;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @param Entity $model
     */
    public function setModel(Entity $model): void
    {
        $this->model = $model;
    }

    /**
     * @param string $form_endpoint
     */
    public function setFormEndpoint(string $form_endpoint): void
    {
        $this->form_endpoint = $form_endpoint;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @param array $request
     */
    public function setRequest(array $request): void
    {
        $this->request = $request;
    }

    /**
     * @param mixed $usernameKey
     */
    public function setUsernameKey($usernameKey): void
    {
        $this->usernameKey = $usernameKey;
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
     * Validates the existence of a file.
     *
     * @return BOOLEAN is the file present or not
     *
     */
    protected function validateUpload($file)
    {
        if (is_null($file)) {
            $this->response['message'][] = "File unavailable";
            $this->response['code'] = 422;
            return false;
        } else {
            return true;
        }
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
        $q_list = $this->api_form->fetch(" endpoint LIKE '$endpoint' AND method LIKE '$method' ");
        if (count($q_list) > 0) {
            $i = 0;
            foreach ($q_list as $q_item) {
                $i++;
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
