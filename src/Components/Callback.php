<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Storage;
use Httpful\Request;
use DD;

class Callback{
	public static function reg_callback($accesstoken,$url,$token,$aes_key,$call_back_tag){
        $param=[
            "call_back_tag"=> $call_back_tag,
            "token"=>$token,
            "aes_key"=>$aes_key,
            "url"=>$url
        ];
        $response = Request::post('https://oapi.dingtalk.com/call_back/register_call_back?access_token='.$accesstoken)
            ->body(json_encode($param))
            ->sends('application/json');
        $response=dd::try_http_query($response);
        if ($response->hasErrors()){
            var_dump($response);
            exit;
        }
        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }
        return $response->body;
	}

	public static function fail_callback($accesstoken){

      $response = Request::get('https://oapi.dingtalk.com/call_back/get_call_back_failed_result?access_token='.$accesstoken)->TimeoutIn(10);
			$response=dd::try_http_query($response);
        if ($response->hasErrors()){
            	var_dump($response);
            	exit;
        }
        if(!is_object($response->body)){
                $response->body=json_decode($response->body);
        }
        if ($response->body->errcode != 0){
             //    var_dump('https://oapi.dingtalk.com/chat/get?'.$param);
            	// var_dump($response->body);
            	// exit;
        }

        return $response->body;

	}
	public function get(){

	}

	public static function up_callback($accesstoken,$url,$token,$aes_key,$call_back_tag){
        $param=[
            "call_back_tag"=> $call_back_tag,
            "token"=>$token,
            "aes_key"=>$aes_key,
            "url"=>$url
        ];
        $response = Request::post('https://oapi.dingtalk.com/call_back/update_call_back?access_token='.$accesstoken)
            ->body(json_encode($param))
            ->sends('application/json');
        $response=dd::try_http_query($response);
        if ($response->hasErrors()){
            var_dump($response);
            exit;
        }
        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }
        return $response->body;
	}
}
