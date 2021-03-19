<?php

/* Configuration Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Configuration support for unified config file handling
 */

namespace Geekcow\FonyCore\Utils;

use Geekcow\FonyCore\FonyApi;
use Geekcow\FonyCore\CoreModel\ApiForm;

/**
 * Class ConfigurationUtils
 * @package Geekcow\FonyCore\Utils
 */
class ConfigurationUtils
{
    // Hold the class instance.
    /**
     * @var null
     */
    private static $instance = null;

    /**
     * @var mixed|string
     */
    private $app_name;
    /**
     * @var mixed
     */
    private $app_secret;
    //default client that allows login
    /**
     * @var mixed
     */
    private $user_client;
    /**
     * @var mixed
     */
    private $user_secret;
    /**
     * @var mixed
     */
    private $file_path;
    /**
     * @var mixed
     */
    private $file_url;
    /**
     * @var mixed
     */
    private $site_url;
    /**
     * @var mixed|string
     */
    private $filename;
    //authentication
    /**
     * @var mixed
     */
    private $auth_method;
    /**
     * @var mixed|string
     */
    private $auth_url;
    /**
     * @var mixed|string
     */
    private $auth_refresh;
    /**
     * @var mixed|string
     */
    private $auth_validate;
    //other custom fields
    /**
     * @var array
     */
    private $fieldsMap;

    /**
     * @var ApiForm
     */
    private $apiForm;

    /**
     * The constructor is private
     * to prevent initiation with outer code.
     *
     * ConfigurationUtils constructor.
     * @param string $configfile
     */
    private function __construct($configfile = MY_DOC_ROOT . "/src/config/config.ini")
    {
        if (($config = @parse_ini_file($configfile)) == false) {
            $this->app_name = "NO CONFIG FILE FOUND";
        } else {
            $this->app_name = $config['fony.app_name'];
            $this->app_secret = $config['fony.app_secret'];
            $this->user_client = $config['fony.user_client'];
            $this->user_secret = $config['fony.user_secret'];
            $this->file_path = $config['fony.file_path'];
            $this->file_url = $config['fony.file_url'];
            $this->site_url = $config['fony.site_url'];
            $this->auth_method = $config['fony.auth_method'];
            if ($this->auth_method != 'self') {
                $this->auth_url = $config['fony.auth_url'];
                $this->auth_refresh = $config['fony.auth_refresh'];
                $this->auth_validate = $config['fony.auth_validate'];
            } else {
                $this->auth_url = "";
                $this->auth_refresh = "";
                $this->auth_validate = "";
            }
        }

        $this->filename = $configfile;
        $this->fieldsMap = array();
    }

    public function enableForm(ApiForm $form): ConfigurationUtils
    {
        $this->apiForm = $form;
        return $this;
    }

    /**
     * @return ApiForm
     */
    public function getApiForm(): ApiForm
    {
        return $this->apiForm;
    }

    /**
     * @return mixed
     */
    public function getFileUrl()
    {
        return $this->file_url;
    }

    /**
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

    /**
     * @return mixed
     */
    public function getSiteUrl()
    {
        return $this->site_url;
    }

    /**
     * @return mixed|string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return mixed
     */
    public function getAppSecret()
    {
        return $this->app_secret;
    }

    /**
     * @return mixed|string
     */
    public function getAppName()
    {
        return $this->app_name;
    }

    /**
     * @return mixed
     */
    public function getUserClient()
    {
        return $this->user_client;
    }

    /**
     * @return mixed
     */
    public function getUserSecret()
    {
        return $this->user_secret;
    }

    /**
     * @return mixed
     */
    public function getAuthenticationMethod()
    {
        return $this->auth_method;
    }

    /**
     * @return mixed|string
     */
    public function getAuthenticationUrl()
    {
        return $this->auth_url;
    }

    /**
     * @return mixed|string
     */
    public function getAuthenticationValidateTokenEndpoint()
    {
        return $this->auth_validate;
    }

    /**
     * @return mixed|string
     */
    public function getAuthenticationRefreshTokenEndpoint()
    {
        return $this->auth_refresh;
    }

    /**
     * @param array $parameters
     */
    public function fromArray($parameters = array())
    {
        $this->fieldsMap = $parameters;
    }

