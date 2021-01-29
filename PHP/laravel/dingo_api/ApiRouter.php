<?php
namespace core\classes;

use App;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use core\util\router\RouterHelper;

class ApiRouter extends Controller
{
    use Helpers;

    private $ns;

    const API_NS = 'core\bapi';
    function __construct($ns)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:*');
        $this->ns = $ns;
    }

    /**
     * @var string Allows early access to page action.
     */
    public static $action;

    /**
     * @var array Allows early access to page parameters.
     */
    public static $params;

    /**
     * 调试模式在.env设置API_DEBUG=true会有出错跟踪，不设置的话返回500的一般格式错误
     */
    public function route($url = null)
    {
        //echo $url;exit;
        $params = RouterHelper::segmentizeUrl($url);

        /*
         * Look for a Api controller
         */
        $api = isset($params[0]) ? $params[0] : null;
        $api = Str::ucfirst($api).'Api';
        $apiClass = $this->ns."\\".$api;
        // 是否测试类
        $testPathDeep = 0;
        if (env('APP_DEBUG') && $params[0] == 'test') {
            $api = isset($params[1]) ? $params[1] : null;
            $api = Str::ucfirst($api).'Test';
            $apiClass = $this->ns."\\test\\".$api;
            $testPathDeep = 1;
        }
        if (is_null($api)) {
            return $this->response->errorNotFound('api not exist ');
        }
        self::$action = $action = isset($params[$testPathDeep+1]) ? $this->parseAction($params[$testPathDeep+1]) : null;
        self::$params = $controllerParams = array_slice($params, $testPathDeep+2);

        if (!class_exists($apiClass)) {
            return $this->response->errorNotFound('api path not exist ' . (env('APP_DEBUG') ? $apiClass : ''));
        } else {
            $controllerObj = App::make($apiClass);
            if (!$action) $action = 'index';
            if ($controllerObj->actionExists($action)) {
                return $controllerObj->handle($action, $controllerParams);
            } else {
                return $this->response->errorNotFound('api command not exist ' . (env('APP_DEBUG') ? $action : ''));
            }
        }
    }

    /**
     * Process the action name, since dashes are not supported in PHP methods.
     * @param  string $actionName
     * @return string
     */
    protected function parseAction($actionName)
    {
        if (strpos($actionName, '-') !== false) {
            return camel_case($actionName);
        }

        return $actionName;
    }
}