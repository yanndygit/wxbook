<?php
class DouBanApi
{
    public function getDoubanInfo($isbn)
    {
        $url = "https://api.douban.com//v2/book/isbn/";
        $url .= $isbn;
        $content  = file_get_contents($url);
        $bookInfo = json_decode($content, true);

        $img               = $bookInfo["image"];
        $bookInfo['image'] = substr($img, strrpos($img, '/') + 1);
        $ret               = $this->getImage($img, '', 1);
        if ($ret == true) {
            return $bookInfo;
        } else {
            return false;
        }

    }

    public function getDoubanInfoByName($name)
    {
        $url = "https://api.douban.com/v2/book/search?q=";
        $url .= $name;
        $content  = file_get_contents($url);
        $bookInfo = json_decode($content, true);
        return $bookInfo;
        
    }

    public function getImage($url, $filename = '', $type = 0)
    {
        try {
            if ($url == '') {return false;}
            if ($filename == '') {
                $ext = strrchr($url, '.');
                if ($ext != '.gif' && $ext != '.jpg') {return false;}
                $filename1 = substr($url, strrpos($url, '/') + 1);
                $dir       = $_SERVER['DOCUMENT_ROOT'];
                $filename  = $dir . './' . $filename1;
               
            }
            //文件保存路径
            if ($type) {
                $ch      = curl_init();
                $timeout = 5;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $img = curl_exec($ch);
                curl_close($ch);
            } else {
                ob_start();
                readfile($url);
                $img = ob_get_contents();
                ob_end_clean();
            }
            $size = strlen($img);
            //文件大小
            $fp2 = @fopen($filename, 'a');
            fwrite($fp2, $img);
            fclose($fp2);
            return true;
        } catch (Exception $e) {echo $e->getMessage();exit();}

    }

}


//$douBanObj = new DouBanApi();
//$bookInfo = $douBanObj->getDoubanInfo("9787508642802");
//echo "<br><br>bookInfo:".$bookInfo."    title:".$bookInfo["title"];
//$content = array();
//$content[] = array("Title"=>$bookInfo["title"], "Description"=>$bookInfo["summary"], "PicUrl"=>"http://images.cnitblog.com/i/340216/201404/301756448922305.jpg", "Url" =>"http://mm.wanggou.com/item/jd2.shtml?sku=11447844");
//echo "<br><br>";
//echo var_dump($content);
 
