<?php
/**
 * wechat php test
 */
//define your token
include "./wb_douban_api.php";
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
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
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
        switch ($object->Event) {
            case "subscribe":
                $content = "欢迎关注wxbook公众号！";
                break;
            case "unsubscribe":
                $content = "谢谢关注wxbook公众号！";
                break;
            case "scancode_waitmsg":
                $isbn      = substr($object->ScanCodeInfo->ScanResult, 7);
                $content   = "isbn:" . $isbn;
                $douBanObj = new DoubanApi();
                $bookInfo  = $douBanObj->getDoubanInfo($isbn);
                $image     = $bookInfo["images"]["large"];
                if (is_array($bookInfo['author']) && sizeof($bookInfo['author']) > 0) {
                    $bookInfo['author'] = $bookInfo['author'][0];
                } else {
                    $bookInfo['author'] = '';
                }
                $content   = array();
                $summary   = mb_substr($bookInfo['summary'], 0, 200, 'utf-8') . '...';
                $content[] = array(
                    "Title"       => "已成功添加：" . $bookInfo["title"],
                    "Description" => $summary,
                    "PicUrl"      => $image,
                    "Url"         => $check_url,
                );

                break;
            default:
                $content = "receive a new event: " . $object->Event;
                break;

        }
        if (is_array($content)) {
            if (isset($content[0]['PicUrl'])) {
                $result = $this->transmitNews($object, $content);
            }
        } else {
            $result = $this->transmitText($object, $content);

        }
        return $result;

    }

    private function transmitNews($object, $arr_item, $funcFlag = 0)
    {
        $itemTpl = "    <item>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($arr_item as $item) {
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }

        #防止字符中有百分号
        $new_item_str = str_replace("%", "%%", $item_str, $i);

        $newsTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <Content><![CDATA[]]></Content>
        <ArticleCount>%s</ArticleCount>
        <Articles>
        $new_item_str</Articles>
        <FuncFlag>%s</FuncFlag>
        </xml>";

        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item), $funcFlag);
        return $resultStr;
    }

    public function transmitText($object, $content, $funcFlag = 0)
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
