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
		$result = $result . "<label>Aangemeld: </label><input size=50 value='";
		$sql = "select distinct user_email from $user_table inner join $booking_table on ID = person_id and event_id = $eventid and booking_status = 1";
		$rows = $wpdb->get_results($sql);
		foreach($rows as $row) {
			$result = $result . ' ' . $row->user_email . ",";
		}
		$result = $result . "' /><br/><label>Not niet aangemeld: </label><input size=50 value='";
		$sql = "select distinct user_email from $user_table where ID not in ( select person_id from $booking_table where event_id = $eventid )";
		$rows = $wpdb->get_results($sql);
		foreach($rows as $row) {
			$result = $result . ' ' . $row->user_email . ",";
		}
		$result = $result . "'/><br/><label>Gastenlijst: </label> <a href=\"/evenementen/gastenlijst/?eventid=$eventid\">Link naar gasten overzicht om te printen</a>";
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
		$event = em_get_event($_REQUEST['eventid']);
		$bookings = $event->get_bookings();

		$result = '<style>table.printlines td, table.printlines th { border: 1px solid black; }</style>';
		$result = $result . '<table class="printlines">
			<tr>
				<th>Foto</th>
				<th>Naam</th>
				<th>E-Mail</th>
				<th style="width: 30%">Comment</th>
				<th>Status</th>
			</tr>
			<tr><th colspan="99">Leden</th></tr>	';
		foreach ( $bookings as $booking ) {
			$tableline = '
				<tr>
				<td>' . str_replace('bpthumb','bpfull',get_avatar($booking->person_id, 64)) . '</td>
				<td>' . $booking->person->display_name . '</td>
				<td>' . $booking->person->user_email . '</td>
				<td>' . $booking->booking_comment . '</td>
				<td>' . $booking->status_array[$booking->status] . '</td>
				</tr>';
			if ( $booking->person->has_cap('mkb_lid') ) {
				$leden = $leden . $tableline;
			} else {
				$gast = $gast . $tableline;
			}
		}
		$result = $result . $leden . '<tr><th colspan="99">Gasten</th></tr>' . $gast . '</table>';
	};
	return $result;
}
