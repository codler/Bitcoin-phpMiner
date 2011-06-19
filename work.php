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
require_once(dirname(__file__) . '/miner.php');

set_time_limit(0);

// Example
$host = 'pit.deepbit.net';
$port = 8332;
$user = 'username';
$pass = 'password';

$miner = new Miner($host, $port, $user, $pass);

# Param1: How many seconds it should run
# Param2: How long it should sleep after each getwork
# Param3: How many nonce it should try before taking next getwork
$miner->run();








/*
------------------------------
If you want to mine on bitp.it
*/
# Parameters: ClientID and Domain
$miner = new BitpitMiner(4384, 'zencodez.net');
$miner->run();
?>