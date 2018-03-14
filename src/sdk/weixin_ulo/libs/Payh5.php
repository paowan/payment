<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 17-6-15
 * Time: 下午3:32
 * To change this template use File | Settings | File Templates.
 */
require('../libs/Utils.class.php');
require('../libs/Props.php');
require('../libs/RequestHandler.class.php');
require('../libs/ResponseHandler.class.php');
require('../libs/HttpClient.php');
require('../libs/Log.php');


class Payh5 {
    private $resHandler = null;
    private $reqHandler = null;
    private $pay = null;
    private $props = null;
    private $url = null;

    public function __construct(){
        $this->Request();
    }

    public function Request(){
        $this->resHandler = new ResponseHandler();
        $this->reqHandler = new RequestHandler();
        $this->pay = new HttpClient();
        $this->props = new Props();
        $this->reqHandler->setKey($this->props->K('SIGN_KEY'));
    }

    public function order(){
        $detail = '{"app_name":"","bundle_id":"","package_name":"","wap_url":"http://pay.youxi53.com","wap_name":"youxi53","note":"","attach":""}';
        $orderNum=time();
        $param = array(
            'out_trade_no'=>'ML20170208162957100002817',//商户订单号
            'body'=>'商品测试',
            'total_fee'=>'1',
            'spbill_create_ip'=>'127.0.0.1',
            'return_url'=>'http://pay.youxi53.com/index.php?m=test&a=payback',
            'notify_url'=>"http://pay.youxi53.com/index.php?m=test&a=payback",
            'trade_type'=>'trade.weixin.h5pay',
            'detail'=>$detail,

        );
        $this->url = $this->props->K('PAY_URL');
        $this->reqHandler->setReqParams($param,array('method'));
        $this->reqHandler->setParameter('mch_id',$this->props->K('MCH_ID'));//必填项，商户号，由梓微兴分配
        $this->reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand()));//随机字符串，必填项，不长于 32 位
        $this->reqHandler->createSign();
        $data = Utils::to($this->reqHandler->getAllParameters());

        print_r($data);
        $this->pay->setReqContent($this->url,$data);
        if($this->pay->invoke()){
            var_dump($this->pay->getResContent());
            exit();
            $xml = new SimpleXMLElement($this->pay->getResContent());
            print_r(Utils::parse($xml));
        }else{
            exit('错误');
        }
    }



}


