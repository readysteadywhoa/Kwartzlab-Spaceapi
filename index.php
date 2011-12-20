<?php
/*
 * SpaceAPI Implementation for Kwartzlab v0.1 - 12.18.2011
 * Last Updated by Doc (ben@generik.ca) 
 * 
 * Currently scrapes Google Calendar for events, but they're only used to see if the space is
 * currently open or not. Easily expanded for adding calendar events to spaceapi
 * 
 */

$calendar_id = 'events@kwartzlab.ca';
$google_api_key = 'XXXXXXXX';
$timezone = 'America/Toronto';
$min_date = '1 day ago';	// in strtotime() format
$max_date = '+1 day';		// in strtotime() format
$max_events = 1;			// maximum events to return

// status messages depending if space is open or not
$status_open = 'Open to the public; if door is closed knock loudly!';
$status_closed = 'Closed to the public; open for members.';

date_default_timezone_set($timezone);
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');
header('Content-type: application/json');

// return google event information in JSON format

$url = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendar_id . '/events?' .
		'maxResults=' . $max_events .
		'&orderBy=startTime' . 
		'&singleEvents=true' .
		'&timeMin=' . date('c',strtotime($min_date)) .
		'&timeMax=' . date('c',strtotime($max_date)) .
		'&timezone=' . $timezone .
		'&pp=1&key=' . $google_api_key;

$ch = curl_init();	// get the curl party started

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER,false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
  curl_setopt($ch, CURLOPT_FAILONERROR, true);

$result = curl_exec($ch);  // return our (hopefully complete) events list
curl_close($ch);

// decode the data into array
$result_array = json_decode($result); 

$space_open = 'false';
$space_status = $status_closed;

// since we have all our Google events now, I wonder if SpaceAPI will be extended to include calendar
// events instead of just check-ins, toilet flushes and other data ;) 

if (count($result_array->items)>0) {
	foreach ($result_array->items as $event) {
		
		// everything in our public calendar is open to the public, so if we're in the middle
		// of an event, we're open!
		
		if ($space_open != 'true') {			// only keep checking events if we're not open yet
			$now = date('U');
			if ((($now) >= strtotime($event->start->dateTime)) && ($now <= strtotime($event->end->dateTime))) {
				$space_open = 'true';
				$space_status = $status_open;
			}
		}
	} 	
}

// spit out our JSON data 

?>
{"api":"0.11","space":"Kwartzlab Makerspace","logo":"http:\/\/www.kwartzlab.ca\/wp-content\/uploads\/spaceapi\/logo.png","icon":{"open":"http:\/\/www.kwartzlab.ca\/wp-content\/uploads\/spaceapi\/open.png","closed":"http:\/\/www.kwartzlab.ca\/wp-content\/uploads\/spaceapi\/closed.png",},"url":"http:\/\/kwartzlab.ca","address":"106-283 Duke Street West, Kitchener, ON N2H 3X7, CANADA","contact":{"email":"join@kwartzlab.ca","twitter":"@kwartzlab","ml":"discuss@kwartzlab.ca"},"lat":"43.455","lon":"-80.496389","cam":"http:\/\/www.ustream.tv\/channel\/kwartzlab","open":<?php echo $space_open ?>,"status":"<?php echo $space_status ?>"}  