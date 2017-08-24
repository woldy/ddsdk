<?php
namespace Woldy\ddsdk;
use Illuminate\Config\Repository;
use Woldy\ddsdk\Components\Token;
use Woldy\ddsdk\Components\Message;
use Woldy\ddsdk\Components\Contacts;
use Woldy\ddsdk\Components\Group;
use Woldy\ddsdk\Components\Chat;
use Woldy\ddsdk\Components\App;
use Woldy\ddsdk\Components\Work;
use Woldy\ddsdk\Components\Callback;
use Woldy\ddsdk\Components\dCrypt;
use Illuminate\Support\Facades\Input;
use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
class dd{
	public static $config;
	public static $token;
	public static $ACCESS_TOKEN;
	public function __construct(Repository $config){
		self::$config = $config;
		self::$token = new Token($config);
		try{
			self::$ACCESS_TOKEN=self::$token->getAccessToken();
		}catch(Httpful\Exception\ConnectionErrorException $e){
			self::$ACCESS_TOKEN=self::$token->getAccessToken();
		}
	}

	/**
	 * 取得JS SDK 配置
	 * @Author   Woldy
	 * @DateTime 2016-05-09T17:01:32+0800
	 */
	public static function getJsConfig($appId='',$url=''){
		try{
			return self::$token->getJsConfig($appId,$url);
		}catch(Httpful\Exception\ConnectionErrorException $e){
			return self::$token->getJsConfig($appId,$url);
		}
	}

	public static function test(){
		echo 'ddtest';
	}

	public static function getAccessToken(){
		return self::$token->getAccessToken();
	}


	/**
	 * 获取SSO配置,好像没啥卵用了
	 */
	public static function getSsoConfig($ssoid){
		$ssolist=self::$config->get('dd')['sso'];

		if(!array_key_exists($ssoid,$ssolist)){
			die('wrong id!');
		}else{
			return $ssolist[$ssoid];
		}
	}

	/**
	 * 上传文件
	 * @Author   Woldy
	 * @DateTime 2016-05-09T17:03:07+0800
	 * @return   [type]                   [description]
	 */
	public static function upLoadFile($path){
		try{
			return Message::upLoadFile(self::$ACCESS_TOKEN,$path);
		}catch(Httpful\Exception\ConnectionErrorException $e){
      return Message::upLoadFile(self::$ACCESS_TOKEN,$path);
    }
	}


	public static function putAttend($data){
		try{
			return Work::putAttend(self::$ACCESS_TOKEN,$data);
		}catch(Httpful\Exception\ConnectionErrorException $e){
      return Work::putAttend(self::$ACCESS_TOKEN,$data);
    }
	}

	/**
	 * 根据免登CODE获取用户信息
	 * @Author   Woldy
	 * @DateTime 2016-05-09T17:03:07+0800
	 * @return   [type]                   [description]
	 */
	public static function getUserInfoByCode($authcode){
		try{
			return Contacts::getUserInfoByCode(self::$ACCESS_TOKEN,$authcode);
		}catch(Httpful\Exception\ConnectionErrorException $e){
            return Contacts::getUserInfoByCode(self::$ACCESS_TOKEN,$authcode);
    }
	}

	/**
	 * 根据UserID获取用户信息
	 * @Author   Woldy
	 * @DateTime 2016-05-09T17:03:34+0800
	 * @return   [type]                   [description]
	 */
	public static function getUserInfoByUid($uid){
		try{
				return Contacts::getUserInfoByUid(self::$ACCESS_TOKEN,$uid);
		}catch(Httpful\Exception\ConnectionErrorException $e){
				echo 'retry';
        return Contacts::getUserInfoByUid(self::$ACCESS_TOKEN,$uid);
    }
	}

	/**
	 * 发送消息
	 * @Author   Woldy
	 * @DateTime 2016-05-09T19:57:22+0800
	 * @param    string                   $type    [description]
	 * @param    [type]                   $content [description]
	 * @return   [type]                            [description]
	 */
	public static function sendMessage($touser,$toparty,$content,$type='text'){
		try{
						return Message::sendMessage($touser,$toparty,$content,self::$config,self::$ACCESS_TOKEN,$type='text');
		}catch(Httpful\Exception\ConnectionErrorException $e){
            return Message::sendMessage($touser,$toparty,$content,self::$config,self::$ACCESS_TOKEN,$type='text');
  	}
	}

