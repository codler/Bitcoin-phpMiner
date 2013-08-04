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
class Sha256 {
	public static $K = array(
		0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
		0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
		0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
		0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
		0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
		0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
		0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
		0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2);

	static function rotr($x, $n) {
		return Util::zerofill($x, $n) | Util::uint32_left_shift($x, 32-$n);
	}
	static function shr($x, $n) {
		return Util::zerofill($x, $n);
	}
	static function add($x, $y) {
		$lsw = ($x & 65535) + ($y & 65535);
		$msw = ($x >> 16) + ($y >> 16) + ($lsw >> 16);
		return Util::uint32_left_shift($msw, 16) | $lsw & 65535;
	}

	static function extend_work($data) {
		$w = $data;
		for($i = 16; $i < 64; $i++) {
			$s0 = self::rotr($w[$i - 15], 7) ^ self::rotr($w[$i - 15], 18) ^ self::shr($w[$i - 15], 3);
			// 64bits
			if (hexdec(80000000) & $s0) $s0 = ~$s0 ^ 0xffffffff;
			$s1 = self::rotr($w[$i - 2], 17) ^ self::rotr($w[$i - 2], 19) ^ self::shr($w[$i - 2], 10);
			// 64bits
			if (hexdec(80000000) & $s1) $s1 = ~$s1 ^ 0xffffffff;
			$w[$i] = self::add($w[$i - 16], $s0);
			$w[$i] = self::add($w[$i], $w[$i - 7]);
			$w[$i] = self::add($w[$i], $s1);
		}
		return $w;
	}
	
	static function hash($midstate, $data=null) {
		// Remapping if data is null
		if ($data === null) {
			$data = $midstate;
			$midstate = array(0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a, 0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19);
		}
		
		$w = self::extend_work($data);
		$a = $midstate[0]; $b = $midstate[1]; $c = $midstate[2]; $d = $midstate[3];
		$e = $midstate[4]; $f = $midstate[5]; $g = $midstate[6]; $h = $midstate[7];

		for($i = 0; $i < 64; $i++) {
			$s0 = self::rotr($a, 2) ^ self::rotr($a, 13) ^ self::rotr($a, 22);
			$maj = $a & $b ^ $a & $c ^ $b & $c;
			$t2 = self::add($s0, $maj);
			$s1 = self::rotr($e, 6) ^ self::rotr($e, 11) ^ self::rotr($e, 25);
			$ch = $e & $f ^ ~$e & $g;
			$t1 = self::add($h, $s1);
			$t1 = self::add($t1, $ch);
			$t1 = self::add($t1, self::$K[$i]);
			$t1 = self::add($t1, $w[$i]);
			$h = $g;
			$g = $f;
			$f = $e;
			$e = self::add($d, $t1);
			$d = $c;
			$c = $b;
			$b = $a;
			$a = self::add($t1, $t2);
		}
		$midstate[0] = self::add($midstate[0], $a);
		$midstate[1] = self::add($midstate[1], $b);
		$midstate[2] = self::add($midstate[2], $c);
		$midstate[3] = self::add($midstate[3], $d);
		$midstate[4] = self::add($midstate[4], $e);
		$midstate[5] = self::add($midstate[5], $f);
		$midstate[6] = self::add($midstate[6], $g);
		$midstate[7] = self::add($midstate[7], $h);
		
		return $midstate;
	}
}
?>