<?php

namespace Controller;

use Geekcow\Dbcore\Entity;
use Geekcow\FonyCore\Controller\GenericController;
use Geekcow\FonyCore\CoreModel\ApiForm;
use Geekcow\FonyCore\Utils\AuthenticatorInterface;
use Geekcow\FonyCore\Utils\ConfigurationUtils;
use Geekcow\FonyCore\Utils\Oauth\Oauth;
use Geekcow\FonyCore\Utils\SessionUtils;
use Mockery;
use PHPUnit\Framework\TestCase;

class GenericControllerTest extends TestCase
{
    /**
     * @before
     */
    public function init()
    {
        if (!defined('MY_DOC_ROOT')) {
            define('MY_DOC_ROOT', __DIR__);
        }
        if (!defined('MY_ASSET_ROOT')) {
            define('MY_ASSET_ROOT', __DIR__);
        }
        $mockApiForm = Mockery::mock(ApiForm::class);
        $mockApiForm->shouldReceive('getCount')
            ->once()
            ->andReturn(0);
        $config = ConfigurationUtils::getInstance("../resources/config.ini", false, $mockApiForm);
        $mockAuthentication = Mockery::mock(Oauth::class);
        $mockAuthentication->shouldReceive('validateBearerToken')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);
        $mockAuthentication->shouldReceive('getUsername')
            ->once()
            ->andReturn('test');
        $mockAuthentication->shouldReceive('getScopes')
            ->once()
            ->andReturn('test');
        $mockAuthentication->shouldReceive('getScopeLevel')
            ->once()
            ->andReturn(1);
        $session = SessionUtils::getInstance($mockAuthentication);
    }

    public function testDoGET()
    {
        $columnsToExpect = ['value' => 'test', 'id' => 123];
        $_SERVER['HTTP_Authorization'] = "test";
        $expectedResult = array("code"=>200,"Mockery_2_Geekcow_Dbcore_Entity"=>array($columnsToExpect));
        $mockEntityResult = Mockery::mock(Entity::class);
        $mockEntityResult->columns = $columnsToExpect;

        $mockEntity = Mockery::mock(Entity::class);
        $mockEntity->shouldReceive('get_mapping')
            ->once()
            ->andReturn(array('id' => ['type' => 'int', 'pk' => true, 'incremental' => true]));
        $mockEntity->shouldReceive('assembly_search')
            ->once()
            ->andReturn("");
        $mockEntity->shouldReceive('set_pagination')
            ->once();
        $mockEntity->shouldReceive('set_paging')
            ->once();
        $mockEntity->shouldReceive('paginate')
            ->once();
        $mockEntity->shouldReceive('fetch')
            ->once()
            ->andReturn(array($mockEntityResult));

        $controller = new GenericController();
        $controller->setModel($mockEntity);
        $controller->setAllowedRoles(array("test"));
        $controller->doGET(array(),"");

        $this->assertEquals($expectedResult, $controller->response);

    }

    public function testDoPOST()
    {
        $this->markTestSkipped("TBD");
    }

    public function testDoPUT()
    {
        $this->markTestSkipped("TBD");
    }

    public function testDoDELETE()
    {
        $this->markTestSkipped("TBD");
    }
}
