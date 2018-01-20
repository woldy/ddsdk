<?php
namespace Woldy\ddsdk\Components;
use Httpful\Request;
use Woldy\ddsdk\Components\Util;
class App{
	public static function getApp($ACCESS_TOKEN,$agentId){
			$param=array(
        		  'agentId' =>$agentId,
        		  'access_token'=>$ACCESS_TOKEN
        	   );

            $response = Request::post('https://oapi.dingtalk.com/microapp/visible_scopes?access_token='.$ACCESS_TOKEN)
            ->body(json_encode($param),'json')
            ->sends('application/json')
						->TimeoutIn(10);
            $response=Util::try_http_query($response);



            return $response->body;
		}


	public static function setApp($ACCESS_TOKEN,$app){

			$app['access_token']=$ACCESS_TOKEN;
            $response = Request::post('https://oapi.dingtalk.com/microapp/set_visible_scopes?access_token='.$ACCESS_TOKEN)
            ->body(json_encode($app),'json')
            ->sends('application/json')
						->TimeoutIn(10);
            $response=Util::try_http_query($response);

            if ($response->hasErrors()){
            	// var_dump($response);
            	// exit;
        	}
            if(!is_object($response->body)){
                $response->body=json_decode($response->body);
            }

        	if ($response->body->errcode != 0){
              //   var_dump('https://oapi.dingtalk.com/microapp/set_visible_scopes?access_token=ACCESS_TOKEN');
            	// var_dump($response);
            	// exit;
        	}

          return $response->body;
		}
}
