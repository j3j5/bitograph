<?php

class Ajax_Controller extends Base_Controller {

	public function __construct() {
	}

	public function action_bitonic() {

		$start  = Input::get('start-day');
		$end    = Input::get('end-day');
		$market = Input::get('chart-market');

		if(!in_array($market, Config::get('application.supported_markets'))) {
			$market = 'bitonic';
		}

		$prices = Prices::get_price_by_range($start, $end . ' 23:59:59', $market);
		$data_prices = array();

		if ($market == 'bitonic') {

			foreach($prices AS $ts=>$price) {
				$data_prices[] = array($ts * 1000, array($price['buy'], $price['sell']));
			}

			$data_prices = array_reverse($data_prices);

			$data = array(	'type' => 'line',
				'options' => array(
					'dual_axis' => FALSE,
					'colors' => array('#2BBBD8', '#F78D3F')
				),
				'data' => array(
					'metrics' => array('buy', 'sell'),
					'values' => $data_prices
				),
			);
		}
		else { // bitpay

			foreach($prices AS $ts=>$price) {
				$data_prices[] = array($ts * 1000, array($price['buy']));
			}

			$data_prices = array_reverse($data_prices);

			$data = array(	'type' => 'line',
				'options' => array(
					'colors' => array('#2BBBD8')
				),
				'data' => array(
					'metrics' => array('buy'),
					'values' => $data_prices
				),
			);
		}

		return json_encode($data);
	}

}