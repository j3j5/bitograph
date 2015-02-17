<?php

class Prices {

	private static $blob_table_format = "mY";
	private static $blob_table_prefix = "blob_";

	public static function get_uncompressed_blob($start_ts = FALSE, $end_ts = FALSE, $market = 'bitonic', $convert_to_float = FALSE) {

		$supported_markets = Config::get('application.supported_markets');
		if(!is_string($market) OR !in_array($market, $supported_markets)) {
			return FALSE;
		}

		if(!$start_ts) {
			$start_ts = strtotime("-1 week");
			// If $start_ts is NOT provided, overwrite the $end_ts to now anyway
			$end_ts = time();
		} else {
			if(!$end_ts) {
				// If $start_ts is provided but no $end_ts it means we just want the date for one month
				$end_ts = $start_ts;
			}
		}

		$blobs = self::get_blobs( $market, $start_ts, $end_ts );
		if(empty($blobs)) {
			return FALSE;
		}

		$i = 0;
		foreach($blobs AS $blob) {
			$blob = self::uncompress_blob( $blob );
			if(is_array($blob)) {
				if($i == 0) {
					$blob_results = $blob;
				} else {
					$blob_results = array_merge($blob_results, $blob);
				}
			}
		}

		if($convert_to_float) {
			// Convert from long to float again
			foreach($blob_results AS $ts => $prices) {
				$blob_results[$ts]['buy'] = $prices['buy']/100;
				$blob_results[$ts]['sell'] = $prices['sell']/100;
			}
		}
		return $blob_results;
	}

	public static function get_price_by_range ($start = NULL, $end = NULL, $market = 'bitonic') {

		if(empty($start)) {
			$start = date("Y-m-d");
		}

		$start_ts = strtotime($start);
		$end_ts   = @strtotime($end); // $end may be NULL, in that case ignore it

		$prices = self::get_uncompressed_blob($start_ts, $end_ts, $market, TRUE);
		if (is_null($end)) {
			return $prices;
		}

		$range_prices = array();
		foreach ($prices AS $ts => $price) {
			if ( $ts >= $start_ts && $ts <= $end_ts) {
				$range_prices[$ts] = $price;
			}
		}
		unset($prices);

		return $range_prices;
	}

	private static function get_blobs($market, $start_ts, $end_ts) {
		if(empty($market) OR !is_string($market)) {
			return FALSE;
		}

		if(!is_numeric($start_ts) OR !is_numeric($end_ts)) {
			return FALSE;
		}

		$results = array();
		$tables = self::get_blob_tables($start_ts, $end_ts);
		foreach($tables as $table) {
			try {
				$result = DB::table($table)->where('market', '=', $market)->only('prices');
			} catch(Exception $e) {
				// If the table doesn't exist, create it
				if($e->getCode() == '42S02') {
					self::create_table($table);
					return array();
				}
				return FALSE;
			}
			if(!empty($result)) {
				$results[] = $result;
			}
		}
		return $results;
	}

	private static function get_blob_tables($start_ts, $end_ts) {
		$start_date = date(self::$blob_table_format, $start_ts);
		$end_date = date(self::$blob_table_format, $end_ts);

		// If both ts are on the same month, return an array w/ only one element
		if($start_date == $end_date) {
			return array(self::$blob_table_prefix . $start_date);
		}

		$tables = array();
		$last_month = new DateTime("@{$end_ts}");
		// Make the current date the beginning of the first month
		$current_date = new DateTime(date("Y-m-", $start_ts) . "01");
		$current_date->setTimestamp();
		while($current_date->getTimestamp() <= $last_month->getTimestamp()) {
			$tables[] = self::$blob_table_prefix . $current_date->format(self::$blob_table_format);
			$current_date->setTimestamp(strtotime("+1 month", $current_date->getTimestamp()));
		}
		return $tables;
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

		$prices = self::get_uncompressed_blob($new_prices['timestamp'], FALSE, $market);

		// Convert prices to cents so we can store it on a long
		$prices[$new_prices['timestamp']]['buy'] = $new_prices['buy'] * 100;
		$prices[$new_prices['timestamp']]['sell'] = $new_prices['sell'] * 100;
		krsort($prices);

		return self::set_compressed_blob($market, $prices, $new_prices['timestamp']);
	}


	private static function set_compressed_blob($market, $prices, $timestamp) {
		$supported_markets = Config::get('application.supported_markets');

		if( !is_string( $market ) === 0 || !in_array($market, $supported_markets)
			|| !is_array($prices) || !is_numeric($timestamp)) {
			return FALSE;
		}

		$tables = self::get_blob_tables($timestamp, $timestamp);
		$table_count = is_array($tables) ? count($tables) : 0;
		if($table_count !== 1) {
			return FALSE;
		}

		$blob = self::compress_prices($prices);
		$bindings = array($market, $blob, $blob);

		return DB::query(
			"INSERT INTO `{$tables[0]}` (`market`, `prices`, `created_at`, `updated_at`) " .
			"VALUES (?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE `prices` = ?, " .
			"`updated_at` = NOW()",
			$bindings
		);
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

	private static function create_table($table_name) {

		Schema::create($table_name, function($t) {
			$t->string('market', 255);
			$t->blob('prices');
			$t->timestamps();
			$t->primary("market");
		});
	}
}
