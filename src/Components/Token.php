<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class token{
	private $AgentID;
	private $CorpID;
	private $CorpSecret;
	private $SSOSecret;

	function __construct($config){
		$this->AgentID=$config->get('dd')['AgentID'];
 		$this->CorpID=$config->get('dd')['CorpID'];
 		$this->CorpSecret=$config->get('dd')['CorpSecret'];
 		$this->SSOSecret=$config->get('dd')['SSOSecret'];
	}

	public function getAccessToken(){
        /**
         * 缓存accessToken。accessToken有效期为两小时，需要在失效前请求新的accessToken（注意：以下代码没有在失效前刷新缓存的accessToken）。
         */
        $accessToken = Cache::get('corp_access_token');
        $param=http_build_query(
        	array(
        		'corpid' =>$this->CorpID, 
        		'corpsecret'=>$this->CorpSecret
        	)
        );
        if (!$accessToken){
        	//die('https://oapi.dingtalk.com/gettoken?'.$param);
            $response = Request::get('https://oapi.dingtalk.com/gettoken?'.$param)->send();
        	if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        	}
        	if ($response->body->errcode != 0){
            	var_dump($response->body);
            	exit;
        	}
            $accessToken = $response->body->access_token;
            Cache::put('corp_access_token', $accessToken,120);
        }
        return $accessToken;
	}

	public function getSsoToken(){

		//https://oapi.dingtalk.com/sso/gettoken?corpid=id&corpsecret=ssosecret
	}


	public function getJsapiTicket(){
		//https://oapi.dingtalk.com/get_jsapi_ticket?access_token=ACCESS_TOKE
	}

	public function getSignature(){
		
	}

	public static function getJsConfig(){
        $corpId =$this->CorpID;
        $agentId = $this->AgentID;
        $timeStamp = time();
        $nonceStr = md5($timestamp.'woldy');
        
        $url = self::curPageURL();
        $corpAccessToken = self::getAccessToken();
        if (!$corpAccessToken)
        {
            Log::e("[getConfig] ERR: no corp access token");
        }
        $ticket = self::getTicket($corpAccessToken);
        $signature = self::sign($ticket, $nonceStr, $timeStamp, $url);
        
        $config = array(
            'url' => $url,
            'nonceStr' => $nonceStr,
            'agentId' => $agentId,
            'timeStamp' => $timeStamp,
            'corpId' => $corpId,
            'signature' => $signature);
        return json_encode($config, JSON_UNESCAPED_SLASHES);
	}

	public static function sign($ticket, $nonceStr, $timeStamp, $url){
        $plain = 'jsapi_ticket=' . $ticket .
            '&noncestr=' . $nonceStr .
            '&timestamp=' . $timeStamp .
            '&url=' . $url;
        return sha1($plain);
    }
}