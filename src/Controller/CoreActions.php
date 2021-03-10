<?php

/* API user controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: User controller
 */

namespace Geekcow\FonyCore\Controller;

class CoreActions
{
    public $response;
    public $pagination_link;
    protected $session;
    protected $allowed_roles;
    protected $file;

    //internalDB
    private $api_form;

    public function __construct()
    {
        $this->api_form = new ApiForm();
        $this->response = array();
        $this->pagination_link = null;
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
            $this->response['request'] = $_POST;
            $this->response['message'] = 'Fields definition error';
            $this->response['code'] = 500;
            return false;
        }
        return $rvalue;
    }
}
