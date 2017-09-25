<?php
namespace Woldy\ddsdk;
use Illuminate\Config\Repository;
use Cache;
use Httpful\Request;
use Woldy\ddsdk\Components\Work;
use App\Models\ISV\IsvConfigModel;
use App\Models\ISV\IsvCorpModel;
class Isv{
	public static $ACCESS_TOKEN;
	public function __construct(Repository $config){
		self::$ACCESS_TOKEN = self::get_suite_token();
		//var_dump(self::$ACCESS_TOKEN);
	}



	public static function get_suite_token(){
		$accessToken = Cache::get('isv_access_token');
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
			Cache::put('isv_access_token', $accessToken,60);
		}
		return $accessToken;
	}

	public static function Callback($msg){
		Log::info('00000');
		switch ($msg['EventType']) {
			case 'suite_ticket':
				return self::upTicket($msg);
				break;
			case 'tmp_auth_code':
				return self::Active($msg);
				break;
			default:
				Log::info($msg['EventType']);
				break;
		}
		return false;
	}

	public static function Active($msg){
		Log::info('1111');
		$corp_info=self::get_permanent_code($msg);
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
		Log::info('2222222');
		$param=[
				"tmp_auth_code"=>$msg['AuthCode'],
		];
		$response = Request::post('https://oapi.dingtalk.com/service/get_permanent_code?suite_access_token='.self::$ACCESS_TOKEN)
		->body(json_encode($param),'json')
		->sends('application/json')
		->TimeoutIn(10)
		->send();

		$corp=[
			'corpid'=>$response->body->auth_corp_info->corpid,
			'corp_name'=>$response->body->auth_corp_info->corp_name,
			'permanent_code'=>$response->body->auth_corp_info->permanent_code,
			'ch_permanent_code'=>$response->body->auth_corp_info->ch_permanent_code,
		];

		IsvCorpModel::create($corp);

		Log::info(json_encode($corp));

		return $corp;
	}

}
