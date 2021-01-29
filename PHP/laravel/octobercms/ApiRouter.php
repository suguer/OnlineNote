<?php namespace Cloud\Rent\Classes\Api;

use App;
use Illuminate\Routing\Controller as ControllerBase;
use October\Rain\Router\Helper as RouterHelper;
use Illuminate\Support\Str;

/**
 * Class ApiRouter
 * API例子：在Now\Youpin\Api下建类Auth，里面有public function hello()，可以通过http://host/api/auth/hello
 * @package Now\Youpin\Classes\Api
 */
class ApiRouter extends ControllerBase
{
    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:*');
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
    }

    /**
     * @var string Allows early access to page action.
     */
    public static $action;

    /**
     * @var array Allows early access to page parameters.
     */
    public static $params;

    public function route($url = null)
    {
        //echo $url;exit;
        $params = RouterHelper::segmentizeUrl($url);

        /*
         * Look for a Api controller
         */
        $api = isset($params[0]) ? $params[0] : null;
        if (is_null($api)) {
            return $this->handle404Error('api not exist');
        }
        $api = Str::ucfirst($api);
        self::$action = $action = isset($params[1]) ? $this->parseAction($params[1]) : null;
        self::$params = $controllerParams = array_slice($params, 2);
        $apiClass = 'Cloud\Rent\Fapi'."\\".$api."Api";
        if (!class_exists($apiClass)) {
            return $this->handle404Error('api path not exist');
        } else {
            $controllerObj = App::make($apiClass);
            if ($controllerObj->actionExists($action)) {
                return $controllerObj->handle($action, $controllerParams);
            } else {
                return $this->handle404Error('api command not exist');
            }
        }
    }

    public function broute($url = null)
    {
        //echo $url;exit;
        $params = RouterHelper::segmentizeUrl($url);

        /*
         * Look for a Api controller
         */
        $api = isset($params[0]) ? $params[0] : null;
        if (is_null($api)) {
            return $this->handle404Error('api not exist');
        }
        $api = Str::ucfirst($api);
        self::$action = $action = isset($params[1]) ? $this->parseAction($params[1]) : null;
        self::$params = $controllerParams = array_slice($params, 2);
        $apiClass = 'Cloud\Rent\Bapi'."\\".$api."Api";
        if (!class_exists($apiClass)) {
            return $this->handle404Error('api path not exist');
        } else {
            $controllerObj = App::make($apiClass);
            if ($controllerObj->actionExists($action)) {
                return $controllerObj->handle($action, $controllerParams);
            } else {
                return $this->handle404Error('api command not exist');
            }
        }
    }


    public function handle404Error($msg) {
        $data = [
            'code' => ApiStatusCode::Not_Found,
            'msg' => $msg,
        ];
        return json_encode($data);
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
