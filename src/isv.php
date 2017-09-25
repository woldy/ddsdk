<?php
namespace Woldy\ddsdk;
use Illuminate\Config\Repository;
use Cache;
use Httpful\Request;
use Woldy\ddsdk\Components\Work;
use App\Models\ISV\IsvConfigModel;
class Isv{
	public static $ACCESS_TOKEN;
	public function __construct(Repository $config){
		self::$ACCESS_TOKEN = self::get_suite_token();
		var_dump(self::$ACCESS_TOKEN);
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

			if(!is_object($response->body)){
					$response->body=json_decode($response->body);
			}
			$accessToken = $response->body->suite_access_token;
			Cache::put('isv_access_token', $accessToken,60);
		}
		return $accessToken;
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

	public static function get_permanent_code(){
		return true;
	}

}
