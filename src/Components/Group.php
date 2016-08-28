<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class group{
	public static function getAll2($ACCESS_TOKEN){
        	$param=http_build_query(
        		array(
        			'access_token'=>$ACCESS_TOKEN
        		)
        	);
 			
            $response = Request::get('https://oapi.dingtalk.com/department/list?'.$param)->send();
            if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        	}
        	if ($response->body->errcode != 0){
            	var_dump($response->body);
            	exit;
        	}
            $result = $response->body;
 
        return  $result;
	}

	public static function getAll($ACCESS_TOKEN){
        	$param=http_build_query(
        		array(
        			'access_token'=>$ACCESS_TOKEN,
        			'department_id'=>1
        		)
        	);
 			
            $response = Request::get('https://oapi.dingtalk.com/user/list?'.$param)->send();
            if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        	}
        	if ($response->body->errcode != 0){
            	var_dump($response->body);
            	exit;
        	}
            $result = $response->body;
 
        return  $result;
	}

}