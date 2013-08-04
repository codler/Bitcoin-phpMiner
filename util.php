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

class Util {
	static function zeroFill($a, $b) {
		$z = hexdec(80000000);
		if ($z & $a) {
			$a = ($a>>1);
			$a &= (~$z & 0xffffffff);
			$a |= 0x40000000;
			$a = ($a>>($b-1));
		} else {
			$a = ($a>>$b);
		}
		return $a;
	}

	// needed for 64bits
	static function uint32_left_shift($a, $b) {
		// check if negative
		#if (hexdec(80000000) & $a) {
		#	return ~(~($a << $b) & 0xffffffff);
		#}
		$c = $a << $b & 0xffffffff;
		if (hexdec(80000000) & $c) {
			$c = ~$c ^ 0xffffffff;
		}
		return $c;
	}

	static function uint32_array_to_hex($arr) {
		# PHP 5.3+
		#return implode('', array_map(function ($a) { return str_pad(dechex($a), 8, '0', STR_PAD_LEFT); }, $arr));
		# PHP 5.2
		$s = '';
		foreach($arr AS $a) {
			$s .= str_pad(substr(dechex($a), -8), 8, '0', STR_PAD_LEFT);
		}
		return $s;
	}
	
	static function hex_to_uint32_array($h) {
		return array_map('hexdec', str_split($h, 8));
	}
	
	static function reverse_bytes_in_word($w) {
		$a = (Util::uint32_left_shift($w, 24) & 0xff000000) | (Util::uint32_left_shift($w, 8) & 0x00ff0000) | ((Util::zeroFill($w, 8)) & 0x0000ff00) | ((Util::zeroFill($w, 24)) & 0x000000ff);
		// 64bits
		if (hexdec(80000000) & $a) {
			$a = ~$a ^ 0xffffffff;
		}
		return $a;
	}
	
	static function reverse_bytes_in_words($words) {
		$reversed = array();
		foreach($words AS $word) {
			$reversed[] = self::reverse_bytes_in_word($word);
		}
		return $reversed;
	}
	
	static function from_pool_string($hex) {
		return self::reverse_bytes_in_words(self::hex_to_uint32_array($hex));
	}
	
	static function to_pool_string($data) {
		return self::uint32_array_to_hex(self::reverse_bytes_in_words($data));
	}
}

?>