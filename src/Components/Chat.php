<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class Chat{
	public static function getChat($ACCESS_TOKEN,$chatid){
            $param=http_build_query(
        	   array(
        		  'chatid' =>$chatid,
        		  'access_token'=>$ACCESS_TOKEN
        	   )
            );

            $response = Request::get('https://oapi.dingtalk.com/chat/get?'.$param)->send();
            if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        	}
            if(!is_object($response->body)){
                $response->body=json_decode($response->body);
            } 
        	if ($response->body->errcode != 0){
                var_dump('https://oapi.dingtalk.com/chat/get?'.$param);
            	var_dump($response->body);
            	exit;
        	}

            return $response->body;

        
        
	}
}