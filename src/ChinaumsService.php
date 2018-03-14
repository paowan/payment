<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 17-4-21
 * Time: 下午1:55
 * To change this template use File | Settings | File Templates.
 */
namespace Hepm;
/**
 * 银联商务公共H5支付
 * Class ChinaumsService
 */
class ChinaumsService {

    public $api_url = 'https://qr-test2.chinaums.com/netpay-portal/webpay/pay.do';
    public $api_query_url = 'https://qr-test2.chinaums.com/netpay-route-server/api/';
    public $api_key = 'fcAmtnx7MwismjWNhNKdHC44mNXtnEQeJkRrhKJwyrW2ysRR';//正式

    /**
     *      测试账号
     *      商户号(mid):898310060514010
            终端号(tid)：88880001
            机构商户号(instMid)：H5DEFAULT
            消息来源(msgSrc)：WWW.TEST.COM

            来源编号（msgSrcId）：3194
            测试环境MD5密钥:fcAmtnx7MwismjWNhNKdHC44mNXtnEQeJkRrhKJwyrW2ysRR
     * @param $notify_arr
     * @return bool
     */


    public function notify($notify_arr){

        $sign = $notify_arr['sign'];
        $calc_sign = $this->calc_sign($notify_arr,$this->api_key);
        if($sign===$calc_sign){
            return true;
        }else{
            return false;
        }


    }

    /**
     * 微信下单 支付
     */
    public function order($out_trade_no,$game_name='游戏充值'){
        $order = M('order')->where(array('out_trade_no'=>$out_trade_no))->find();
        if(empty($order)){
            return false;
        }
        /*
        $order_param = array(
            "walletOption"=> "SINGLE",
            "billNo"=> "31940000201700002",
            "billDate"=> "2017-06-26",
            "sign"=> "2631915B7F7822C4B00A488A32E03764",
            "requestTimestamp"=> "2017-06-26 17:28:02",
            "instMid"=> "QRPAYDEFAULT",
            "msgSrc"=> "WWW.TEST.COM",
            "totalAmount"=> "1",
            "goods"=> [
                    [
                        "body"=> "微信二维码测试",
                        "price"=> "1",
                        "goodsName"=> "微信二维码测试",
                        "goodsId"=> "1",
                        "quantity"=> "1",
                        "goodsCategory"=> "TEST"
                    ]

                ],
                "msgType"=> "bills.getQRCode",
                "mid"=> "898340149000005",
                "tid"=> "88880001"
        );*/
        $sceneType = 'AND_WAP';
        if(stripos($_SERVER['HTTP_USER_AGENT'],'iphone')!==false){
            $sceneType = 'IOS_WAP';//ios支付 支付场景
        }

        $msgSrcId = '3194';
        $order_param = array(
            'msgSrcId'=>$msgSrcId,
            'msgSrc'=>'WWW.TEST.COM',//消息来源
            'msgType'=>'WXPay.h5Pay',//
            'requestTimestamp'=>date("Y-m-d H:i:s"),
            'merOrderId'=>$msgSrcId.$out_trade_no,
            'mid'=>'898310060514010',//商户号
            'tid'=>'88880001',//终端号
            'instMid'=>'H5DEFAULT',
            'goods'=>[
                [
                    'goodsId'=>$order['game_id'],
                    'goodsName'=>$game_name,
                    'quantity'=>1,
                    'price'=>$order['money'],
                    'goodsCategory'=>'游戏',
                    'body'=>'游戏充值'.date("Y-m-d H:i:s"),
                ]
            ],
            'totalAmount'=>1,
            'sceneType'=>$sceneType,
            'merAppName'=>'youxi53',
            'merAppId'=>'xxxx',
            'signType'=>'MD5',
            'notifyUrl'=>'payback/chinaums_notify.html',//充值回调地址
            'returnUrl'=>'payback/return_url.html',//返回地址
        );

        $order_param['sign'] = $this->calc_sign($order_param,$this->api_key);
        $order_str = '';
        foreach($order_param as $k=>$v){
            if(is_array($v)){
                $order_str .= '&' . $k . '=' .urlencode(json_encode($v,JSON_UNESCAPED_UNICODE));

            }else{
                $order_str .= '&' . $k . '=' .  urlencode($v);
            }
        }
     //   $order_str = substr($order_str,1);
        $h5_pay_url = $this->api_url.'?'.$order_str;
        return $h5_pay_url;
    }


