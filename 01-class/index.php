
<?php
/**
 * wechat php test
 */
//define your token
//这个token需要和公众号接口配置信息中填的token一致
define("TOKEN", "weixintest"); 
$wechatObj = new wechatTest();
$wechatObj->valid();

class wechatTest
{
    public function valid()
    {
        if (isset($_GET["echostr"])) {
            if ($this->checkSignature()) {
                echo $_GET["echostr"];
                exit;
            }
        }
    }
    public function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce     = $_GET["nonce"];

        $token  = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}

?>
