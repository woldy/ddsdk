<?php
namespace Woldy\ddsdk;
use Illuminate\Config\Repository;
use Cache;
use Httpful\Request;
use Woldy\ddsdk\Components\Work;
use App\Models\ISV\IsvConfigModel;
use App\Models\ISV\IsvCorpModel;
use App\Models\ISV\IsvSuiteModel;
use Log;
class Isv{

	//根据suite_key获取应用套件
	public static function get_suite($suite_key){
		$suite=IsvSuiteModel::where('suite_key','=',$suite_key)->first();
		return $suite;
	}

	//根据suite_key获取suite_token
	public static function get_suite_token($suite_key){
		$isv_suite_token = Cache::get('isv_suite_token_'.$suite_key);
		if (!$isv_suite_token){
			$suite=self::get_suite($suite_key);
			$param=[
					"suite_key"=>$suite_key,
					"suite_secret"=>$suite->suite_secret,
					"suite_ticket"=>$suite->suite_ticket
			];
			$response = Request::post('https://oapi.dingtalk.com/service/get_suite_token')
			->body(json_encode($param),'json')
			->sends('application/json')
			->TimeoutIn(10)
			->send();
			$isv_suite_token = $response->body->suite_access_token;
			Cache::put('isv_suite_token_'.$suite_key, $isv_suite_token,60);
		}
		return $isv_suite_token;
	}


	public static function Callback($suite_key,$msg){
		switch ($msg['EventType']) {
			case 'suite_ticket':
				return self::upTicket($suite_key,$msg);
				break;
			case 'tmp_auth_code':
				return self::Activate($suite_key,$msg);
				break;
			case 'check_update_suite_url':
					return self::upSuiteUrl($suite_key,$msg);
					break;
			default:
				Log::info($suite_key.'---'.$msg['EventType']);
				break;
		}
		return false;
	}

	//更新套件
	public static function upSuiteUrl($suite_key,$msg){
		return true;
	}

	//企业激活套件
	public static function Activate($suite_key,$msg){
		$corp=self::get_permanent_code($suite_key,$msg);
		return self::activate_suite($suite_key,$corp);
	}

	public static function upTicket($suite_key,$msg){
		IsvSuiteModel::where('suite_key','=',$suite_key)->first()
			->update([
				'suite_ticket'=>$msg['SuiteTicket']
			]);
	}

	public static function putAttend($corp_token,$data){
      return Work::putAttend($corp_token,$data);
	}

	public static function get_permanent_code($suite_key,$msg){
		$access_token=self::get_suite_token($suite_key);

		$param=[
				"tmp_auth_code"=>$msg['AuthCode'],
		];
		$response = Request::post('https://oapi.dingtalk.com/service/get_permanent_code?suite_access_token='.$access_token)
		->body(json_encode($param),'json')
		->sends('application/json')
		->TimeoutIn(10)
		->send();

		$corp=[
			'corp_id'=>$response->body->auth_corp_info->corpid,
			'corp_name'=>$response->body->auth_corp_info->corp_name,
			'permanent_code'=>$response->body->permanent_code,
			'ch_permanent_code'=>$response->body->ch_permanent_code??'',
			'suite_key'=>$suite_key
		];

		$m_corp=IsvCorpModel::where('corp_id',$corp['corp_id'])->first();
		if(!empty($m_corp)){
			$m_corp->update($corp);
		}else{
			IsvCorpModel::create($corp);
		}

		return $corp;
	}

	public static  function activate_suite($suite_key,$corp){
		$access_token=self::get_suite_token($suite_key);
		$param=[
				"suite_key"=>'suitevorubtzcjxo4481g',
				'auth_corpid'=>$corp['corp_id'],
				'permanent_code'=>$corp['permanent_code']
		];
		$response = Request::post('https://oapi.dingtalk.com/service/activate_suite?suite_access_token='.$access_token)
		->body(json_encode($param),'json')
		->sends('application/json')
		->TimeoutIn(10)
		->send();

		return true;
	}

	public static function get_corp_token($suite_key,$corp_id){
		$corp=IsvCorpModel::where('corp_id',$corp_id)->where('suite_key',$suite_key)->first();
		$access_token=self::get_suite_token($suite_key);
		$param=[
				"auth_corpid"=>$corp_id,
				'permanent_code'=>$corp->permanent_code
		];
		$response = Request::post('https://oapi.dingtalk.com/service/get_corp_token?suite_access_token='.$access_token)
		->body(json_encode($param),'json')
		->sends('application/json')
		->TimeoutIn(10)
		->send();

		$corp_token=$response->body->access_token;
		return $corp_token;
	}

}
