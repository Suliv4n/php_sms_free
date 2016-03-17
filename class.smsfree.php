<?php
class SMSFreeException extends Exception{

	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
	
}

class SMSFree{
	
	private static $ERRORS_MESSAGES = array(
		400 => 'Un des paramètres obligatoires est manquant.',
		402 => 'Trop de SMS ont été envoyés en trop peu de temps.',
		403 => 'Le service n\'est pas activé sur l\'espace abonné, ou login / clé incorrect.',
		500 => 'Erreur côté serveur. Veuillez réessayer ultérieurement.',
	);
	
	const SERVICE_SMS_URL = "https://smsapi.free-mobile.fr/sendmsg?user=%s&pass=%s&msg=%s";
	
	private $user;
	private $secret_key;
	
	public function __construct($user, $secret_key){
		$this->user = $user;
		$this->secret_key = $secret_key;
	}
	
	public function send_sms($content){
		if(!empty($content)){
			$curl = $this->init_curl($content);
			
			if(curl_exec($curl) === FALSE){
				$errno = curl_error($curl);
				$errmsg = curl_error($curl);
				
				throw new Exception($errmsg, $errno);
				
				curl_close($curl);
			}
			
			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			curl_close($curl);
			
			if($status != 200){
				throw new SMSFreeException(self::$ERRORS_MESSAGES[$status], $status);
			}
		}
	}
	
	private function init_curl($content){
		
		$key = urlencode($this->secret_key);
		$user = urlencode($this->user);
		$content = urlencode($content);
	
		$url = sprintf(self::SERVICE_SMS_URL, $user, $key, $content);
	
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);

		return $curl;
	}
	
}
