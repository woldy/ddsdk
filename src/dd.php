<?php
namespace Woldy\ddsdk;
use Illuminate\Config\Repository;
use Woldy\ddsdk\Components\Token;
class dd{
	static $config;
	static $token;
	static $ACCESS_TOKE;
	public function __construct(Repository $config){
		self::$config = $config;
		self::$token = new token($config);
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
	 * 根据免登CODE获取用户信息
	 * @Author   Woldy
	 * @DateTime 2016-05-09T17:03:07+0800
	 * @return   [type]                   [description]
	 */
	public static function getUserInfoByCode($CODE){

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
} 