<?php


namespace Woldy\ddsdk\Components;
use DD;

class CorpReportListRequest
{

    /**
     * 接口名称
     **/
    public $method = 'dingtalk.corp.report.list';

    /*
     * 请求地址
     * */
    private $url = 'https://eco.taobao.com/router/rest';

    /**
     * 钉钉提供的授权Token
     **/
    public $session;

    /**
     * 时间戳
     **/
    public $timestamp;

    /*
     * 响应格式
     * */
    public $format = 'json';

    /*
     * API协议版本
     * */
    private $v = '2.0';

    /**
     * 查询游标，初始传入0，后续从上一次的返回值中获取
     **/
    public $cursor;

    /**
     * 查询截止时间
     **/
    public $endTime;

    /**
     * 每页数据量
     **/
    private $size = 10;

    /**
     * 查询起始时间
     **/
    private $startTime;

    /**
     * 要查询的模板名称
     **/
    private $templateName;

    public $readTimeout;
    public $connectTimeout;

    /*
     * 发起请求
     * */
    public function conduct()
    {
        return $this->curl($this->url,[
            'method'=>$this->method,
            'session'=>$this->session,
            'timestamp'=>$this->timestamp,
            'format'=>$this->format,
            'v'=>$this->v,
            'start_time'=>$this->startTime,
            'end_time'=>$this->endTime,
            'template_name'=>$this->templateName,
            'cursor'=>$this->cursor,
            'size'=>$this->size,
        ]);
    }


    /*
     * 处理请求参数
     *
     * @param int $start_time
     * @param int $end_time
     * @param int $cursor
     * @param string $template_name
     * */
    public function demand($start_time, $end_time, $cursor = 0, $template_name = '周报')
    {
        $this->startTime = (int) $start_time.'000';
        $this->endTime = (int) $end_time.'000';
        $this->cursor = (int) $cursor;
        $this->templateName = $template_name;
    }


    /*
     * 处理公共参数
     *
     * */
    public function commonality($ACCESS_TOKEN)
    {
        $this->session = $ACCESS_TOKEN;
        $this->timestamp = date('Y-m-d H:i:s',time());

    }


    /*
     * 提交
     *
     * @param url
     * @param array $postFileds
     * @return string $reponse
     * */
    private function curl($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->readTimeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
        }
        if ($this->connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }
        curl_setopt ( $ch, CURLOPT_USERAGENT, "dingtalk-sdk-php" );
        //https 请求
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if (is_array($postFields) && 0 < count($postFields))
        {
            $postBodyString = "";
            $postMultipart = false;
            foreach ($postFields as $k => $v)
            {
                if("@" != substr($v, 0, 1))//判断是不是文件上传
                {
                    $postBodyString .= "$k=" . urlencode($v) . "&";
                }
                else//文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    echo $k.'11111'."\n";
                    $postMultipart = true;
                    if(class_exists('\CURLFile')){
                        $postFields[$k] = new \CURLFile(substr($v, 1));
                    }
                }
                unset($k, $v);
            }
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart)
            {
                if (class_exists('\CURLFile')) {
                    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
                } else {
                    if (defined('CURLOPT_SAFE_UPLOAD')) {
                        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }
            else
            {
                $header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
                curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
            }
        }
        $reponse = curl_exec($ch);

        if (curl_errno($ch))
        {
            throw new Exception(curl_error($ch),0);
        }
        else
        {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode)
            {
                throw new Exception($reponse,$httpStatusCode);
            }
        }
        curl_close($ch);
        return $reponse;
    }

}





/**
 * CorpReportListRequest.php
 *
 * 说明:
 *
 * 修改历史
 * ----------------------------------------
 * 2017/12/4   操作:创建
 **/
