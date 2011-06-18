<?php
/**
 * Fetch URL class
 *
 * Fetch a page by url
 *
 * @author Han Lin Yap < http://zencodez.net/ >
 * @copyright 2011 zencodez.net
 * @license http://creativecommons.org/licenses/by-sa/3.0/
 * @package fetch_url
 * @version 1.2 - 2011-06-18
 */
class Fetch_url {
	public $header;
	public $source;
	public $error;
	
	function __construct($url, $posts=null, $save_cookie_path=null, $new_session=null, $user_agent=null, $port=null, $auth=null) {
		// nollstÃ¤ller header
		$this->header = '';
		$this->error = '';

		if ($save_cookie_path) {
			// Skapar en cookie fil om den inte existerar
			if (!file_exists($save_cookie_path)) {
				$handle = @fopen($save_cookie_path, 'w');
				if (!$handle) {
					$this->error = 'Cannot create cookie file';
					return;
				}
				fclose($handle);
				chmod($save_cookie_path, 0777);
			}
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); // set url to get 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable 
		curl_setopt($ch, CURLOPT_TIMEOUT, 9); // times out after 9s 
		curl_setopt($ch, CURLOPT_HEADER, 0); // output header

		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(__class__,'header_callback'));
		
		if ($user_agent) {
			curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		}
		
		if ($new_session) {
			curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
		}
		
		if ($port) {
			curl_setopt($ch, CURLOPT_PORT, $port);
		}
		
		if ($auth) {
			curl_setopt($ch, CURLOPT_USERPWD, $auth[0] . ":" . $auth[1]);    
		}
		// if $_POST
		if ($posts) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
		} 
		if ($save_cookie_path) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $save_cookie_path); // spara
			curl_setopt($ch, CURLOPT_COOKIEFILE, $save_cookie_path); // hÃ¤mta
		}
		$this->source = curl_exec($ch);
		
		if ($curl_error = curl_error($ch)) {
			$this->error = 'CURL error : ' . $curl_error;
			return;
		}
		
		if ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
			$this->error = 'HTTP code is ' . $http_code;
		}
		
		curl_close($ch);
	}
	
	function header_callback($ch, $data) {
		$this->header .= $data;
		return strlen($data);
	}
	
	public function get_header($type) {
		if (!strstr($this->header,$type)) return false;
		$header = nl2br(strstr($this->header,$type));
		$header = explode("<br />",$header);
		return $header[0];
	}
}
?>