	/**
	 * 通过加密串发送信息
	 * @Author   Woldy
	 * @DateTime 2016-05-10T13:20:06+0800
	 * @param    [type]                   $code [description]
	 * @return   [type]                         [description]
	 */
	public static function sendMessageByCode($code=''){
		if(empty($code)){
			$code=Input::get('code');
		}


		try{
					return Message::sendMessageByCode(self::$ACCESS_TOKEN,self::$config,$code);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Message::sendMessageByCode(self::$ACCESS_TOKEN,self::$config,$code);
    }

	}


	/**
	 * 扫码登录
	 * @Author   Woldy
	 * @DateTime 2016-08-23T11:17:36+0800
	 * @param    [type]                   $code [description]
	 * @return   [type]                         [description]
	 */
	public static function snsLogin($code){
		$accesstoken=self::$token->getSnsAccessToken();
		$persistent=self::$token->getPersistent($accesstoken,$code);
		$snscode=self::$token->getSnsToken($accesstoken,$persistent);
		try{
					$userinfo=Contacts::getUserInfoBySns($snscode);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           $userinfo=Contacts::getUserInfoBySns($snscode);
    }

		try{
					$userid=Contacts::getUserIdByUnionId(self::$ACCESS_TOKEN,$userinfo->unionid);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           $userid=Contacts::getUserIdByUnionId(self::$ACCESS_TOKEN,$userinfo->unionid);
    }

		try{
					$userinfo=Contacts::getUserInfoByUid(self::$ACCESS_TOKEN,$userid);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           $userinfo=Contacts::getUserInfoByUid(self::$ACCESS_TOKEN,$userid);
    }

		return $userinfo;
	}


	public static function getUserIdByUnionId($unionid){
		try{
					return Contacts::getUserIdByUnionId(self::$ACCESS_TOKEN,$unionid);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Contacts::getUserIdByUnionId(self::$ACCESS_TOKEN,$unionid);
    }


	}


	/**
	 * 删除用户
	 * @Author   Woldy
	 * @DateTime 2016-08-23T11:17:48+0800
	 * @param    [type]                   $ids [description]
	 * @return   [type]                        [description]
	 */
	public static function delUser($ids){
		$accesstoken=self::$ACCESS_TOKEN;


		try{
				return Contacts::delUserByIds($accesstoken,$ids);
		}catch(Httpful\Exception\ConnectionErrorException $e){
        return Contacts::delUserByIds($accesstoken,$ids);
    }
	}

