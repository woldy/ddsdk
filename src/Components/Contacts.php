<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class contacts{
    /**
     * 根据免登码获取用户信息
     * @Author   Woldy
     * @DateTime 2016-05-12T09:13:43+0800
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    [type]                   $CODE         [description]
     * @return   [type]                                 [description]
     */
 	public static function getUserInfoByCode($ACCESS_TOKEN,$CODE){
        	$param=http_build_query(
        		array(
        			'code' =>$CODE, 
        			'access_token'=>$ACCESS_TOKEN
        		)
        	);
 
            $response = Request::get('https://oapi.dingtalk.com/user/getuserinfo?'.$param)->send();
            if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        	}
        	if ($response->body->errcode != 0){
            	var_dump($response->body);
            	exit;
        	}
            $userid = $response->body->userid;
 
        return self::getUserInfoByUid($ACCESS_TOKEN,$userid);
	}

    /**
     * 根据uid获取用户信息
     * @Author   Woldy
     * @DateTime 2016-05-12T09:14:17+0800
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    [type]                   $uid          [description]
     * @return   [type]                                 [description]
     */
	public static function getUserInfoByUid($ACCESS_TOKEN,$uid){
        	$param=http_build_query(
        		array(
        			'userid' =>$uid, 
        			'access_token'=>$ACCESS_TOKEN
        		)
        	);
 
            $response = Request::get('https://oapi.dingtalk.com/user/get?'.$param)->send();
            if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        	}
        	if ($response->body->errcode != 0){
            	var_dump($response->body);
            	exit;
        	}
            $user = $response->body;
 
        return $user;
	}


    /**
     * [getUserInfoBySns description]
     * @Author   Woldy
     * @DateTime 2016-08-10T13:53:11+0800
     * @param    [type]                   $SNS_TOKEN [description]
     * @return   [type]                              [description]
     */
    public static function getUserInfoBySns($SNS_TOKEN){
            $param=http_build_query(
                array(
                    'sns_token'=>$SNS_TOKEN
                )
            );
 
            $response = Request::get('https://oapi.dingtalk.com/sns/getuserinfo?'.$param)->send();
            if ($response->hasErrors()){
                var_dump($response);
                exit;
            }
            if ($response->body->errcode != 0){
                var_dump($response->body);
                exit;
            }

            $userinfo = $response->body->user_info;
 
        return $userinfo;
    }

    /**
     * 根据unionId获取员工信息
     * @Author   Woldy
     * @DateTime 2016-08-12T19:16:16+0800
     * @param    [type]                   $ACCESS_TOKEN [description]
     * @param    [type]                   $unionid      [description]
     * @return   [type]                                 [description]
     */
    public static function  getUserIdByUnionId($ACCESS_TOKEN,$unionid){
            $param=http_build_query(
                array(
                    'access_token'=>$ACCESS_TOKEN,
                    'unionid'=>$unionid
                )
            );
 
            $response = Request::get('https://oapi.dingtalk.com/user/getUseridByUnionid?'.$param)->send();
            if ($response->hasErrors()){
                var_dump($response);
                exit;
            }
            if ($response->body->errcode != 0){
                var_dump($response->body);
                exit;
            }
            $userid = $response->body->userid;
 
        return $userid;       
    }

    public static function delUserByIds($ACCESS_TOKEN,$ids){
        if(!is_array($ids)){
            $ids=explode(',', $ids);
        }

        foreach ($ids as $id) {
            $param=http_build_query(
                array(
                    'access_token'=>$ACCESS_TOKEN,
                    'userid'=>$id
                )
            );
            //die('https://oapi.dingtalk.com/user/delete?'.$param);
            $response = Request::get('https://oapi.dingtalk.com/user/delete?'.$param)->send();
            if ($response->hasErrors()){
                var_dump($response);
                exit;
            }
            if ($response->body->errcode != 0){
                var_dump($response->body);
                exit;
            }

            echo "$id,";
        }

        return true;
    }
}