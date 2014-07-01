<?php
include 'helpers.php';	//helper functions
include 'login.php';	//login information for mysql database

//see google static maps api
$usa = '<img src=\'http://maps.google.com/maps/api/staticmap?center=usa&amp;zoom=4&amp;size=640x500&amp;sensor=true\' alt=\'map\'>';
$endBlock = '</body>' . '</html>';

/*Get parameters from URL.
If no parameters, just ouput American map.*/
if(!(isset($_GET['lat']) && isset($_GET['lng']) && isset($_GET['locate']))) {
	echo $usa . $endBlock;
	die;
}

$lat = $_GET['lat'];
$lng = $_GET['lng'];
$locate = $_GET['locate'];	//boolean
 
//If parameters are not numeric, just output American map.
if(!(is_numeric($lat) && is_numeric($lng) && is_numeric($locate))) {
	echo $usa . $endBlock;
	die;
}
				
// Opens a connection to a mySQL server
//user: localhost
$connection = mysql_connect('localhost', $username, $password) or die;

// Set the active mySQL database
//database: crimap
$db_selected = mysql_select_db($database, $connection) or die;

/*Make mysql query string.
Get all the cities closest to the search city. The lengthy formula is for calculating distance using lat and lng.
I copied it from Google store locater example.*/
$query = sprintf(
	'SELECT *, 
	(3959*acos(cos(radians("%s"))*cos(radians(lat))*cos(radians(lng)-radians("%s"))+sin(radians("%s"))*sin(radians(lat)))) AS distance 
	FROM stats 
	HAVING distance < 40 
	ORDER BY distance 
	LIMIT 9',
	
	//these functions are for security.
	mysql_real_escape_string($lat),
	mysql_real_escape_string($lng),
	mysql_real_escape_string($lat));
	
//run mysql query
$result = mysql_query($query) or die;

//no results
$length = mysql_num_rows($result);
if($length <= 0) {
	echo 'Sorry, no crime data available.  Try searching for a city instead of a state.';
	echo $usa . $endBlock;
	die;
}

//set colors. see about.html for details
$colors = array();
while ($row = @mysql_fetch_assoc($result)) {
	$colors[] = $row['crime_index'];
}

sort($colors, SORT_NUMERIC);
$bottom = $colors[round($length/3)];
$top = $colors[round($length*2/3)];

$counter = 0;
$letters = array('A','B','C','D','E','F','G','H','I'); //only using 9. the rest of the letter are 'J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$image = 'http://maps.google.com/maps/api/staticmap?'; //map url

//html table
$table = '<table><thead><tr><th colspan=\'16\'>Crime Data for Year of 2008</th></tr><tr>'; 
$table .= '<th>&nbsp;&nbsp;#&nbsp;&nbsp;</th>';

//don't print these variables.
$exclude = array('id', 'lat', 'lng');

//generate and print both the map url and the table
mysql_data_seek($result, 0);
$row = @mysql_fetch_assoc($result);
foreach($row as $key => $value) {
	if(!in_array($key, $exclude))
		$table .= '<th>' . $key . '</th>';
}
$table .= '</tr></thead><tbody>';

$table = str_replace('distance', 'distance_(miles)', $table);
$table = str_replace('_', '<br>', $table);

mysql_data_seek($result, 0);
while ($row = @mysql_fetch_assoc($result)){
	$counter += 1;
	
	//map url
	if ($counter != 1)
		$image .= '&amp;';
	$color = getColor($bottom, $top, $row['crime_index']);
	$image .= 'markers=color:' . $color . '|label:' . $letters[$counter-1] . '|' . $row['lat'] . ',' . $row['lng'];
	
	//data table
	$table .= '<tr><th>' . $letters[$counter-1] . '</th>';
	foreach($row as $key => $value)
		if(!in_array($key, $exclude)) {
			if($key == 'state')
				$value = $sTa[$value];
			if($key == 'distance')
				$value = round($value, 2);
			$table .= '<td>' . $value . '</td>';
		}
	$table .= '</tr>';
}

//geolocation
if($locate == '1')
  $image .= '&amp;markers=color:blue|' . $lat . ',' . $lng;
  
//finish url and table
$image .= '&amp;size=640x500&amp;sensor=true';
$table .=  '</tbody></table>';

//print them out
echo '<img src=\'' . $image . '\' alt=\'map\'>';
echo '<br>';
echo $table;
?>