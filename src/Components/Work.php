<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class Work{
	public static function getAttend($ACCESS_TOKEN,$userid,$from='',$to=''){
						if(empty($from) || empty($to)){
							$from=date("Y-m-d").' 00:00:00';
							$to=date("Y-m-d").' 23:59:59';
						}

            $param=[
        		  'userId' =>$userid,
							'workDateFrom'=>$from,
							'workDateTo'=>$to,
        		  'access_token'=>$ACCESS_TOKEN
        	   ];


						$response = Request::post('https://oapi.dingtalk.com/attendance/list?access_token='.$ACCESS_TOKEN)
								->TimeoutIn(10)
		            ->body(json_encode($param))
		            ->sends('application/json')
		            ->send();


            if ($response->hasErrors()){

        		}
            if(!is_object($response->body)){
                $response->body=json_decode($response->body);
            }
        		if ($response->body->errcode != 0){

        		}

            return $response->body;
	}


	public static function putAttend($ACCESS_TOKEN,$data){
		$seed='checkrecordforxier';

		$param=array(
				'access_token' =>$ACCESS_TOKEN,
				'data'=>$data,
				'sign'=>md5($seed.$data),
		);

		//var_dump($param);

		$response = Request::post('https://oapi.dingtalk.com/attendance/uploadCheckRecordForXier?access_token='.$ACCESS_TOKEN)
		->TimeoutIn(10)
		->body(json_encode($param),'json')
		->sends('application/json')
		->send();

		if ($response->hasErrors()){
				// var_dump($response);
				// exit;
		}

		return $response->body;
	}
}
