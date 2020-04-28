<?php
namespace Geekcow\FonyCore\Installer\User;
use Geekcow\FonyCore\CoreModel\ApiForm;
use Geekcow\FonyCore\CoreModel\ApiScope;
use Geekcow\FonyCore\CoreModel\ApiUserType;
use Geekcow\FonyCore\CoreModel\ApiFieldType;
use Geekcow\FonyCore\CoreModel\ApiAssetType;

class DatabaseMaintenance{
  private $user_type;
  private const DEFAULT_USER_TYPES = array(
    "1" => array("name"=>"administrator","priority"=>10,"scope"=>"administrator"),
    "2" => array("name"=>"user","priority"=>1,"scope"=>"user"));

  private $scope;
  private const DEFAULT_SCOPES = array(
    "administrator" => array("level"=>1,"priority"=>1),
    "visitor" => array("level"=>1,"priority"=>1),
    "user" => array("level"=>2,"priority"=>2));

  private $field_type;
  private const DEFAULT_FIELD_TYPES = array(
    "1" => array("name" => 'string', "regex" => '/^.{1,1500}$/'),
    "2" => array("name" => 'integer', "regex" => '/^[0-9]+$/'),
    "3" => array("name" => 'float', "regex" => '!\\d+(?:\\.\\d+)?!'),
    "4" => array("name" => 'email', "regex" => '/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+\\.[a-zA-Z]{2,4}/'),
    "5" => array("name" => 'password', "regex" => '/^([0-9A-Za-z@.]{4,14})$/'),
    "6" => array("name" => 'url', "regex" => '#((http|https|ftp)://(\\S*?\\.\\S*?))(\\s|\\;|\\)|\\]|\\[|\\{|\\}|,|\\"|\'\'|:|\\<|$|\\.\\s)#ie'),
    "7" => array("name" => 'MD5', "regex" => '/^[a-f0-9]{32}$/i'),
    "8" => array("name" => 'username', "regex" => '/^[a-z0-9_-]{3,16}$/'),
    "9" => array("name" => 'boolean', "regex" => '/^[1|0]$/'),
    "10" => array("name" => 'date', "regex" => '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/'),
    "11" => array("name" => 'string and empty', "regex" => '/^$|^.{1,1500}$/')
  );