	/**
	 * 增加用户
	 * @Author   Woldy
	 * @DateTime 2016-08-23T11:17:58+0800
	 * @param    [type]                   $user [description]
	 */
	public static function addUser($user){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Contacts::addUser($accesstoken,$user);
		}catch(Httpful\Exception\ConnectionErrorException $e){
      return Contacts::addUser($accesstoken,$user);
    }

	}

	public static function updateUser($user){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Contacts::updateUser($accesstoken,$user);
		}catch(Httpful\Exception\ConnectionErrorException $e){
			echo 'retry';
      return Contacts::updateUser($accesstoken,$user);
    }
	}

	public static function createChat($user_ids,$chat_title){
		$accesstoken=self::$ACCESS_TOKEN;
		return Chat::createChat($accesstoken,$user_ids,$chat_title);
	}

	public static function addToChat($user_ids,$chat_id){
		$accesstoken=self::$ACCESS_TOKEN;
		return Chat::addToChat($accesstoken,$user_ids,$chat_id);
	}

	public static function getChat($chatid){
		$accesstoken=self::$ACCESS_TOKEN;
		return Chat::getChat($accesstoken,$chatid);
	}

	public static function getAllGroups($refresh=false){
		$accesstoken=self::$ACCESS_TOKEN;
		return Group::getAllGroups($accesstoken,$refresh);
	}

	public static function getFullGroups($refresh=false){
		$accesstoken=self::$ACCESS_TOKEN;
		return Group::getFullGroups($accesstoken,$refresh);
	}

	public static function getAllUsers($refresh=false,$extPart=''){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Contacts::getAllUsers($accesstoken,$refresh,$extPart);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Contacts::getAllUsers($accesstoken,$refresh,$extPart);
    }

	}

	public static function getGroupUsers($groupid){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Group::getGroupUsers($groupid,$accesstoken);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Group::getGroupUsers($groupid,$accesstoken);
    }

	}

	public static function getGroupInfo($groupid){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Group::getGroupInfo($groupid,$accesstoken);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Group::getGroupInfo($groupid,$accesstoken);
    }

	}

	public static function getGroupById($groupid,$sub=true,$refresh=false){
		$groupid=preg_replace('/\D/','',$groupid);
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Group::getGroupById($groupid,$accesstoken,$sub,$refresh);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Group::getGroupById($groupid,$accesstoken,$sub,$refresh);
    }

	}

	public static function getSubGroups($groupid,$deep,$refresh=false){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Group::getSubGroups($groupid,$accesstoken,$deep,$refresh);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Group::getSubGroups($groupid,$accesstoken,$deep,$refresh);
    }

	}

	public static function updateGroup($group){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Group::updateGroup($group,$accesstoken);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Group::updateGroup($group,$accesstoken);
    }

	}

	public static function delGroup($groupid){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Group::delGroup($groupid,$accesstoken);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Group::delGroup($groupid,$accesstoken);
    }

	}




	public static function getAttend($userid,$from='',$to=''){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Work::getAttend($accesstoken,$userid,$from,$to);
		}catch(Httpful\Exception\ConnectionErrorException $e){
       return Work::getAttend($accesstoken,$userid,$from,$to);
    }

	}

	public static function getApp($agentId){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return App::getApp($accesstoken,$agentId);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return App::getApp($accesstoken,$agentId);
    }

	}

	public static function setApp($app){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return App::setApp($accesstoken,$app);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return App::setApp($accesstoken,$app);
    }

	}

	public static function getGroupByName($groupName,$create=true,$refresh=false){
		$accesstoken=self::$ACCESS_TOKEN;
		try{
			return Group::getGroupByName($groupName,$accesstoken,$create,$refresh);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Group::getGroupByName($groupName,$accesstoken,$create,$refresh);
    }

	}



	public static function reg_callback($url,$crypt_token='Ca11Back@W0LDy',$aes_key='vFj6jfj7EtDQzPrN0NqWbElkaCN8ZbGDRX86ayxMT5w',
		$call_back_tag=['user_add_org', 'user_modify_org', 'user_leave_org','org_admin_add', 'org_admin_remove', 'org_dept_create', 'org_dept_modify', 'org_dept_remove', 'org_remove', 'chat_add_member', 'chat_remove_member', 'chat_quit', 'chat_update_owner', 'chat_update_title', 'chat_disband', 'chat_disband_microapp'
		]){
		$accesstoken=self::$ACCESS_TOKEN;


		return Callback::reg_callback($accesstoken,$url,$crypt_token,$aes_key,$call_back_tag);
	}


	public static function fail_callback(){
		$ACCESS_TOKEN=self::$ACCESS_TOKEN;
		try{
			 return Callback::fail_callback($ACCESS_TOKEN);
		}catch(Httpful\Exception\ConnectionErrorException $e){
            return Callback::fail_callback($ACCESS_TOKEN);
    }

	}


	public static function createGroup($name,$parentid){
		$ACCESS_TOKEN=self::$ACCESS_TOKEN;
		try{
			return Group::createGroup($name,$parentid,$ACCESS_TOKEN);
		}catch(Httpful\Exception\ConnectionErrorException $e){
           return Group::createGroup($name,$parentid,$ACCESS_TOKEN);
    }

	}

	/**
	 * 人员去重
	 * @Author   Woldy
	 * @DateTime 2016-08-23T18:02:57+0800
	 * @return   [type]                   [description]
	 */
	public function cleanDouble($id1,$id2){
		$accesstoken=self::$ACCESS_TOKEN;
		$info1=Contacts::getUserInfoByUid(self::$ACCESS_TOKEN,$id1);
		$info2=Contacts::getUserInfoByUid(self::$ACCESS_TOKEN,$id2);

		$a1=$info1->active;
		$a2=$info2->active;

		if($a1===false){
			$delid=$info1->userid;
		}else if($a2===false){
			$delid=$info2->userid;
		}else{
			//return Contacts::createChat($accesstoken,"manager7108,$id1,$id2","号码去重确认");
		 	return false;
		};
		return Contacts::delUserByIds($accesstoken,$delid);
	}

	public function proxy($api,$param){
		$ACCESS_TOKEN=self::$ACCESS_TOKEN;
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
			    if(!empty(Input::file('media'))){
					$file=Input::file('media');
					$path=$file -> getRealPath().'.jpg';
					move_uploaded_file($file -> getRealPath(),$path);
					try{
	                	$response = Request::post("https://oapi.dingtalk.com/{$api}?access_token=".$ACCESS_TOKEN.'&type='.Input::get('type'))
							->attach(array('media' =>$path))
	                 		->sends('upload')
	                    	->send();
					}catch(Httpful\Exception\ConnectionErrorException $e){
	                	$response = Request::post("https://oapi.dingtalk.com/{$api}?access_token=".$ACCESS_TOKEN.'&type='.Input::get('type'))
							->attach(array('media' =>$path))
	                 		->sends('upload')
	                    	->send();
	        		}

				}else{
					try{
				        $response = Request::post("https://oapi.dingtalk.com/{$api}?access_token=".$ACCESS_TOKEN)
		               	->body($param)
		                ->sends('json')
		                ->send();
					}catch(Httpful\Exception\ConnectionErrorException $e){
                        $response = Request::post("https://oapi.dingtalk.com/{$api}?access_token=".$ACCESS_TOKEN)
		               	->body($param)
		                ->sends('json')
		                ->send();
        			}

				}
			}else{
				$param['access_token']=$ACCESS_TOKEN;
				$param=http_build_query($param);
				try{
					$response = Request::get("https://oapi.dingtalk.com/{$api}?".$param)->send();
				}catch(Httpful\Exception\ConnectionErrorException $e){
           			$response = Request::get("https://oapi.dingtalk.com/{$api}?".$param)->send();
       	}

			}


		return $response->body;

	}

	public static function encrypt($signature, $timeStamp, $nonce, $string,$crypt_token='',$aes_key='',$CorpID=''){
		if(empty($crypt_token)){
			$crypt_token=self::$config->get('dd')['CryptToken'];
		}
		if(empty($aes_key)){
			$aes_key=self::$config->get('dd')['AesKey'];
		}

		if(empty($CorpID)){
			$CorpID=self::$config->get('dd')['CorpID'];
		}

			$dCrypt=new dCrypt($crypt_token,$aes_key,$CorpID);
    	$encryptMsg = "";
    	$errCode = $dCrypt->EncryptMsg($string, $timeStamp, $nonce, $encryptMsg);

    	return $encryptMsg;

    	// return [
    	// 	'errcode'=>$errCode,
    	// 	'errmsg'=>$encryptMsg
    	// ];
	}

	public static function decrypt($signature,$timeStamp,$nonce,$encrypt,$crypt_token='',$aes_key='',$CorpID=''){
		if(empty($crypt_token)){
			$crypt_token=self::$config->get('dd')['CryptToken'];
		}
		if(empty($aes_key)){
			$aes_key=self::$config->get('dd')['AesKey'];
		}

		if(empty($CorpID)){
			$CorpID=self::$config->get('dd')['CorpID'];
		}

		$dCrypt=new dCrypt($crypt_token,$aes_key,$CorpID);
		// $signature = $_GET["signature"];
		// $timeStamp = $_GET["timestamp"];
		// $nonce = $_GET["nonce"];
		$msg = "";
		$errCode = $dCrypt->DecryptMsg($signature, $timeStamp, $nonce, $encrypt, $msg);

		return $msg;
		// return [
  //   		'errcode'=>$errCode,
  //   		'errmsg'=>$msg
  //   	];
	}

}
