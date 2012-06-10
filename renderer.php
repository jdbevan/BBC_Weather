<?php 
/*
function alphanum($string){
	return preg_replace("/[^0-9a-zA-Z]/i", '', $string);
}

$open_tab = false;

function tab($name = false){
	global $open_tab;
	if($open_tab){
		echo '</div><script>
			// Screw the DOM
			document.getElementById(\'nav\').innerHTML += \'<a href="#" onclick="document.getElementById(\\\'RAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\\\') ; return false;" ></a>\';			
		</script>';
	}
	if($name){
		echo '<div id="tab_'.alphanum($name).'" '.(($open_tab)?'style="display:none"':'').' ><h2>'.$name.'</h2>';
		$open_tab = true;
	}else{
		$open_tab = false;
	}
}
*/


mysql_connect("localhost", "root", "hacker") or die(mysql_error());
mysql_select_db("bbc_weather") or die(mysql_error());


?>

<h1>Renderer</h1>

<h2>How bad is the BBC</h2>

<table>
<tr><td>Forecast Date</td><td colspan="3">Wind Speed</td></tr>
<tr><td></td><td>3 Days out</td><td>2 Days out</td><td>1 Day out</td></tr>
<?php

// Use this later for awesome select
$forecast_values = array(
							'maximum_temp_celsius' => 'Maximum Temperature', 
							'wind_speed' => 'Wind Speed', 
						);

$q = 'SELECT forecast_day, days_before, wind_speed FROM forecasts ORDER BY forecast_day';

// Get and store results in [day][raw]
$forecast_results = array();
$averages = array();
$forecastq = mysql_query($q);
while($result = mysql_fetch_array($forecastq)){
	$forecast_results[$result['forecast_day']]['raw'][$result['days_before']] = $result['wind_speed'];
}

// Process deltas for results and store in [day][delta]
foreach($forecast_results as $day => $a){
	if(count($a['raw']) == 3){
		$actual = $forecast_results[$day]['delta'][0]; // Can replace later 
		echo '<tr><td>'.$day.'</td>';
		foreach($a['raw'] as $days_before => $forecast_value){
			$delta = $forecast_value - $actual;
			// Store result
			//$forecast_results[$day]['delta'][$days_before] = $delta;
			$averages[$days_before][] = $delta;
			// Output result
			echo '<td><b>'.$delta.'</b> ('.$forecast_value.')</td>';
		}
		echo '</tr>';
	}
}
?>
<h2>Averages</h2>
<?php

foreach($averages as $days_before => $day_values){
	$av_delta = array_sum($day_values) / count($day_values);
	echo 'Average delta for '.$days_before.' days before: '. $av_delta.'<br />';
}

?>

