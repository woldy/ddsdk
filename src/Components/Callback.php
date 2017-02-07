<?php
namespace Woldy\ddsdk\Components;
use Cache;  
use Storage;
use Httpful\Request;
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
            ->sends('application/json')
            ->send();
        if ($response->hasErrors()){
            var_dump($response);
            exit;
        }
        if(!is_object($response->body)){
            $response->body=json_decode($response->body);
        }   
        return $response->body; 		
	}

	public function get(){

	}

	public function update(){
		
	}

}