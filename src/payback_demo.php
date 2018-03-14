<?php

/**
 * Created by JetBrains PhpStorm.
 * User: ok_fish
 * Date: 18-3-14
 * Time: 上午11:09
 * To change this template use File | Settings | File Templates.
 */


function alipay_notify(){
    $notify = $_POST;
    $AlipayApp = new \Hepm\Payment\AlipayAppService();
    $verify = $AlipayApp->notify($notify);
    $map = array('out_trade_no' => $notify['out_trade_no']);//商户订单号
    $res = D('Order')->pay_back_order($verify,$map,$notify);
    if($res){
        echo "success";
    }else{
        echo "fail";
    }
}

/***
 * 优络微信H5
 * @return string
 */
function ulo_notify(){
    require_once __DIR__ . '/src/sdk/weixin_ulo/libs/Utils.class.php';
    $xml = file_get_contents("php://input");
    if (empty($xml)) {
        $response = [
            'return_code'   => 'FAIL',
            'return_msg'    => '未获取到微信的数据',
        ];
        echo Utils::to($response);return;
    }
    // 格式化数据为数组
    $notify = Utils::parse($xml);
    $verify = service('UloWeixin')->notify($notify);

    if($verify['return_code']=='SUCCESS'){
        $map = array('out_trade_no' => $notify['out_trade_no']);//商户订单号
        $res = D('Order')->pay_back_order(true,$map,$notify);
        if($res){
            echo Utils::to($verify);
            $this->CallbackGame($notify['out_trade_no']);
        }
    }else{
        echo Utils::to($verify);return;
    }
}

/**
 * 银联商户h5 支付
 */
function chinaums_notify(){
    $log_filename = sprintf("%s.%s.%s",__CLASS__,__FUNCTION__,'log');//日志文件
    Service('Filelog')->write(var_export($_POST,true),$log_filename,'pay');

    $notify = $_POST;
    /***
     *  NEW_ORDER 	新订单	 
    UNKNOWN 	不明确的交易状态	 
    TRADE_CLOSED 	在指定时间段内未支付时关闭的交易；在交易完成全额退款成功时关闭的交易；支付失败的交易。	TRADE_CLOSED的交易不允许进行任何操作。
    WAIT_BUYER_PAY 	交易创建，等待买家付款。	 
    TRADE_SUCCESS	支付成功	 
    TRADE_REFUND	订单转入退货流程	退货可能是部分也可能是全部。
     */
    /*
     * 注意：商户收到通知后，需要对通知做出响应：成功时响应”SUCCESS”；失败时响应”FAILED”。
     */
    if($notify['status']!='TRADE_SUCCESS'){
        echo "SUCCESS";
        Service('Filelog')->write("SUCCESS".__LINE__,$log_filename,'pay');
        exit();
    }
    $verify = service('Chinaums')->notify($notify);
    if($verify){
        $out_trade_no = str_replace('3194YX','YX',$notify['merOrderId']);//去掉订单号前缀
        $map = array('out_trade_no' =>$out_trade_no);//商户订单号
        $res = D('Order')->pay_back_order($verify,$map,$notify);
        if($res){
            echo "SUCCESS";
            Service('Filelog')->write("SUCCESS".__LINE__,$log_filename,'pay');
            $this->CallbackGame($out_trade_no);
        }else{
            echo "FAILED";
            Service('Filelog')->write("FAILED".__LINE__,$log_filename,'pay');
        }
    }else{
        echo "FAILED";
        Service('Filelog')->write("FAILED".__LINE__,$log_filename,'pay');
    }
}

function return_url(){
    echo <<<JS
        <script>
          SdkPayJS.paySucess("支付完成");
        </script>
JS;
}