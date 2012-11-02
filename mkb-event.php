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
		// Database Setup
		global $wpdb;
		$user_table = $wpdb->prefix . "users";
		$booking_table = $wpdb->prefix . "em_bookings";
		$xprofile_table = $wpdb->prefix . 'bp_xprofile_data';
		$membership_table = $wpdb->prefix . 'm_membership_relationships';
		$eventid = $_REQUEST['eventid'];
		$sql = "
			select distinct 
				$user_table.ID, 
				display_name, 
				user_email,
				booking_comment,
				value as 'telefoon',
				level_id
			from 
				$user_table inner join 
				$booking_table on $user_table.ID = person_id left join
				$xprofile_table on $user_table.ID = user_id and field_id = 4 inner join
				$membership_table on $user_table.ID = $membership_table.user_id
			where
				event_id = $eventid 
			order by 
				level_id, display_name
		";
		$rows = $wpdb->get_results($sql);
		$result = '<style>table.printlines td, table.printlines th { border: 1px solid black; }</style>';
		$result = $result . '<table class="printlines"><tr><th>Foto</th><th>Naam</th><th>E-Mail</th><th>Telefoon</th><th style="width: 30%">Comment</th></tr>';
		$level = -1;
		foreach($rows as $row) {
			if ( $level != $row->level_id ) {
				$level = $row->level_id;
				if ( $level == 1 ) { $result = $result . '<tr><th colspan="5">Leden</th></tr>'; }
				if ( $level == 5 ) { $result = $result . '<tr><th colspan="5">Gasten</th></tr>'; }
				if ( $level == 6 ) { $result = $result . '<tr><th colspan="5">Activiteitencommisie</th></tr>'; }
			}
			$result = $result . '<tr><td>' . str_replace('bpthumb','bpfull',get_avatar($row->ID, 64)) . '</td><td>' . $row->display_name . '</td><td>' . $row->user_email . '</td><td>' . $row->telefoon . '</td><td>' . $row->booking_comment . '</td></tr>';
		}
		$result = $result . '</table>';
	};
	return $result;
}
