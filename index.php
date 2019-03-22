<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:GET,POST,OPTIONS,PUT');
header('Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Max-Age: 1728000');
require_once './admin.config.php';
error_reporting(0);
$open_check = 1;
$libRequest = \Lib\Lib_Request::getInstance();
if (!isset($_GET['a']) || !isset($_GET['m'])) {
    header("location:index.html");
    exit();
}
$action = $_GET['a'];
$method = $_GET['m'];
$api = $action.'-'.$method;
//释放路由信息，因为用不到了
unset($_GET['a'],$_GET['m'],$_REQUEST['a'],$_REQUEST['m']);
$objName = "App\\Controller\\Controller_" . ucfirst($action);
if (class_exists($objName)) {
    $obj = new $objName();
    if (method_exists($obj, $method)) {
        if ($open_check) {
            $libRequest->checkToken($api);
        }
        $outPut = $obj->$method();
        $outPut = is_array($outPut) ? $outPut : ['status' => '703','msg' => '返回值必须是数组,而不是'.gettype($outPut)];
        $libRequest->outPut($outPut);
    } else {
        $libRequest->outPut(['status' => 404, 'msg' => '方法不存在']);
    }
} else {
    $libRequest->outPut(['status' => 404, 'msg' => '类不存在']);
}