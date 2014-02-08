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
		$data_prices = array();
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

		$view = View::make('home.index')->with('data', json_encode($data));
		return $view;
	}

}
