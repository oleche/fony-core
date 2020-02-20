<?php
namespace Geekcow\FonyCore\CoreModel;

use \Geekcow\Dbcore\Entity;

class ApiAssetType extends Entity{
  private $api_asset_type;

  public function __construct($configfile = MY_DOC_ROOT . "/src/config/config.ini"){
    $this->api_asset_type = [
        'id' => [ 'type' => 'int', 'unique' => true, 'pk' => true ],
        'name' => [ 'type' => 'string', 'length' => 200, 'postable' => true ],
        'format' => [ 'type' => 'string', 'length' => 500, 'postable' => true ],
        'max_size' => [ 'type' => 'int', 'postable' => true ],
        'max_dimensions' => [ 'type' => 'string', 'length' => 50, 'postable' => true ],
        'mime' => [ 'type' => 'string', 'length' => 600, 'postable' => true ],
        'type' => [ 'type' => 'string', 'length' => 50, 'postable' => true ]
    ];
    parent::__construct($this->api_asset_type, get_class($this), $configfile);
  }
}

?>
