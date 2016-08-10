<?php
namespace Woldy\ddsdk;
use Illuminate\Config\Repository;
use Woldy\ddsdk\Components\Token;
use Woldy\ddsdk\Components\Message;
use Woldy\ddsdk\Components\Contacts;
use Illuminate\Support\Facades\Input;
class dd{
	static $config;
	static $token;
	static $ACCESS_TOKE;
	public function __construct(Repository $config){
		self::$config = $config;
		self::$token = new Token($config);
		self::$ACCESS_TOKE=self::$token->getAccessToken();
	}

	/**
	 * 取得JS SDK 配置
	 * @Author   Woldy
	 * @DateTime 2016-05-09T17:01:32+0800
	 */
	public static function getJsConfig(){
		return self::$token->getJsConfig();
	}


	/**
	 * 获取SSO配置
	 */
	public static function getSsoConfig($id){
		$ssolist=self::$config->get('dd')['sso'];
		if(!array_key_exists($id,$ssolist)){
			die('wrong id!');
		}else{
			return $ssolist[$id];
		}
	}

	/**
	 * 根据免登CODE获取用户信息
	 * @Author   Woldy
	 * @DateTime 2016-05-09T17:03:07+0800
	 * @return   [type]                   [description]
	 */
	public static function getUserInfoByCode(){
		$code=Input::get('authcode');
		return Contacts::getUserInfoByCode(self::$ACCESS_TOKE,$code);
	}

	/**
	 * 根据UserID获取用户信息
	 * @Author   Woldy
	 * @DateTime 2016-05-09T17:03:34+0800
	 * @return   [type]                   [description]
	 */
	public static function getUserInfoByUid(){

	}

	/**
	 * 发送消息
	 * @Author   Woldy
	 * @DateTime 2016-05-09T19:57:22+0800
	 * @param    string                   $type    [description]
	 * @param    [type]                   $content [description]
	 * @return   [type]                            [description]
	 */
	public static function sendMessage($touser,$toparty,$type='text',$content){

	}

	/**
	 * 通过加密串发送信息
	 * @Author   Woldy
	 * @DateTime 2016-05-10T13:20:06+0800
	 * @param    [type]                   $code [description]
	 * @return   [type]                         [description]
	 */
	public static function sendMessageByCode(){
		$code=Input::get('code');
		//echo $code;
		return Message::sendMessageByCode(self::$ACCESS_TOKE,self::$config,$code);
	}


	public static function snsLogin($code){
		$accesstoken=self::$token->getSnsAccessToken();
		$persistent=self::$token->getPersistent($accesstoken,$code);
		$snscode=self::$token->getSnsToken($accesstoken,$persistent);
		$userinfo=Contacts::getUserInfoBySns($snscode);
		$userid=Contacts::getUserIdByUnionId(self::$ACCESS_TOKE,$userinfo->unionid);
		$userinfo=Contacts::getUserInfoByUid(self::$ACCESS_TOKE,$userid);
		return $userinfo;
	}
} 