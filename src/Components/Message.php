<?php
namespace Woldy\ddsdk\Components;
use Cache;  
use Storage;
use Httpful\Request;
class Message{
    /**
     * 根据加密串发送企业消息。
     * @Author   Woldy
     * @DateTime 2016-05-12T09:15:19+0800
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    [type]                   $config      [description]
     * @param    [type]                   $code        [description]
     * @return   [type]                                [description]
     */
	public static function sendMessageByCode($ACCESS_TOKEN,$config,$code){
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

            if(isset($param['emails']) && !empty($param['emails'])){
                $touser=[];
                $emails=explode(',', $param['emails']);
                foreach ($emails as $email) {
                    array_push($touser, self::getUser($email));
                }
                $touser=implode('|',$touser);
            }
            			                  
			$toparty=$config->get('dd')['notice'][$param['user']]['toparty'];
			self::sendMessage($touser,$toparty,$content,$AgentID,$ACCESS_TOKEN);
		}
 
	}

    public static function getUser($email){
           $result['list']=[];
            $all=(Storage::disk('local')->get("/ding/all.csv"));
            $list=explode("\n",$all);
            $count=0;
            foreach ($list as $item) {
                $item=trim($item);
                $info=explode(',',$item);
                if(isset($info[5]) && strrpos($info[5], '@')!==false){ //email
                    if($info[5]==$email){
                        return $info[0];
                    }
                }
            }
    }

    /**
     * 根据详细参数发送企业消息
     * @Author   Woldy
     * @DateTime 2016-05-12T09:15:39+0800
     * @param    [type]                   $touser      [description]
     * @param    [type]                   $toparty     [description]
     * @param    [type]                   $content     [description]
     * @param    [type]                   $AgentID     [description]
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    string                   $type        [description]
     * @return   [type]                                [description]
     */
	public static function sendMessage($touser,$toparty,$content,$AgentID,$ACCESS_TOKEN,$type='text'){
		//$content=iconv('GB2312', 'UTF-8', $content);

        //
      
		if($type=='text'){
            if(mb_detect_encoding( $content,'UTF-8') !='UTF-8'){
                $content=iconv('GB2312', 'UTF-8', $content);
            }
        	$param=array(
        		'touser' =>$touser, 
        		'toparty'=>$toparty,
        		'agentid'=>$AgentID,
        		"msgtype"=>"text",
            	"text"=>array("content"=>$content)
        	);
        	//var_dump(json_encode($param));
        	//exit;
            $response = Request::post('https://oapi.dingtalk.com/message/send?access_token='.$ACCESS_TOKEN)
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

    /**
     * 加密串函数
     * @Author   Woldy
     * @DateTime 2016-05-12T09:16:37+0800
     * @param    string                   $string [description]
     * @param    string                   $skey   [description]
     * @return   [type]                           [description]
     */
	static function encode($string = '', $skey = 'woldy') {
    	$strArr = str_split(base64_encode($string));
    	$strCount = count($strArr);
    	foreach (str_split($skey) as $key => $value)
        $key < $strCount && $strArr[$key].=$value;
    	return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
	}

    /**
     * 解密串函数
     * @Author   Woldy
     * @DateTime 2016-05-12T09:16:56+0800
     * @param    string                   $string [description]
     * @param    string                   $skey   [description]
     * @return   [type]                           [description]
     */
	static function decode($string = '', $skey = 'woldy') {
    	$strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    	$strCount = count($strArr);
    	foreach (str_split($skey) as $key => $value)
        $key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    	return base64_decode(join('', $strArr));
	}
}