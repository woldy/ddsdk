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

            $response = Request::get('https://oapi.dingtalk.com/chat/get?'.$param)->TimeoutIn(10);
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


    public static function addToChat($ACCESS_TOKEN,$user_ids,$chat_id){
        // $chat=self::getChat($ACCESS_TOKEN,$chat_id);
        // if($chat['errcode']==0){
        //     $chat=$chat['info'];
        // }else{
        //     return $chat;
        // }
        $ids=$user_ids;
        if(!is_array($ids)){
            $ids=explode(',', $ids);
        }
        $param=[
            "chatid"=> $chat_id,
            "add_useridlist"=>$ids
        ];
        $response = Request::post('https://oapi.dingtalk.com/chat/update?access_token='.$ACCESS_TOKEN)
						->TimeoutIn(10)
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
        // if ($response->body->errcode != 0){
        //      var_dump($response->body);
        //     exit;
        // }
        return $response->body;

    }

    public static function createChat($ACCESS_TOKEN,$ids,$chat_title='噗~'){
        if(!is_array($ids)){
            $ids=explode(',', $ids);
        }
        $param=[
            "name"=> $chat_title,
            "owner"=> $ids[0],
            "useridlist"=>$ids
        ];

        //var_dump($param);
        $response = Request::post('https://oapi.dingtalk.com/chat/create?access_token='.$ACCESS_TOKEN)
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
        // if ($response->body->errcode != 0){
        //      var_dump($response->body);
        //     exit;
        // }
        return $response->body;
    }
}
