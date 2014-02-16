<?php

class Prices {

	public static function get_uncompressed_blob($market = 'bitonic', $convert_to_float = FALSE) {
		$supported_markets = Config::get('application.supported_markets');
		if(!is_string($market) OR !in_array($market, $supported_markets)) {
			return FALSE;
		}

		$blob = self::get_blob( $market );
		if(!$blob) {
			return FALSE;
		}
		$blob = self::uncompress_blob( $blob );

		if($convert_to_float) {
			// Convert from long to float again
			foreach($blob AS $ts => $prices) {
				$blob[$ts]['buy'] = $prices['buy']/100;
				$blob[$ts]['sell'] = $prices['sell']/100;
			}
		}
		return $blob;
	}

	public static function get_price_by_range ($start = NULL, $end = NULL, $market = 'bitonic') {

		$prices = self::get_uncompressed_blob($market, TRUE);

		if (is_null($start) || is_null($end)) {
			return $prices;
		}

		$start_ts = strtotime($start);
		$end_ts   = strtotime($end);

		$range_prices = array();
		foreach ($prices AS $ts => $price) {
			if ( $ts >= $start_ts && $ts <= $end_ts) {
				$range_prices[$ts] = $price;
			}
		}

		unset($prices);

		return $range_prices;
	}

	private static function get_blob($market) {
		if(empty($market) OR !is_string($market)) {
			return FALSE;
		}
		return DB::table('bitcoin_price')->where('market', '=', $market)->only('prices');
	}


	private static function uncompress_blob($blob) {
		$bytes = unpack("V*", gzinflate($blob));

		$prices = array();
		$i = 0;
		// Build the array and undo the cent conversion
		foreach ($bytes as $v) {
			if ($i%3 == 0) {
				$k = $v;
			} elseif($i%3 == 1) {
				$prices[$k]['buy'] = $v;
// 			} elseif($i%3 == 2)
			} else {
				$prices[$k]['sell'] = $v;
			}
			$i++;
		}
		return $prices;

	}

	/**
	 *
	 * example: $old_prices = array(time()-233 => array('buy' => 40055, 'sell' => 52322));
	 * 			$new_prices = array('buy' => 500.25, 'sell' => 400.23, 'timestamp' => time())
	 *
	 */
	public static function update_prices($market, $new_prices) {
		$supported_markets = Config::get('application.supported_markets');

		if( !is_string($market) OR !in_array($market, $supported_markets) ) {
			return FALSE;
		}

		if( !is_array($new_prices)) {
			var_dump($new_prices);
			return FALSE;
		}
		elseif(!isset($new_prices['buy'], $new_prices['sell'], $new_prices['timestamp'])){
			var_dump($new_prices);
			return FALSE;
		}


		$prices = self::get_uncompressed_blob($market);

		// Convert prices to cents so we can store it on a long
		$prices[$new_prices['timestamp']]['buy'] = $new_prices['buy'] * 100;
		$prices[$new_prices['timestamp']]['sell'] = $new_prices['sell'] * 100;
		krsort($prices);

		return self::set_compressed_blob($market, $prices);
	}


	private static function set_compressed_blob($market, $prices) {
		$supported_markets = Config::get('application.supported_markets');

		if( !is_string( $market ) === 0 || !in_array($market, $supported_markets)  || !is_array($prices)) {
			return FALSE;
		}

		$blob = self::compress_prices($prices);
// 		return DB::table('bitcoin_price')->insert(array('market' => $market, 'prices' => $blob));
		return DB::query("REPLACE INTO `bitcoin_price` (`market`, `prices`) VALUES (?, ?)", array($market, $blob));
	}

	private static function compress_prices($prices) {
		// compress stats
		$pack="";
		foreach($prices as $timestamp => $stats_segments){
			if(!isset($stats_segments['buy'], $stats_segments['sell'])){
				continue;
			}
			$pack.= pack("V*", $timestamp, $stats_segments['buy'], $stats_segments['sell']);
		}
		return gzdeflate($pack);
	}
}
