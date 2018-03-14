<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 17-6-15
 * Time: 下午3:32
 * To change this template use File | Settings | File Templates.
 */

namespace Hepm;

require_once(__DIR__ . '/src/sdk/weixin_ulo/libs/Utils.class.php');
require_once(__DIR__ . '/src/sdk/weixin_ulo/libs/Props.php');
require_once(__DIR__ . '/src/sdk/weixin_ulo/libs/RequestHandler.class.php');
require_once(__DIR__ . '/src/sdk/weixin_ulo/libs/ResponseHandler.class.php');
require_once(__DIR__ . '/src/sdk/weixin_ulo/libs/HttpClient.php');
require_once(__DIR__ . '/src/sdk/weixin_ulo/libs/Log.php');


class UloWeixinService {
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

    public function order($out_trade_no,$money){
        $detail = '{"app_name":"","bundle_id":"","package_name":"","wap_url":"http://pay.youxi53.com","wap_name":"youxi53","note":"","attach":""}';
        $param = array(
            'out_trade_no'=>$out_trade_no,//商户订单号
            'body'=>"游戏支付".date("Ymd"),
            'total_fee'=>$money*100,
            'spbill_create_ip'=>$_SERVER["SERVER_ADDR"],
            'return_url'=>'http://pay.youxi53.com/payback/return_url.html',
            'notify_url'=>"http://pay.youxi53.com/payback/ulo_notify.html",
            'trade_type'=>'trade.weixin.h5pay',
            'detail'=>$detail,
        );
        $this->url = $this->props->K('PAY_URL');
        $this->reqHandler->setReqParams($param,array('method'));
        $this->reqHandler->setParameter('mch_id',$this->props->K('MCH_ID'));//必填项，商户号，由梓微兴分配
        $this->reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand()));//随机字符串，必填项，不长于 32 位
        $this->reqHandler->createSign();
        $data = Utils::to($this->reqHandler->getAllParameters());
      //  $log_filename = sprintf("%s.%s.%s",__CLASS__,__FUNCTION__,'log');//日志文件
     //   Service('Filelog')->write(var_export($data,true),$log_filename,'pay');
        $this->pay->setReqContent($this->url,$data);
        if($this->pay->invoke()){
        //    Service('Filelog')->write($this->pay->getResContent(),$log_filename,'pay');
            $res = Utils::parse($this->pay->getResContent());
            return $res['prepay_url'];
        }else{
            return false;
        }
    }



    function isRightSign(array $ary) {
        $props = new Props();
        $signPars = "";
        ksort($ary);
        foreach($ary as $k => $v) {
            if("sign" != $k && "" != $v) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $props->K("SIGN_KEY");

        $sign = strtolower(md5($signPars));

        $signOrigin  = strtolower($ary["sign"]);

        return $sign == $signOrigin;

    }
    function notify($data) {
        $response = [];// 响应微信的数据结构
        // 采用以下方式替换weiixn的方式
      //  $log_filename = sprintf("%s.%s.%s",__CLASS__,__FUNCTION__,'log');//日志文件
      //  Service('Filelog')->write(var_export($data,true),$log_filename,'pay');
        if ($data === false) {
            $response = [
                'return_code'   => 'FAIL',
                'return_msg'    => '解析数据错误',
            ];
            return $response;
        }
        // 检查是否完成支付
        if ($data['result_code'] !== 'SUCCESS' || $data['return_code'] !== 'SUCCESS') {
            $response = [
                'return_code'   => 'FAIL',
                'return_msg'    => '尚未支付',
            ];
            return $response;
        }
        // 验证返回的结果签名
        if (! $this->isRightSign($data)) {
            $response = [
                'return_code'   => 'FAIL',
                'return_msg'    => '签名错误',
            ];
            return $response;
        }else {
            $response = [
                'return_code'   => 'SUCCESS',
                'return_msg'    => 'OK',
            ];
            return $response;
        }
    }



}
