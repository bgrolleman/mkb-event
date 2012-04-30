<?php
/*
Plugin Name: MKB Event
Plugin URI: http://www.emendo-it.nl
Description: Plugin to add special field to events for email
Version: 0.1
Author: Bas Grolleman <bgrolleman@emendo-it.nl>
License: No License 
*/
add_shortcode('mkbeventadmin','mkbeventadmin');
add_shortcode('mkbguestlist','mkbguestlist');

function mkbeventadmin($options) {
	// Only Event Managers
	if ( current_user_can('manage_others_bookings') ) {
		// Database Setup
		global $wpdb;
		$user_table = $wpdb->prefix . "users";
		$booking_table = $wpdb->prefix . "em_bookings";
		$eventid = $options["eventid"];

		// Header
		$result = '<div style="em-admin"><h3 style="clear:both">Beheerders</h3>';
		$result = $result . '<p>Dit gedeelte is puur voor beheerders, en laat een lijst met email adressen zien die eenvoudig naar een eigen email pakket gekopieerd kunnen worden</p>';

		// Email of those available
		$result = $result . "<label>Aangemeld: </label>";
		$sql = "select distinct user_email from $user_table inner join $booking_table on ID = person_id and event_id = $eventid";
		$rows = $wpdb->get_results($sql);
		foreach($rows as $row) {
			$result = $result . ' ' . $row->user_email . ",";
		}
		$result = $result . "<label>Not niet aangemeld: </label>";
		$sql = "select distinct user_email from $user_table where ID not in ( select person_id from $booking_table where event_id = $eventid )";
		$rows = $wpdb->get_results($sql);
		foreach($rows as $row) {
			$result = $result . ' ' . $row->user_email . ",";
		}
		$result = $result . "<label>Gastenlijst: </label> <a href=\"/evenementen/gastenlijst/?eventid=$eventid\">Link naar gasten overzicht om te printen</a>";
		$result = $result . "</div>";
	} else {
		$result = '';
	}
	return $result;
}

function mkbguestlist() {
	$result = '';
	// Only Event Managers
	if ( current_user_can('manage_others_bookings') ) {
		// Database Setup
		global $wpdb;
		$user_table = $wpdb->prefix . "users";
		$booking_table = $wpdb->prefix . "em_bookings";
		$xprofile_table = $wpdb->prefix . 'bp_xprofile_data';
		$eventid = $_REQUEST['eventid'];
		$sql = "
			select distinct 
				$user_table.ID, 
				display_name, 
				user_email,
				value as 'telefoon'	
			from 
				$user_table inner join 
				$booking_table on $user_table.ID = person_id left join
				$xprofile_table on $user_table.ID = user_id and field_id = 4
			where
				event_id = $eventid order by display_name";
		$rows = $wpdb->get_results($sql);
		$result = '<table><tr><th>Foto</th><th>Naam</th><th>E-Mail</th><th>Telefoon</th></tr>';
		foreach($rows as $row) {
			$result = $result . '<tr><td>' . get_avatar($row->ID) . '</td><td>' . $row->display_name . '</td><td>' . $row->user_email . '</td><td>' . $row->telefoon . '</td></tr>';
		}
		$result = $result . '</table>';
	};
	return $result;
}
