<?php
/**
 * @author Han Lin Yap < http://zencodez.net/ >
 * @copyright 2011 zencodez.net
 * @license http://creativecommons.org/licenses/by-sa/3.0/
 * @package Miner
 * @version 1.0 - 2011-06-18
 *
 * Feel free to donate: 1NibBDZPvJCm568CZMnJUBJoPyUhW7aSag
 */
require_once('simpletest/autorun.php');
require_once('miner.php');

class TestMinerMod extends Miner {
	function getwork($d=null) {
		if ($d && $d == TestMiner::$result) {
			return '{"result":true,"error":null,"id":"json"}';
		} elseif ($d === null) {
			return TestMiner::$raw;
		}
	}
}

class TestBitpitMinerMod extends BitpitMiner {
	function getwork($d=null) {
		if ($d && $d == TestMiner::$result) {
			return json_encode(array('result' => true));
		} elseif ($d === null) {
			$data = json_decode(TestMiner::$raw, false);
			$data = $data->result;
			$data->first_nonce = 303620454;
			$data->last_nonce = 0xffffffff;
			return json_encode($data);
		}
	}
}

class TestMiner extends UnitTestCase {

	function testZeroFill() {
		$this->assertEqual(Util::zeroFill(-9, 2), 1073741821);
	}
	
	function testAdd() {		
		$this->assertEqual(Sha256::add(1748768122, 2104669558), -441529616);
	}
	
	function testHash() {
		$data = array(603604794, -1992726684, 1050533917, -2049282207, 378026414, -38779290, -25761049, -551699343);
	
		$result = array(31505934, 205022762, -1518051379, -194223946, 2000539338, 1835853132, -99974255, 477201633);
		
		$this->assertEqual(Sha256::hash($data), $result);
	}
	
	function testUint32LeftShift() {
		$this->assertEqual(Util::uint32_left_shift(51164975, 24), 788529152);
		$this->assertEqual(~(~(-51164975 << 24) & 0xffffffff), -788529152);
		$this->assertEqual(Util::uint32_left_shift(-51164975, 24), -788529152);
	}
	
	function testReverseBytesInWord() {
	
		$this->assertEqual(Util::reverse_bytes_in_word(-783748100), -51164975);
	}

	public static $raw = '{"result":{"midstate":"36caf078fcf348d1b56f301f1e6cc1d58d00bf85cb25f304ac24af2eb1935bc8","data":"000000013727f594b69bb354758b6c4dcf055716edf831377a3d330100000c0f00000000bd65d2f01b6633212b7da6d608e0a60fc3016a99acaa11bc6aca3502ea0b1d634dfa37b21a13218500000000000000800000000000000000000000000000000000000000000000000000000000000000000000000000000080020000","hash1":"00000000000000000000000000000000000000000000000000000000000000000000008000000000000000000000000000000000000000000000000000010000","target":"ffffffffffffffffffffffffffffffffffffffffffffffffffffffff00000000"},"error":null,"id":"json"}';

	public static $result = '000000013727f594b69bb354758b6c4dcf055716edf831377a3d330100000c0f00000000bd65d2f01b6633212b7da6d608e0a60fc3016a99acaa11bc6aca3502ea0b1d634dfa37b21a13218566e11812000000800000000000000000000000000000000000000000000000000000000000000000000000000000000080020000';
	
	function testUint32ArrayToHex() {
		$data = array(-737688356, -2045301235, 811774914, 678038070, 851948952, -1884913375, 2010173980, 1411895477, -2147483648, 0, 0, 0, 0, 0, 0, 256);
		
		$result = 'd407c4dc86172e0d3062b3c2286a0a3632c7b5988fa6812177d0d21c5427d0b58000000000000000000000000000000000000000000000000000000000000100';
		
		$this->assertEqual(Util::uint32_array_to_hex($data), $result);
	
	}
	
	function testHexToUint32Array() {
		$data = 'd407c4dc86172e0d3062b3c2286a0a3632c7b5988fa6812177d0d21c5427d0b58000000000000000000000000000000000000000000000000000000000000100';
		
		$result = array(3557278940, 2249666061, 811774914, 678038070, 851948952, 2410053921, 2010173980, 1411895477, 2147483648, 0, 0, 0, 0, 0, 0, 256);
		
		$this->assertEqual(Util::hex_to_uint32_array($data), $result);
	}
	
	function testFromPoolString() {
		$data = '36caf078fcf348d1b56f301f1e6cc1d58d00bf85cb25f304ac24af2eb1935bc8';
		
		$result = array(2029046326, -783748100, 523268021, -708744162, -2051080051, 83043787, 783230124, -933522511);
		
		$this->assertEqual(Util::from_pool_string($data), $result);
	}
	
	function testFindNonce() {
		$miner = new Miner();
		$miner->parse(self::$raw);
		$miner->nonce = 303620454;
		
		$this->assertEqual($miner->find(1), Util::from_pool_string(self::$result));
	}
		
	function testMinerMod() {
		$miner = new TestMinerMod();
		$miner->nonce = 303620454;
		ob_start();
		$miner->run(1,1,1);
		$result = ob_get_contents();
		ob_end_clean();
		$this->assertEqual($result, self::$result);
	}
	
	function testBitpitFindNonce() {
	
		$miner = new TestBitpitMinerMod(4384, 'zencodez.net');
		$data = $miner->getwork();
		$miner->parse($data);
		
		$this->assertEqual($miner->find(1), Util::from_pool_string(self::$result));
		
	}

}
?>