    public function query_order($out_trade_no){
        $order = M('order')->where(array('out_trade_no'=>$out_trade_no))->find();
        if(empty($order)){
            return false;
        }
        $msgSrcId = '3194';
        $order_param = array(
            'msgType'=>'query',
            'msgSrcId'=>$msgSrcId,
            'msgSrc'=>'WWW.TEST.COM',//消息来源
            'requestTimestamp'=>date("Y-m-d H:i:s"),
            'mid'=>'898310060514010',
            'tid'=>'88880001',
            'instMid'=>'H5DEFAULT',
            'merOrderId'=>$msgSrcId.$out_trade_no,
            'signType'=>'MD5',
        );
        $order_param['sign'] = $this->calc_sign($order_param,$this->api_key);
        $order_param = json_encode($order_param);
        $data = $this->post($this->api_query_url,$order_param);

       // echo($data);
        $data = json_decode($data,true);
        return $data;
    }

    public function calc_sign($data,$key){
        if(isset($data['sign'])){
            unset($data['sign']);
        }
        ksort ( $data );
        $data_str  = "";
        foreach($data as $k => $v ) {
            if(is_array($v)){
                $data_str .= '&' . $k . '=' .json_encode($v,JSON_UNESCAPED_UNICODE);

            }else{
                $data_str .= '&' . $k . '=' . $v;
            }

        }
        $data_str = substr($data_str,1);
        //sign = Md5(原字符串&key=商户密钥).toUpperCasef
        $sign = strtoupper(md5($data_str.$key));
        return $sign;

    }


    public function arr_to_xml($data){
        $xml = '<xml>';
        foreach($data as $k=>$v){
            $xml .= "<{$k}><![CDATA[{$v}]]></{$k}>\n";
        }
        $xml .= '</xml>';
        return $xml;
    }


    /**
     *
     * @param $out
     * @return mixed
     */
    public function xml_to_arr($out){
        $xml  = simplexml_load_string($out);
        $json = json_encode($xml);
        $data = json_decode($json, true);
        return $data;
    }

    /**
     * post 请求
     * @param $url 请求url
     * @param array $param  post参数
     * @param array $header 头部信息
     * @param bool $login   是否登陆
     * @param int $ssl      启用ssl
     * @param int $log      是否记录日志
     * @param string $format返回数据格式
     * @return mixed
     */
    function get($url,array $header_options = array(), $cookie = false)
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1, //返回原生的（Raw）输出
//            CURLOPT_HEADER => 0,
//            CURLOPT_TIMEOUT => 120, //超时时间
            CURLOPT_FOLLOWLOCATION => 1, //是否允许被抓取的链接跳转
            CURLOPT_HTTPHEADER => $header_options,
            CURLOPT_ENCODING=>'gzip,deflate'
        );
        if ($cookie) {
            $curl_options[CURLOPT_COOKIE] = $cookie;
        }
        if (strpos($url,"https")!==false) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = false; // 对认证证书来源的检查
        }
//        curl_setopt($ch,CURLINFO_HEADER_OUT,1);
//        curl_setopt($ch,CURLOPT_HEADER,1);

        curl_setopt_array($ch, $curl_options);
        $data = curl_exec($ch);
//        print_r(curl_getinfo($ch));
        curl_close($ch);
        return $data;
    }

    /**
     * post 请求
     * @param $url 请求url
     * @param array $param  post参数
     * @param array $header 头部信息
     * @param bool $login   是否登陆
     * @param int $ssl      启用ssl
     * @param int $log      是否记录日志
     * @param string $format返回数据格式
     * @return mixed
     */
    function post($url, array $param = array(), array $header = array())
    {
        $ch = curl_init();
        $post_param = array();
        if (is_array($param)) {
            $post_param = http_build_query($param);
        } else if (is_string($param)) { //json字符串
            $post_param = $param;
        }

        $header_options =  $header;
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1, //返回原生的（Raw）输出
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => 120, //超时时间
            CURLOPT_FOLLOWLOCATION => 1, //是否允许被抓取的链接跳转
            CURLOPT_HTTPHEADER => $header_options,
            CURLOPT_POST => 1, //POST
            CURLOPT_POSTFIELDS => $post_param, //post数据
            CURLOPT_ENCODING=>'gzip,deflate'
        );

        //debug 1
//        curl_setopt($ch,CURLINFO_HEADER_OUT,1);
//        curl_setopt($ch,CURLOPT_HEADER,1);
        //debug 2 详细的请求过程
//        curl_setopt($ch,CURLOPT_VERBOSE,true);
//        curl_setopt($ch,CURLINFO_HEADER_OUT,0);
//        curl_setopt($ch,CURLOPT_HEADER,0);
//        curl_setopt($ch,CURLOPT_VERBOSE,true);
//        $fp = fopen('php://temp', 'rw+');
//        curl_setopt($ch,CURLOPT_STDERR,$fp);

        if (strpos($url,"https")!==false) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = false; // 对认证证书来源的检查
        }
        curl_setopt_array($ch, $curl_options);
        $data = curl_exec($ch);
        // $debug_info = rewind($fp) ? stream_get_contents($fp):"";
        //$debug_info = curl_getinfo($ch);
        // print_r($debug_info);

//        $data = json_decode($data, true);
        curl_close($ch);
        return $data;
    }




}