    /**
     * @param $path
     */
    public function exportToFile($path)
    {
        if (!isset($this->fieldsMap['fony'])) {
            $this->fieldsMap['fony'] = array();
        }
        if (!isset($this->fieldsMap['fony']['fony.app_name'])) {
            $this->fieldsMap['fony']['fony.app_name'] = $this->app_name;
        } else {
            $this->app_name = $this->fieldsMap['fony']['fony.app_name'];
        }
        if (!isset($this->fieldsMap['fony']['fony.app_secret'])) {
            $this->fieldsMap['fony']['fony.app_secret'] = $this->app_secret;
        } else {
            $this->app_secret = $this->fieldsMap['fony']['fony.app_secret'];
        }
        if (!isset($this->fieldsMap['fony']['fony.user_client'])) {
            $this->fieldsMap['fony']['fony.user_client'] = $this->user_client;
        } else {
            $this->user_client = $this->fieldsMap['fony']['fony.user_client'];
        }
        if (!isset($this->fieldsMap['fony']['fony.user_secret'])) {
            $this->fieldsMap['fony']['fony.user_secret'] = $this->user_secret;
        } else {
            $this->user_secret = $this->fieldsMap['fony']['fony.user_secret'];
        }
        if (!isset($this->fieldsMap['fony']['fony.file_url'])) {
            $this->fieldsMap['fony']['fony.file_url'] = $this->file_url;
        } else {
            $this->file_url = $this->fieldsMap['fony']['fony.file_url'];
        }
        if (!isset($this->fieldsMap['fony']['fony.file_path'])) {
            $this->fieldsMap['fony']['fony.file_path'] = $this->file_path;
        } else {
            $this->file_path = $this->fieldsMap['fony']['fony.file_path'];
        }
        if (!isset($this->fieldsMap['fony']['fony.site_url'])) {
            $this->fieldsMap['fony']['fony.site_url'] = $this->site_url;
        } else {
            $this->site_url = $this->fieldsMap['fony']['fony.site_url'];
        }
        if (!isset($this->fieldsMap['fony']['fony.auth_method'])) {
            $this->fieldsMap['fony']['fony.auth_method'] = $this->auth_method;
        } else {
            $this->auth_method = $this->fieldsMap['fony']['fony.auth_method'];
        }
        if (!isset($this->fieldsMap['fony']['fony.auth_url'])) {
            $this->fieldsMap['fony']['fony.auth_url'] = $this->auth_url;
        } else {
            $this->auth_url = $this->fieldsMap['fony']['fony.auth_url'];
        }
        if (!isset($this->fieldsMap['fony']['fony.auth_validate'])) {
            $this->fieldsMap['fony']['fony.auth_validate'] = $this->auth_validate;
        } else {
            $this->auth_validate = $this->fieldsMap['fony']['fony.auth_validate'];
        }
        if (!isset($this->fieldsMap['fony']['fony.auth_refresh'])) {
            $this->fieldsMap['fony']['fony.auth_refresh'] = $this->auth_refresh;
        } else {
            $this->auth_refresh = $this->fieldsMap['fony']['fony.auth_refresh'];
        }

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        $fp = fopen($path, "wb");
        fwrite($fp, "");
        fclose($fp);

        $this->writeIniFile($this->fieldsMap, $path, true);
    }

    /**
     * @param $assoc_arr
     * @param $path
     * @param $has_sections
     * @return bool
     */
    private function writeIniFile($assoc_arr, $path, $has_sections)
    {
        $content = '';

        if (!$handle = fopen($path, 'w')) {
            return false;
        }

        $this->writeIniFileR($content, $assoc_arr, $has_sections);

        if (!fwrite($handle, $content)) {
            return false;
        }

        fclose($handle);
        return true;
    }

    /**
     * @param $content
     * @param $assoc_arr
     * @param $has_sections
     */
    private function writeIniFileR(&$content, $assoc_arr, $has_sections)
    {
        foreach ($assoc_arr as $key => $val) {
            if (is_array($val)) {
                if ($has_sections) {
                    $content .= "[$key]\n";
                    $this->writeIniFileR($content, $val, false);
                } else {
                    foreach ($val as $iKey => $iVal) {
                        if (is_int($iKey)) {
                            $content .= $key . "[] = $iVal\n";
                        } else {
                            $content .= $key . "[$iKey] = $iVal\n";
                        }
                    }
                }
            } else {
                $content .= "$key = $val\n";
            }
        }
    }

    /**
     * The object is created from within the class itself
     * only if the class has no instance.
     *
     * @param string $configfile
     * @return ConfigurationUtils|null
     */
    public static function getInstance($configfile = MY_DOC_ROOT . "/src/config/config.ini", $form = true, $formEntity = null)
    {
        if (self::$instance == null) {
            if ($form) {
                self::$instance = (new ConfigurationUtils($configfile))->enableForm(new ApiForm());
            } else {
                self::$instance = (new ConfigurationUtils($configfile))->enableForm($formEntity);
            }
        }

        return self::$instance;
    }
}
