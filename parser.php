<?php

error_reporting(E_ALL);
ini_set("display_errors", true);


mysql_connect("localhost", "root", "hacker") or die(mysql_error());
mysql_select_db("bbc_weather") or die(mysql_error());


$bbc_id_converter = array();
$bbc_placenames = array();


$path = './2012/';

if ($handle = opendir($path)) {
  
    while (false !== ($month = readdir($handle))) {
        
        if ($month == "." or $month == "..") {
        	continue;
        }
        
        for($i = 1; $i < date("t",strtotime('2012/'.$month.'/1')); $i++){
        	
        	$day_path  = $path . $month . '/' . sprintf("%02d", $i);
        	
        	if( file_exists($day_path ) ){

				if ($handle2 = opendir($day_path)) {
  
				    while (false !== ($filename = readdir($handle2))) {
				    	if ($filename == "." or $filename==".." or !preg_match("/\.rss$/", $filename)) {
				    		continue;
				    	}
				    	
//				    	echo $day_path . '/'.$filename, "\n";
				    	
				    	if (!preg_match("/^([0-9]+)_([0-9]+)_/", $filename, $bbc_get_data)) {
				    		echo "Odd filename... $filename\n";
				    		continue;
				    	}
				    	
			    		$bbc_id = $bbc_get_data[1];
			    		$bbc_fetch_time = $bbc_get_data[2];
				    	
						$feed = @simplexml_load_file ($day_path.'/'.$filename);
						if ($feed === false) {
							echo $day_path . '/' . $filename . ": BROKEN\n";
							continue;
						}
						$items = $feed->channel->item;
						
						$location_data = $feed->channel->title;
						$location_name = trim(str_replace(array("BBC Weather - Forecast for ", ", United Kingdom"), "", $location_data));

						if (!isset($bbc_placenames[$location_name])) {
							mysql_query("INSERT INTO locations SET name='" . mysql_real_escape_string($location_name) . "', bbc_id = '$bbc_id'") or die(mysql_error());
							//echo "INSERT INTO locations SET name='" . mysql_real_escape_string($location_name) . "', bbc_id = '$bbc_id';\n";
							$location_id = mysql_insert_id();
							
							$bbc_placenames[$location_name]=1;
							$bbc_id_converter[$bbc_id] = $location_id;
						}
						
						$first_item = true;
						$days_before = 0;
						foreach($items as $item){
							$title = (string) $item->title;
							$description = (string) $item->description;
							$pubDate = (string) $item->pubDate;
							
							// Time
							$date = date('Y-m-d H:i:s',strtotime($pubDate));
//							echo $date, "\n";
							
							// Parse Title
							$title_regex = "/^[a-zA-Z]+:\s+([^,]+)/";
							if (preg_match($title_regex, $title, $desc_match)) {
								$weather_description = $desc_match[1];
							}
							
							// Parse Forecast
							$forecast_regex = "/^" .
											  "(Maximum Temperature:\s+(?P<maximum_temp_celsius>[0-9]+)[^\s]+\s+\((?P<maximum_temp_farenheit>[0-9]+)[^\s]+\),\s+)?" .
											  "Minimum Temperature:\s+(?P<minimum_temp_celsius>[0-9]+)[^\s]+\s+\((?P<minimum_temp_farenheit>[0-9]+)[^\s]+\),\s+" .
											  "Wind Direction:\s+(?P<wind_direction>[a-zA-Z.,:;_-\s]+),\s+" .
											  "Wind Speed:\s+(?P<wind_speed>[0-9]+)mph,\s+" .
											  "Visibility:\s+(?P<visibility>[A-Za-z:,.;_-\s]+),\s+" .
											  "Pressure:\s+(?P<pressure>[0-9]+)mb,\s+" .
											  "Humidity:\s+(?P<humidity>[0-9]+)%,\s+" .
											  "UV Risk:\s+(?P<uv_risk>[0-9]+),\s+" .
											  "Pollution:\s+(?P<pollution>[a-zA-Z;:.,_-\s]+),\s+" .
											  "(Sunrise:\s+(?P<sunrise>[0-9:]+)\s+(?P<sunrise_zone>[A-Z]+),\s+)?" .
											  "(Sunset:\s+(?P<sunset>[0-9:]+)\s+(?P<sunset_zone>[A-Z]+))?" .
											  "$/";

							if ($first_item) {
//								echo "First item:\n";
								$first_item = false;
							}
							
							if (preg_match($forecast_regex, $description, $forecast_fragments)) {
//								var_export($forecast_fragments);
//								echo "\n";

								$q = "INSERT INTO forecasts SET location_id = {$bbc_id_converter[$bbc_id]},
																	datetime_recorded = '2012-$month-" . sprintf("%02d", $i) . " " . mysql_real_escape_string(substr($bbc_fetch_time, 0, 2) .":".substr($bbc_fetch_time,2,2).":".substr($bbc_fetch_time,4,2)) . "',
																	days_before = " . $days_before . ",
																	forecast_time = '12:00:00',
																	forecast_day = '" . date("Y-m-d", strtotime($date . " +$days_before days")) . "'";
																	
								foreach($forecast_fragments as $key=>$data) {
									if (!is_int($key)) {
										if ($data != null) {
											$q .= ", $key = '" . mysql_real_escape_string($data) . "'";
										}
									}
								}
								
								$q .= ";";
								//echo $q, "\n";
								mysql_query($q) or die(mysql_error());

								$days_before++;

							} else {
//								echo "No match on: $description\n";
							}
						}
				    }        	
        		}
        		closedir($handle2);
			}
        }
    }


    closedir($handle);
}


?>
