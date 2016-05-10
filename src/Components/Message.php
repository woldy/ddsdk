<?php
namespace Woldy\ddsdk\Components;
use Cache;
use Httpful\Request;
class message{
	private $AgentID;
	private $CorpID;
	private $CorpSecret;
	private $SSOSecret;

	function __construct($config){
		$this->AgentID=$config->get('dd')['AgentID'];
 		$this->CorpID=$config->get('dd')['CorpID'];
 		$this->CorpSecret=$config->get('dd')['CorpSecret'];
 		$this->SSOSecret=$config->get('dd')['SSOSecret'];
	}

	public function sendMessageByCode($code){

	}
}