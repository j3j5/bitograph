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

	public function action_index() {

		$prices = Prices::get_uncompressed_blob('bitonic', TRUE);
		$data_buy = array();
		$data_sell = array();
		foreach($prices AS $ts=>$price) {
			$data_buy[] = array('x' => $ts * 1000, 'y' => $price['buy']);
			$data_sell[] = array('x' => $ts * 1000, 'y' => $price['sell']);
		}
		$data_buy = array_reverse($data_buy);
		$data_sell = array_reverse($data_sell);

		$data = array(	'type' => 'line',
						'options' => array('dual_axis' => FALSE),
						'series' => array(
											array(	'key' => 'buy',
													'color' => '#2BBBD8',
													'values' => $data_buy
											),
											array(	'key'   => 'sell',
													'color' => '#F78D3F',
													'values' => $data_sell
											),
									),
				);

		$view = View::make('home.index')->with('data', json_encode($data));
		return $view;
	}

}
