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
		            ->sends('application/json');
		            $response=dd::try_http_query($response);


            if ($response->hasErrors()){

        		}
            if(!is_object($response->body)){
                $response->body=json_decode($response->body);
            }
        		if ($response->body->errcode != 0){

        		}

            return $response->body;
	}


	public static function putAttend($ACCESS_TOKEN,$content){
		$seed='checkrecordforxier';

		$param=array(
				'access_token' =>$ACCESS_TOKEN,
				'content'=>$content,
				'sign'=>md5($seed.$content),
		);



		$response = Request::post('https://oapi.dingtalk.com/attendance/uploadCheckRecordForXier?access_token='.$ACCESS_TOKEN)
		->TimeoutIn(10)
		->body(json_encode($param),'json')
		->sends('application/json');
		$response=dd::try_http_query($response);

		if ($response->hasErrors()){
				// var_dump($response);
				// exit;
		}

		return $response->body;
	}


	/**
 * 获取部门签到记录
 * @param $accessToken
 * @param $departmentId
 * @param $startTime
 * @param $endTime
 * @return array|mixed|object|string
 */
		public static function getDepartmentSignLogs($accessToken, $departmentId, $startTime, $endTime)
		{
				$response = Request::get("https://oapi.dingtalk.com/checkin/record?access_token={$accessToken}&department_id={$departmentId}&start_time={$startTime}&end_time={$endTime}")
						->TimeoutIn(10)
						->sends('application/json');
					$response=dd::try_http_query($response);

				if ($response->hasErrors()){
						var_dump($response);
						exit;
				}

				if(!is_object($response->body)){
						$response->body = json_decode($response->body);
				}


				return $response->body;
		}
}