  private $form;
  private const DEFAULT_FORMS = array(
    "1" => array("endpoint" => 'login', "field" => 'username', "id_type" => 4, "sample" => '', "internal" => 0, "required" => 1, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "2" => array("endpoint" => 'login', "field" => 'password', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 1, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "3" => array("endpoint" => 'v1/user', "field" => 'name', "id_type" => 1, "sample" => '',"internal" =>  0, "required" => 1, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "4" => array("endpoint" => 'v1/user', "field" => 'lastname', "id_type" => 11, "sample" => '', "internal" => 0, "required" => 1, "blank" => 0, "scopes" => '', "method" => 'POST'),
    "5" => array("endpoint" => 'v1/user', "field" => 'type', "id_type" => 2, "sample" => '', "internal" => 0, "required" => 1, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "6" => array("endpoint" => 'v1/user', "field" => 'email', "id_type" => 4, "sample" => '', "internal" => 0, "required" => 1, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "7" => array("endpoint" => 'v1/user', "field" => 'phone', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 0, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "8" => array("endpoint" => 'v1/user', "field" => 'password', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 0, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "9" => array("endpoint" => 'v1/user', "field" => 'fbid', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 0, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "10" => array("endpoint" => 'v1/user', "field" => 'googleid', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 0, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "11" => array("endpoint" => 'v1/user', "field" => 'is_developer', "id_type" => 9, "sample" => '1 or 0', "internal" => 0, "required" => 0, "blank" => 1, "scopes" => '', "method" => 'POST'),
    "12" => array("endpoint" => 'v1/user/:id', "field" => 'name', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 1, "blank" => 1, "sample" => '', "scopes" => '', "method" => 'PUT'),
    "13" => array("endpoint" => 'v1/user/:id', "field" => 'lastname', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 1, "blank" => 1, "scopes" => '', "method" => 'PUT'),
    "14" => array("endpoint" => 'v1/user/:id', "field" => 'phone', "id_type" => 1, "sample" => '', "internal" => 0, "required" => 0, "blank" => 1, "scopes" => '', "method" => 'PUT'),
    "15" => array("endpoint" => 'v1/user/:id/password', "field" => 'old_password', "id_type" => 5, "sample" => '', "internal" => 0, "required" => 0, "blank" => 1, "scopes" => '', "method" => 'PUT'),
    "16" => array("endpoint" => 'v1/user/:id/password', "field" => 'password', "id_type" => 5, "sample" => '', "internal" => 0, "required" => 0, "blank" => 1, "scopes" => '', "method" => 'PUT'),

  );

  private $asset_type;
  private const DEFAULT_ASSET_TYPES = array(
    "1" => array("name" => 'JPG Image File', "format" => '.jpg', "max_size" => 5000000, "max_dimensions" => '300|300', "mime" => 'image/jpeg|image/pjpeg|', "type" => 'image'),
    "2" => array("name" => 'MP3 Audio File', "format" => '.mp3', "max_size" => 50000000, "max_dimensions" => '60', "mime" => 'audio/mpeg|audio/x-mpeg|audio/mp3|audio/x-mp3|audio/mpeg3|audio/x-mpeg3|audio/mpg|audio/x-mpg|audio/x-mpegaudio|', "type" => 'audio'),
    "4" => array("name" => 'WAV Audio File', "format" => '.wav', "max_size" => 50000000, "max_dimensions" => '60', "mime" => 'audio/wav|audio/x-wav', "type" => 'audio'),
    "3" => array("name" => 'OGG Audio File', "format" => '.ogg', "max_size" => 50000000, "max_dimensions" => '60', "mime" => 'audio/ogg|application/ogg', "type" => 'audio'),
    "5" => array("name" => 'PNG Image Type', "format" => '.png', "max_size" => 5000000, "max_dimensions" => '300|300', "mime" => 'image/png', "type" => 'image'),
    "6" => array("name" => 'BMP Image Type', "format" => '.bmp', "max_size" => 5000000, "max_dimensions" => '300|300', "mime" => 'image/bmp|image/x-windows-bmp', "type" => 'image'),
    "7" => array("name" => 'GIF Image Type', "format" => '.gif', "max_size" => 5000000, "max_dimensions" => '300|300', "mime" => 'image/gif', "type" => 'image')
  );

  public function __construct(){
    $this->user_type = new ApiUserType();
    $this->scope = new ApiScope();
    $this->field_type = new ApiFieldType();
    $this->form = new ApiForm();
    $this->asset_type = new ApiAssetType();
  }

  public function create(){
    $this->createUserTypes();
    echo '.';
    $this->createFieldTypes();
    echo '.';
    $this->createForms();
    echo '.';
    $this->createAssetTypes();
    echo '.';
    $this->createScopes();
    echo '.';
  }

  private function createUserTypes(){
    foreach (self::DEFAULT_USER_TYPES as $key => $value) {
      $this->user_type = new ApiUserType();
      foreach ($value as $k => $v) {
        $this->user_type->columns[$k] = $v;
      }
      $this->user_type->insert();
    }
  }

  private function createFieldTypes(){
    foreach (self::DEFAULT_FIELD_TYPES as $key => $value) {
      $this->field_type = new ApiFieldType();
      $this->field_type->columns['id'] = $key;
      foreach ($value as $k => $v) {
        $this->field_type->columns[$k] = $v;
      }
      $this->field_type->insert();
    }
  }

  private function createForms(){
    foreach (self::DEFAULT_FORMS as $key => $value) {
      $this->form = new ApiForm();
      $this->form->columns['id'] = $key;
      foreach ($value as $k => $v) {
        $this->form->columns[$k] = $v;
      }
      $this->form->insert();
    }
  }

  private function createAssetTypes(){
    foreach (self::DEFAULT_ASSET_TYPES as $key => $value) {
      $this->form = new ApiAssetType();
      $this->form->columns['id'] = $key;
      foreach ($value as $k => $v) {
        $this->form->columns[$k] = $v;
      }
      $this->form->insert();
    }
  }

  private function createScopes(){
    foreach (self::DEFAULT_SCOPES as $key => $value) {
      $this->scope = new ApiScope();
      $this->scope->columns['name'] = $key;
      foreach ($value as $k => $v) {
        $this->scope->columns[$k] = $v;
      }
      $this->scope->insert();
    }
  }
}
?>
