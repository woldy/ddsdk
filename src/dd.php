<?php
namespace Woldy\ddsdk;
use Illuminate\Config\Repository;
class dd{
	static $config;
	static $version=0;
	public function __construct(Repository $config){
		self::$config = $config;
	}

	public static function test(){
		echo 'xxx';
	}
	// public static function getcss(){
	// }
	// public static function admin($tpl,$val=array()){
	// 	$val['version']=self::$version;
	// 	$config=self::getconf('admin_cfg');
	// 	$val['template']=$config['tpl_base'].'.'.$tpl;
	// 	$val=array_merge($val,$config);
	// 	$layout=$config['tpl_admin'].'.'.$config['tpl_layout'];
	//     return view($layout,$val);	  	
	// }
	// //获取模板配置
	// public static function getconf($option){
	// 	$conf=self::$config->get('tpl.'.$option);
	// 	return $conf;
	// }
	// public static function portal(){
	// }
} 