<?php
namespace Woldy\ddsdk\Components;
use Woldy\ddsdk\Components\Token;
use Woldy\ddsdk\Components\Message;
use Woldy\ddsdk\Components\Contacts;
use Woldy\ddsdk\Components\Group;
use Log;
class dThreads extends \Thread{
	public $result='';
	public $runing=true;
	public $func='';
	public function __construct($func,$param){
		$this->func=$func;
		$this->param=$param;
	}

	public function run() {
		//echo 'ok';
		$func=$this->func;
		$this->result=$func($this->param);
		$this->runing=false;
	}
}
