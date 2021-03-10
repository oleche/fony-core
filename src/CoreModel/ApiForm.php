<?php

namespace Geekcow\FonyCore\CoreModel;

use Geekcow\Dbcore\Entity;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

class ApiForm extends Entity
{

    private $api_form;

    public function __construct($config_file = MY_DOC_ROOT . "/src/config/config.ini")
    {
        $config = ConfigurationUtils::getInstance($config_file);
        $this->api_form = [
            'id' => ['type' => 'int', 'pk' => true, 'incremental' => true],
            'endpoint' => ['type' => 'string', 'length' => 50],
            'field' => ['type' => 'string', 'length' => 75],
            'id_type' => ['type' => 'int', 'foreign' => array('id', new ApiFieldType())],
            'sample' => ['type' => 'string', 'length' => 350],
            'internal' => ['type' => 'boolean'],
            'required' => ['type' => 'boolean'],
            'blank' => ['type' => 'boolean'],
            'scopes' => ['type' => 'string', 'length' => 500],
            'method' => ['type' => 'string', 'length' => 10]
        ];
        parent::__construct($this->api_form, get_class($this), $config->getFilename());
    }
}
