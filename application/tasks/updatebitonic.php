<?php

class updatebitonic_Task extends Update {

	public function __construct() {
		$this->url_buy = "https://bitonic.nl/json/?part=rate_convert&check=btc&btc=1";
		$this->url_sell = "https://bitonic.nl/json/sell?part=offer&check=btc&btc=1";
		$this->market = 'bitonic';
	}


	public function run($arguments) {
		parent::run_update();
	}

	protected function process_prices($json_buy, $json_sell){
		$buy = json_decode($json_buy, TRUE);
		$sell = json_decode($json_sell, TRUE);
		if(!isset($buy['euros'], $sell['euros'])) {
			if(!isset($buy['euros'], $sell['euros'])) {
				echo 'Not a proper json for buy/sell: ' . PHP_EOL;
				var_dump($buy);
				var_dump($sell);
				exit;
			}
		}
		$this->new_prices = array('timestamp' => time(), 'buy' => $buy['euros'], 'sell' => $sell['euros']);
	}

}
