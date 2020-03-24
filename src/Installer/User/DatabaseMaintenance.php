<?php
namespace Geekcow\FonyCore\Installer\User;
use Geekcow\FonyCore\CoreModel\ApiForm;
use Geekcow\FonyCore\CoreModel\ApiScope;
use Geekcow\FonyCore\CoreModel\ApiUserType;
use Geekcow\FonyCore\CoreModel\ApiFieldType;

class DatabaseMaintenance{
  private $user_type;
  private const $DEFAULT_USER_TYPES = array(
    "1" => array("name"=>"administrator","priority"=>10,"scope"=>"administrator"),
    "2" => array("name"=>"user","priority"=>1,"scope"=>"user"));

  private $scope;
  private const $DEFAULT_SCOPES = array(
    "administrator" => array("level"=>1,"priority"=>1),
    "user" => array("level"=>2,"priority"=>2));

  private $field_type;
  private const $DEFAULT_FIELD_TYPES = array(
    "1" => array("name" => 'string', "regex" => '/^.{1,1500}$/'),
    "2" => array("name" => 'integer', "regex" => '/^[0-9]+$/'),
    "3" => array("name" => 'float', "regex" => '!\\d+(?:\\.\\d+)?!'),
    "4" => array("name" => 'email', "regex" => '/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+\\.[a-zA-Z]{2,4}/'),
    "5" => array("name" => 'password', "regex" => '/^([0-9A-Za-z@.]{4,14})$/'),
    "6" => array("name" => 'url', "regex" => '#((http|https|ftp)://(\\S*?\\.\\S*?))(\\s|\\;|\\)|\\]|\\[|\\{|\\}|,|\\"|''|:|\\<|$|\\.\\s)#ie'),
    "7" => array("name" => 'MD5', "regex" => '/^[a-f0-9]{32}$/i'),
    "8" => array("name" => 'username', "regex" => '/^[a-z0-9_-]{3,16}$/')
  );

  private $form;
  private const $DEFAULT_FORMS = array(
    "1" => ("endpoint" => 'login', "field" => 'username', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 1),
    "2" => ("endpoint" => 'login', "field" => 'password', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 1)
  );

  public function __construct(){
    $this->user_type = new ApiUserType();
    $this->scope = new ApiScope();
    $this->field_type = new ApiFieldType();
    $this->form = new ApiForm();
  }

  public function create(){

  }
}
?>
