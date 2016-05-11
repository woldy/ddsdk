<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class contacts{
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

}