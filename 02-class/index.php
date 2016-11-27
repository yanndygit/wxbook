<?php
/**
 * wechat php test
 */
//define your token
define("TOKEN", "weixintest");
$wechatObj = new wechatTest();
$wechatObj->valid();

class wechatTest
{
    public function valid()
    {
        if (!isset($_GET["echostr"])) {
            $this->responseMsg();
        }
        //valid signature , option
        else if ($this->checkSignature()) {
            echo $_GET["echostr"];
            exit;
        } else {
            $this->responseMsg();
        }
    }
    public function responseMsg()
    {

        //get post data, May be due to the different environments
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents('php://input', 'r');
        } 
        //extract post data
        $postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
            the best way is to check the validity of xml by yourself */
            #######libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            switch ($RX_TYPE) {
                case "text":
                    $resultStr = $this->receiveText($postObj);
                    break;
                case "event":
                    $resultStr = $this->receiveEvent($postObj);
                    break;
                default:
                    $resultStr = "";
                    break;
            }
            if ($resultStr != null) {
                echo $resultStr;
            }

        } else {
            echo "";
            exit();
        }
    }

    private function receiveText($object)
    {
        $funcFlag   = 0;
        $contentStr = "发送的消息是" . $object->Content;
        $resultStr  = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;

    }

    private function receiveEvent($object)
    {
        $content = "";
        global $signature;
        switch ($object->Event) {
            case "subscribe":
                $content = "欢迎关注wxbook公众号！";
                break;
            default:
                $content = "receive a new event: " . $object->Event;
                break;

        }
        $result = $this->transmitText($object, $content);
        return $result;

    }

    private function transmitText($object, $content, $funcFlag = 0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $funcFlag);
        return $resultStr;
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
