<?php

class Home_Controller extends Base_Controller {

	public function __construct() {
		Asset::container('head')->style('style', 'css/style.css');
		Asset::container('footer')->script('libraries', 'js/libraries.js');
		Asset::container('footer')->script('charts', 'js/charts.js');
	}

	/*
	|--------------------------------------------------------------------------
	| The Default Controller
	|--------------------------------------------------------------------------
	|
	| Instead of using RESTful routes and anonymous functions, you might wish
	| to use controllers to organize your application API. You'll love them.
	|
	| This controller responds to URIs beginning with "home", and it also
	| serves as the default controller for the application, meaning it
	| handles requests to the root of the application.
	|
	| You can respond to GET requests to "/home/profile" like so:
	|
	|		public function action_profile()
	|		{
	|			return "This is your profile!";
	|		}
	|
	| Any extra segments are passed to the method as parameters:
	|
	|		public function action_profile($id)
	|		{
	|			return "This is the profile for user {$id}.";
	|		}
	|
	*/

	public function action_index($market = 'bitonic') {

		if(!in_array($market, Config::get('application.supported_markets'))) {
			var_dump($market);
			var_dump(Config::get('application.supported_markets'));
			return View::show_404();
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
