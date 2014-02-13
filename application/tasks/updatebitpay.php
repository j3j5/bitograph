<?php

class Updatebitpay_Task extends Update{

	public function __construct() {
		$this->url_buy = "https://bitpay.com/api/rates";
		$this->url_sell = "";
		$this->market = 'bitpay';
	}

	public function run($arguments) {
		parent::run_update();
	}


	protected function process_prices($json_sell1, $json_sell2) {
		// On Bitpay you can just sell bitcoins
		$sell = json_decode($json_sell1, TRUE);
		// EUR rate is on the second position on the array
		///TODO: Search for the 'EUR' code and retrieve always
		if(!isset($sell[1]['code']) OR $sell[1]['code'] !== 'EUR') {
				echo 'Not a proper json for buy/sell: ' . PHP_EOL;
				var_dump($sell);
				exit;
		}

		$this->new_prices = array('timestamp' => time(), 'buy' => $sell[1]['rate'], 'sell' => $sell[1]['rate']);
	}
}
