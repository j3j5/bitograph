<?php

abstract class Update {

	protected $curl;

	protected $url_buy;
	protected $url_sell;

	protected $new_prices;

	protected $user_agent = 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)';

	protected $market;

	public function run_update() {

		$json_buy = $json_sell = '';

		if(empty($this->curl)) {
			$this->curl = New Curl;
		}

		$this->curl->option('USERAGENT', $this->user_agent);
		if(!empty($this->url_buy)) {
			$json_buy = $this->curl->simple_get($this->url_buy);
		}
		if(!empty($this->url_sell)) {
			$json_sell = $this->curl->simple_get($this->url_sell);
		}

		// These functions, on the child should fill up the $new_prices array
		$this->process_prices($json_buy, $json_sell);


		// Update the value on the DB
		if(!empty($this->new_prices)) {
			$result = Prices::update_prices($this->market, $this->new_prices);
			if(!$result) {
				echo 'Something failed when updating.' . PHP_EOL;
			} else {
				echo 'Price uptaded for ' . $this->market . '. Buy: ' . $this->new_prices['euros'] . '€/฿; Sell: ' . $this->new_prices['euros'] . '€/฿;' . PHP_EOL;
			}
		} else {
			var_dump($this->new_prices);
			echo 'Prices could not be retrieved.' . PHP_EOL;
		}
	}

	abstract protected function process_prices($json_buy, $json_sell);

}
