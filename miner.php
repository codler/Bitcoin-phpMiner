<?php 
/**
 * @author Han Lin Yap < http://zencodez.net/ >
 * @copyright 2013 zencodez.net
 * @license http://creativecommons.org/licenses/by-sa/3.0/
 * @package Miner
 * @version 1.0.1 - 2013-08-04
 *
 * Feel free to donate: 1NibBDZPvJCm568CZMnJUBJoPyUhW7aSag
 */
 
require_once(dirname(__file__) . '/sha256.php');
require_once(dirname(__file__) . '/util.php');
require_once(dirname(__file__) . '/fetch_url.class.php');

class BitpitMiner extends Miner {
	function __construct($client, $domain) {
		parent::__construct();
		$this->client = $client;
		$this->domain = $domain;
	}

	function getwork($d=null) {
		$query = array(
			'client_id' => $this->client,
			'domain' 	=> $this->domain,
		);
		$url = 'http://api.bitp.it/work?' . http_build_query($query);
		if ($this->hash_rate > 0) {
			$url .= '&hash_rate=' .$this->hash_rate . '&hash_count=' . $this->hash_count;
		}
		$raw = new Fetch_url($url, $d, null, null, 'miner/1.0');
		
		if ($d) {
			$data = json_decode($raw->source, false);
			return json_encode(array('result' => $data));
		}
		return $raw->source;
	}
	
	function parse($raw) {
		$data = json_decode($raw, false);
		$this->midstate = Util::from_pool_string($data->midstate);
		$this->half = Util::from_pool_string(substr($data->data, 0, 128));
		$this->data = Util::from_pool_string(substr($data->data, 128, 128));
		$this->hash1 = Util::from_pool_string($data->hash1);
		$this->target = Util::from_pool_string($data->target);
		$this->nonce = $data->first_nonce;
		if (!$this->nonces) $this->nonces = 0xffffffff;
		$this->nonces = min($this->nonces, $data->last_nonce - $data->first_nonce, 100);
	}
}

class Miner {
	function __construct($host='', $port=80, $username='', $password='') {
		$this->nonce = 0;
		
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
	}
	
	/*
	 * @param string 
	 */
	function getwork($d=null) {
		$params = array(
			'id'	 => 'json',
			'method' => 'getwork',
			'params' => array(),
		);
	
		if ($d) {
			$params['params'] = $d;
		}
		
		$raw = new Fetch_url($this->host, json_encode($params), null, null, 'miner/1.0', $this->port, array($this->username, $this->password));
		if ($raw->error) {
			throw new Exception('curl:' . $raw->error);
		}
		//echo $raw->source; die();
		return $raw->source;
	}
	
	/*
	 * Read "getwork"
	 */
	function parse($raw) {
		$data = json_decode($raw, false);
		
		$this->midstate = Util::from_pool_string($data->result->midstate);
		$this->half = Util::from_pool_string(substr($data->result->data, 0, 128));
		$this->data = Util::from_pool_string(substr($data->result->data, 128, 128));
		$this->hash1 = Util::from_pool_string($data->result->hash1);
		$this->target = Util::from_pool_string($data->result->target);
	}
	
	/*
	 * @param int $iterations Max number of tries
	 * @return mixed Returns array if found, otherwise null.
	 */
	function find($iterations = 0xffffffff) {
		$ret = null;
		while ($this->nonce < 0xffffffff && $iterations--) {
			$this->data[3] = $this->nonce;
			$h0 = Sha256::hash($this->midstate, $this->data);
			for($j = 0; $j < 8; $j++) {
				$this->hash1[$j] = $h0[$j];
			}
			$h = Sha256::hash($this->hash1);
			if ($h[7] == 0) {
				foreach($this->data AS $d) {
					$this->half[] = $d;
				}
				$ret = $this->half;
				break;
			}
			$this->nonce++;
			
			#if ($this->nonce % 100 == 0) {
			#	echo '|' . $this->nonce . '|';
			#	if ($this->nonce % 1000 === 0) echo '<br>';
			#	ob_flush();
			#	flush();
			#}
		}
		return $ret;
	}
	
	
	function run($keep_alive=60, $sleep=1, $nonces = 0xffffffff, $silence = false) {
		$this->nonces = $nonces;
		$runtime = intval($keep_alive);
		$eta = microtime(true);
	
		do {
			if (function_exists('sys_getloadavg')) {
				$load = sys_getloadavg();
				if ($load[0] >= 0.89) {
					sleep($check_interval);
				} elseif($load[0] >= 1) {
					die();
				}
			}
			
			$start_time = microtime(true);
			
			$work = $this->getwork();
			$this->parse($work);
			$found = $this->find($this->nonces);
			if ($found) {
				$q = 100;
				while($q--) {
					$found = Util::to_pool_string($found);
					$success = $this->getwork($found);
					$success = json_decode($success, false);
					if ($success->result) {
						// found
						#if (!$silence) {
							echo $found;
						#	ob_flush();
						#	flush();
						#}
						break;
					}
					sleep(1);
				}
			}
			
			$end_time = microtime(true);

			$this->hash_rate = ($nonce - $first_nonce) / ($end_time - $start_time) * 1000;
			$this->hash_count = $this->hash_rate * $check_interval;
			
			$runtime = intval($keep_alive - (microtime(true) - $eta));
			sleep(intval($runtime % $sleep));
		} while ($runtime > 0);
	}
}
?>