<?php
namespace Woldy\ddsdk;
use Illuminate\Config\Repository;
use Cache;
use Httpful\Request;
use Woldy\ddsdk\Components\Work;
use App\Models\ISV\IsvConfigModel;
use App\Models\ISV\IsvCorpModel;
use Log;
class Isv{
	public static $ACCESS_TOKEN;
	public function __construct(Repository $config){
		self::$ACCESS_TOKEN = self::get_suite_token();
		//var_dump(self::$ACCESS_TOKEN);
	}



	public static function get_suite_token(){
		$accessToken = Cache::get('isv_suite_token');
		if (!$accessToken){
			$param=[
					"suite_key"=>'suitevorubtzcjxo4481g',
					"suite_secret"=>'ay9xUkkuMNXC-c2lNJwkPHZjFVAew0MTcWqJOjJ5L3xVh_huEUWbH8URqt2JiywN',
					"suite_ticket"=>IsvConfigModel::where('key','=','SuiteTicket')->pluck('value')[0]
			];
			$response = Request::post('https://oapi.dingtalk.com/service/get_suite_token')
			->body(json_encode($param),'json')
			->sends('application/json')
			->TimeoutIn(10)
			->send();
			$accessToken = $response->body->suite_access_token;
			Cache::put('isv_suite_token', $accessToken,60);
		}
		return $accessToken;
	}

	public static function Callback($msg){
		switch ($msg['EventType']) {
			case 'suite_ticket':
				return self::upTicket($msg);
				break;
			case 'tmp_auth_code':
				return self::Activate($msg);
				break;
			default:
				Log::info($msg['EventType']);
				break;
		}
		return false;
	}

	public static function Activate($msg){
		$corp=self::get_permanent_code($msg);
		return self::activate_suite($corp);
	}

	public static function upTicket($msg){
		IsvConfigModel::where('key','=','SuiteTicket')
			->update([
				'value'=>$msg['SuiteTicket']
			]);
	}

	public static function putAttend($data){
      return Work::putAttend(self::$ACCESS_TOKEN,$data);
	}

	public static function get_permanent_code($msg){
		$param=[
				"tmp_auth_code"=>$msg['AuthCode'],
		];
		$response = Request::post('https://oapi.dingtalk.com/service/get_permanent_code?suite_access_token='.self::$ACCESS_TOKEN)
		->body(json_encode($param),'json')
		->sends('application/json')
		->TimeoutIn(10)
		->send();

		$corp=[
			'corp_id'=>$response->body->auth_corp_info->corpid,
			'corp_name'=>$response->body->auth_corp_info->corp_name,
			'permanent_code'=>$response->body->permanent_code,
			'ch_permanent_code'=>$response->body->ch_permanent_code??'',
		];

		$m_corp=IsvCorpModel::where('corp_id',$corp['corp_id'])->first();
		if(!empty($m_corp)){
			$m_corp->update($corp);
		}else{
			IsvCorpModel::create($corp);
		}

		return $corp;
	}

	public function activate_suite($corp){
		$param=[
				"suite_key"=>'suitevorubtzcjxo4481g',
				'auth_corpid'=>$corp['corp_id'],
				'permanent_code'=>$corp['permanent_code']
		];
		$response = Request::post('https://oapi.dingtalk.com/service/activate_suite?suite_access_token='.self::$ACCESS_TOKEN)
		->body(json_encode($param),'json')
		->sends('application/json')
		->TimeoutIn(10)
		->send();

		return true;
	}

}
