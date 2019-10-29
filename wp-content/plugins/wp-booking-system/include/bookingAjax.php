<?php
function wpbs_bookingModalData_callback() {
    global $wpdb;
    $sql = 'SELECT * FROM ' . $wpdb->prefix . 'bs_calendars WHERE calendarID = ' . $_POST['calendarID'];
    $calendar = $wpdb->get_row( $sql, ARRAY_A );
    echo '<div class="wpbs-modal-box-content wpbs-calendar-'.$calendar['calendarID'].'">';
        echo wpbs_edit_dates( array( 'fromTab' => $_POST['from'], 'bookingAction' => $_POST['buttonAction'], 'customRange' => true, 'startDate' => $_POST['startDate'], 'endDate' => $_POST['endDate'], 'calendarData' => addslashes($calendar['calendarData']), 'calendarLegend' => addslashes($calendar['calendarLegend']), 'currentTimestamp' => time(), 'calendarLanguage' => 'en', 'bookingID' => $_POST['bookingID'] ) ) ;    
        
    echo '</div>';
	die(); 
}

function wpbs_bookingMarkAsRead_callback() {
    global $wpdb;
    $wpdb->update( $wpdb->prefix.'bs_bookings', array('bookingRead' => '1'), array('bookingID' => $_POST['bookingID']) );     
	die(); 
}

