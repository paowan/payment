<?php
require_once './aop/AopClient.php';
require_once './aop/request/AlipayTradeAppPayRequest.php';

$config_rsa = <<<EOT
EOT;

$c = new AopClient;
$c->gatewayUrl = "https://openapi.alipaydev.com/gateway.do";
$c->appId = "xxxxxx";
$c->rsaPrivateKey = trim($config_rsa);
$c->format = "json";
$c->charset= "utf-8";
$c->signType= "RSA2";
 
$request = new AlipayTradeAppPayRequest ();
$order_sn = "ML".date("YmdHis").rand(1000,9999);
$order_sn = "021006445494750";
$request->setBizContent("{\"timeout_express\":\"30m\",\"product_code\":\"QUICK_MSECURITY_PAY\",\"total_amount\":\"0.01\",\"subject\":\"1\",\"body\":\"我是测试数据\",\"out_trade_no\":\"" .$order_sn. "\"}");
echo $c->sdkExecute($request);
?>