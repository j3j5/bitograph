<?php

class updatebitonic_Task {

	public function run($arguments) {

		$curl = New Curl;
		$curl->option('USERAGENT', 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)');
		$json_buy = $curl->simple_get('https://bitonic.nl/json/?part=rate_convert&check=btc&btc=1');
		$json_sell = $curl->simple_get('https://bitonic.nl/json/sell?part=offer&check=btc&btc=1');

		$buy = json_decode($json_buy, TRUE);
		$sell = json_decode($json_sell, TRUE);

		if(!isset($buy['euros'], $sell['euros'])) {
			echo 'Not a proper json for buy/sell: ' . PHP_EOL;
			var_dump($buy);
			var_dump($sell);
			exit;
		}

		$new_prices = array('timestamp' => time(), 'buy' => $buy['euros'], 'sell' => $sell['euros']);

		$result = Prices::update_prices('bitonic', $new_prices);
		if(!$result) {
			echo 'Something failed when updating.' . PHP_EOL;
		} else {
			echo 'Price uptaded. Buy: ' . $buy['euros'] . '€/฿; Sell: ' . $sell['euros'] . '€/฿;' . PHP_EOL;
		}
	}
}
