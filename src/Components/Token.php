<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class Token{
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
            Cache::put('corp_access_token', $accessToken,60);
        }
        return $accessToken;
	}

	public function getSsoToken(){
		//https://oapi.dingtalk.com/sso/gettoken?corpid=id&corpsecret=ssosecret
	}


	public function getJsapiTicket(){
        $jsticket = Cache::get('js_ticket');
        $param=http_build_query(
        	array(
        		'type' =>'jsapi', 
        		'access_token'=>$this->getAccessToken()
        	)
        );
        if (!$jsticket)
        {
            $response = Request::get('https://oapi.dingtalk.com/get_jsapi_ticket?'.$param)->send();
            if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        	}
        	if ($response->body->errcode != 0){
            	var_dump($response->body);
            	exit;
        	}
            $jsticket = $response->body->ticket;
            Cache::put('js_ticket',$jsticket,60);
        }
        return $jsticket;
		//https://oapi.dingtalk.com/get_jsapi_ticket?access_token=ACCESS_TOKE
	}

	public function getSignature($ticket, $nonceStr, $timeStamp, $url){
        $plain = 'jsapi_ticket=' . $ticket .
            '&noncestr=' . $nonceStr .
            '&timestamp=' . $timeStamp .
            '&url=' . $url;
        return sha1($plain);		
	}


    private function getCurPageURL(){
        $pageURL = 'http';
        if (array_key_exists('HTTPS',$_SERVER)&&$_SERVER["HTTPS"] == "on"){
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80"){
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        }
        else{
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }


	public function getJsConfig(){
        $corpId =$this->CorpID;
        $agentId = $this->AgentID;
        $timeStamp = time();
        $nonceStr = md5($timeStamp.'woldy');
        
        $url = $this->getCurPageURL();
        $corpAccessToken = $this->getAccessToken();
        if (!$corpAccessToken)
        {
            Log::e("[getConfig] ERR: no corp access token");
        }
        $ticket = $this->getJsapiTicket($corpAccessToken);
        $signature = $this->getSignature($ticket, $nonceStr, $timeStamp, $url);
        
        $config = array(
            'url' => $url,
            'nonceStr' => $nonceStr,
            'agentId' => $agentId,
            'timeStamp' => $timeStamp,
            'corpId' => $corpId,
            'signature' => $signature
        );
        return json_encode($config, JSON_UNESCAPED_SLASHES);
	}

}