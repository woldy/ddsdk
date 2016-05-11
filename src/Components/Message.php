<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class Message{
	public static function sendMessageByCode($ACCESS_TOKE,$config,$code){
		$join=self::decode($code);
		$param=json_decode($join,true);
		if(!isset($config->get('dd')['notice'][$param['user']])){
			die('api用户不存在！');
		}else if($config->get('dd')['notice'][$param['user']]['password']!=$param['password']){
			die('api密码错误！');
		}else{
			$AgentID=$config->get('dd')['AgentID'];
			$content=base64_decode($param['content']);
			$touser=$config->get('dd')['notice'][$param['user']]['touser'];
			$toparty=$config->get('dd')['notice'][$param['user']]['toparty'];
			self::sendMessage($touser,$toparty,$content,$AgentID,$ACCESS_TOKE);
		}
 
	}

	public static function sendMessage($touser,$toparty,$content,$AgentID,$ACCESS_TOKE,$type='text'){
		//$content=iconv('GB2312', 'UTF-8', $content);
		//var_dump($content);
		//exit;
		if($type=='text'){
			$content=iconv('GB2312', 'UTF-8', $content);
        	$param=array(
        		'touser' =>$touser, 
        		'toparty'=>$toparty,
        		'agentid'=>$AgentID,
        		"msgtype"=>"text",
            	"text"=>array("content"=>$content)
        	);
        	//var_dump(json_encode($param));
        	//exit;
            $response = Request::post('https://oapi.dingtalk.com/message/send?access_token='.$ACCESS_TOKE)
            	->body(json_encode($param))
            	->sendsJson()
            	->send();
            if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        	}
        	if ($response->body->errcode != 0){
            	var_dump($response->body);
            	exit;
        	}
 			echo 'send ok';
            exit;
		}
	}


	static function encode($string = '', $skey = 'woldy') {
    	$strArr = str_split(base64_encode($string));
    	$strCount = count($strArr);
    	foreach (str_split($skey) as $key => $value)
        $key < $strCount && $strArr[$key].=$value;
    	return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
	}

	static function decode($string = '', $skey = 'woldy') {
    	$strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    	$strCount = count($strArr);
    	foreach (str_split($skey) as $key => $value)
        $key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    	return base64_decode(join('', $strArr));
	}
}