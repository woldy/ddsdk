<?php
namespace Woldy\ddsdk\Components;
use Cache;  
use Storage;
use Httpful\Request;
class dCrypt{
	private $m_token;
	private $m_encodingAesKey;
	private $m_suiteKey;

	public  $OK = 0;
	
	public $IllegalAesKey = 900004;
	public $ValidateSignatureError = 900005;
	public $ComputeSignatureError = 900006;
	public $EncryptAESError = 900007;
	public $DecryptAESError = 900008;
	public $ValidateSuiteKeyError = 900010;

	public function __construct($token, $encodingAesKey, $suiteKey){
		$this->m_token = $token;
		$this->m_encodingAesKey = $encodingAesKey;
		$this->m_suiteKey = $suiteKey;
	}

	
	public function EncryptMsg($plain, $timeStamp, $nonce, &$encryptMsg)
	{
 
		$array = $this->encrypt($this->m_encodingAesKey,$plain, $this->m_suiteKey);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}

		if ($timeStamp == null) {
			$timeStamp = time();
		}
		$encrypt = $array[1];

 
		$array = $this->getSHA1($this->m_token, $timeStamp, $nonce, $encrypt);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}
		$signature = $array[1];

		$encryptMsg = array(
			"msg_signature" => $signature,
			"encrypt" => $encrypt,
			"timeStamp" => $timeStamp,
			"nonce" => $nonce
		);
		return $this->OK;
	}


	public function DecryptMsg($signature, $timeStamp = null, $nonce, $encrypt, &$decryptMsg)
	{
		if (strlen($this->m_encodingAesKey) != 43) {
			return $this->IllegalAesKey;
		}
 
		if ($timeStamp == null) {
			$timeStamp = time();
		}


		$array = $this->getSHA1($this->m_token, $timeStamp, $nonce, $encrypt);
		$ret = $array[0];

		if ($ret != 0) {
			return $ret;
		}

		$verifySignature = $array[1];
		if ($verifySignature != $signature) {
			return ErrorCode::$ValidateSignatureError;
		}

		$result = $this->decrypt($this->m_encodingAesKey,$encrypt, $this->m_suiteKey);
		if ($result[0] != 0) {
			return $result[0];
		}
		$decryptMsg = $result[1];

		return $this->OK;
	}


	public function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
	{
		try {
			$array = array($encrypt_msg, $token, $timestamp, $nonce);
			sort($array, SORT_STRING);
			$str = implode($array);
			return array($this->OK, sha1($str));
		} catch (Exception $e) {
			print $e . "\n";
			return array($this->ComputeSignatureError, null);
		}
	}

	function encode($text)
	{
		$block_size = 32;
		$text_length = strlen($text);
		$amount_to_pad = $block_size - ($text_length % $block_size);
		if ($amount_to_pad == 0) {
			$amount_to_pad = $block_size;
		}
		$pad_chr = chr($amount_to_pad);
		$tmp = "";
		for ($index = 0; $index < $amount_to_pad; $index++) {
			$tmp .= $pad_chr;
		}
		return $text . $tmp;
	}

	function decode($text)
	{
		$pad = ord(substr($text, -1));
		if ($pad < 1 || $pad > 32) {
			$pad = 0;
		}
		return substr($text, 0, (strlen($text) - $pad));
	}


	public function encrypt($key,$text, $corpid)
	{
		$key=base64_decode($key . "=");
		try {
			//获得16位随机字符串，填充到明文之前
			$random = $this->getRandomStr();
			$text = $random . pack("N", strlen($text)) . $text . $corpid;
			// 网络字节序
			$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv = substr($key, 0, 16);
			//使用自定义的填充方式对明文进行补位填充
			$text = $this->encode($text);
			mcrypt_generic_init($module, $key, $iv);
			//加密
			$encrypted = mcrypt_generic($module, $text);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);

			//print(base64_encode($encrypted));
			//使用BASE64对加密后的字符串进行编码
			return array($this->OK, base64_encode($encrypted));
		} catch (Exception $e) {
			print $e;
			return array($this->EncryptAESError, null);
		}
	}

	public function decrypt($key,$encrypted, $corpid)
	{
		$key=base64_decode($key . "=");
		try {
			$ciphertext_dec = base64_decode($encrypted);
			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv = substr($key, 0, 16);
			mcrypt_generic_init($module, $key, $iv);

			$decrypted = mdecrypt_generic($module, $ciphertext_dec);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
		} catch (Exception $e) {
			return array($this->DecryptAESError, null);
		}


		try {

			$result = $this->decode($decrypted);
			//去除16位随机字符串,网络字节序和AppId
			if (strlen($result) < 16)
				return "";
			$content = substr($result, 16, strlen($result));
			$len_list = unpack("N", substr($content, 0, 4));
			$xml_len = $len_list[1];
			$xml_content = substr($content, 4, $xml_len);
			$from_corpid = substr($content, $xml_len + 4);
		} catch (Exception $e) {
			print $e;
			return array($this->DecryptAESError, null);
		}
		if ($from_corpid != $corpid)
			return array($this->ValidateSuiteKeyError, null);
		return array(0, $xml_content);

	}

	function getRandomStr()
	{

		$str = "";
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($str_pol) - 1;
		for ($i = 0; $i < 16; $i++) {
			$str .= $str_pol[mt_rand(0, $max)];
		}
		return $str;
	}	
}