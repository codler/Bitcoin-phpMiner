<?php
require_once(dirname(__file__) . '/miner.php');

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents("php://input");
$data = json_decode($raw, false);
$miner = new BitpitMiner(4384, 'zencodez.net');
if (count($data->params)==0) {
	$work = $miner->getwork();
	$data = json_decode($work, false);
	echo json_encode(array('result' => $data, 'error' => null, 'id' => 'json'));
} else {
	$result = $miner->getwork($data->params[0]);
	echo json_encode(array('result' => true, 'error' => null, 'id' => 'json'));
	#echo '{"result":true,"error":null,"id":"json"}';
}
?>