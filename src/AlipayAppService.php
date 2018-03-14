<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 17-2-10
 * Time: 下午4:12
 * To change this template use File | Settings | File Templates.
 */
namespace Hepm;
/*
 示列
 $alipay_params = array(
    "timeout_express"=>"30m",
    "product_code"=>"QUICK_MSECURITY_PAY",
    "total_amount"=>$goods['money'],
    "subject"=> "游戏充值_".date('YmdHis'),
    "body"=>"手游游戏充值",
    "out_trade_no"=>$order_sn
);
$response['pay_channel'] = 1;
$response['status'] = 0;
$response['msg'] = "订单创建成功";
$AlipayApp = new AlipayAppService();
$orderInfo = $AlipayApp->getOrderString($alipay_params);
$response['data'] = array('orderInfo'=>$orderInfo);
 */
/**
 * 支付宝 服务端参数订单参数生成
 * Class AlipayAppService
 *
 *
 *
 */
class AlipayAppService {

    /**
     * 生成支付订单
     * @param $bizContent
     * @return string
     */
    public function getOrderString($bizContent){
        $bizContent = json_encode($bizContent);
        require_once __DIR__.'/sdk/alipay_sdk/aop/AopClient.php';
        require_once __DIR__.'/sdk/alipay_sdk/aop/request/AlipayTradeAppPayRequest.php';
        $config_rsa = <<<EOT
		
EOT;
        $c = new AopClient;
        $c->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $c->appId = "";
        $c->rsaPrivateKey = trim($config_rsa);
        $c->format = "json";
        $c->charset= "utf-8";
        $c->signType= "RSA2";

 
        $request = new AlipayTradeAppPayRequest ();
      //  $order_sn = "ML".date("YmdHis").rand(1000,9999);
       // $order_sn = "021006445494750";
       // $request->setBizContent("{\"timeout_express\":\"30m\",\"product_code\":\"QUICK_MSECURITY_PAY\",\"total_amount\":\"0.01\",\"subject\":\"1\",\"body\":\"我是测试数据\",\"out_trade_no\":\"" .$order_sn. "\"}");
        $request->setBizContent($bizContent);
        $request->setNotifyUrl("http://xxxx/payback/alipay_notify.html");
        return $c->sdkExecute($request);
    }

    /**
     * 支付通知验证
     * @param $response
     * @return bool
     */
    function notify($response){
        $config_rsaPublicKey = <<<EOT

EOT;
        $config_rsaPublicKey = trim($config_rsaPublicKey);
        require_once __DIR__.'/sdk/alipay_sdk/aop/AopClient.php';
        $c = new AopClient;
        $c->charset= "utf-8";
        $c->signType= "RSA2";
        $c->alipayrsaPublicKey = $config_rsaPublicKey;
        $res = $c->rsaCheckV1($response,$config_rsaPublicKey,"RSA2");
        return $res;

    }




}