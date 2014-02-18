<?php

class Home_Controller extends Base_Controller {

	public function __construct() {
		Asset::container('head')->style('style', 'css/style.css');
		Asset::container('footer')->script('libraries', 'js/libraries.js');
		Asset::container('footer')->script('charts', 'js/charts.js');
	}

	public function action_index($market = 'bitonic') {

		if(!in_array($market, Config::get('application.supported_markets'))) {
// 			var_dump($market);
// 			var_dump(Config::get('application.supported_markets'));
			return Response::error('404');;
		}

		$chart_selector = array(
			'start' => date('Y-m-d', strtotime('-5days')),
			'end'   => date('Y-m-d'),
			'min'   => '2014-01-29',
			'max'   => date('Y-m-d')
		);

//		$prices = Prices::get_uncompressed_blob($market, TRUE);
		$prices = Prices::get_price_by_range($chart_selector['start'], $chart_selector['end'] . ' 23:59:59', $market);
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

		$view = View::make('home.index')->with('data', json_encode($data));
		$view->chart_selector = $chart_selector;
		return $view;
	}

}
