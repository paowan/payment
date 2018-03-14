<?php

/**
 * Created by IntelliJ IDEA.
 * User: wjr
 * Date: 16-7-27
 * Time: 上午10:25
 */
class Props{
    private $cfg = array(
        'PAY_URL'=>'https://api.ulopay.com/pay/unifiedorder',//提交订单URL
        'QUERY_URL'=>'https://api.ulopay.com/pay/orderquery',//查询订单URL
        'REFUND_URL'=>'https://api.ulopay.com/secapi/pay/refund',//退款URL
        'QUERY_REFUND_URL'=>'https://api.ulopay.com/pay/refundquery',//查询退款URL
        'MCH_ID'=>'xxxx',
        'SIGN_KEY'=>'xxxx'
    );
/***
 * @param $cfgName
 * @return mixed
 */
    public function K($cfgName){
        return $this->cfg[$cfgName];
    }
}