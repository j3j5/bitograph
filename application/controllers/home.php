<?php

class Home_Controller extends Base_Controller {

	public function __construct() {
	}

	public function action_index ($market = 'bitonic') {

		Asset::container('head')->style('style', 'css/style.css');
		Asset::container('footer')->script('angular', '//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.0rc3/angular.js');
		Asset::container('footer')->script('d3', '//cdnjs.cloudflare.com/ajax/libs/d3/3.4.2/d3.js');
		Asset::container('footer')->script('charts', 'js/charts.js');
		Asset::container('footer')->script('angApp', 'js/app.js');

		$markets = Config::get('application.supported_markets');

		if(!in_array($market, $markets)) {
			return Response::error('404');
		}

		$today = date('Y-m-d');
		$view_params = array(
			'startDate'       => date('Y-m-d', strtotime('-5days')),
			'endDate'         => $today,
			'calendarMinDate' => '2014-01-29',
			'calendarMaxDate' => $today,
			'market'          => $market
		);

		$prices = Prices::get_price_by_range($view_params['startDate'], $view_params['endDate'] . ' 23:59:59', $market);
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

		return View::make('home.index')
			->with('chart_data', json_encode($data))
			->with('view_params', json_encode($view_params));
	}

}
