<?php
function wpbs_changeDayAdmin_callback($data) {
    global $showDateEditor;
    $showDateEditor = true;
    wpbs_changeDay_callback($data);

    die();
}
function wpbs_changeDay_callback($data) 
{

    global $showDateEditor, $wpdb;

    if ( !empty($data) )
    {
        if(!empty($data['showTooltip']) && in_array($data['showTooltip'],array(1,2,3))) $showTooltip = $data['showTooltip']; else $showTooltip = 1;
        if(in_array($data['totalCalendars'],array(1,2,3,4,5,6,7,8,9,10,11,12))) $totalCalendars = $data['totalCalendars']; else $totalCalendars = 1;
        if(in_array($data['weekStart'],array(1,2,3,4,5,6,7))) $firstDayOfWeek = $data['weekStart']; else $firstDayOfWeek = 1;
        if(in_array($data['calendarSelectionType'],array('single','multiple','fixed','week', '8days'))) $calendarSelectionType = $data['calendarSelectionType']; else $calendarSelectionType = 'multiple';
        if(!empty($data['formPosition']) && in_array($data['formPosition'],array('below','side'))) $formPosition = $data['formPosition']; else $formPosition = 'below';
        
        if(!empty($data['currentTimestamp'])) $currentTimestamp = $data['currentTimestamp'];
        if(!empty($data['calendarData'])) $calendarData = $data['calendarData'];
        if(!empty($data['calendarLegend'])) $calendarLegend = $data['calendarLegend'];
        if(!empty($data['showDropdown'])) $showDropdown = $data['showDropdown'];
        if(!empty($data['calendarHistory'])) $calendarHistory = $data['calendarHistory'];
        if(!empty($data['calendarID'])) $calendarID = $data['calendarID'];
        if(!empty($data['autoPending'])) $autoPending = $data['autoPending'];
        if(!empty($data['minDays'])) $minDays = $data['minDays']; else $minDays = 0;
        if(!empty($data['maxDays'])) $maxDays = $data['maxDays']; else $maxDays = 0;
        if(!empty($data['jump'])) $jump = $data['jump']; else $jump = 0;
        if(!empty($data['weekNumbers'])) $weekNumbers = $data['weekNumbers'];
        if(!empty($data['formID'])) $formID = $data['formID']; else $formID = false;

        $calendarSelection = ''; if(!empty($data['calendarSelection'])) $calendarSelection = $data['calendarSelection'];
        
        if(!empty($data['calendarLanguage'])) $calendarLanguage = $data['calendarLanguage'];
        if(!empty($data['weekStart'])) $firstDayOfWeek = $data['weekStart'];

        if(!empty($data['calendarDirection']) && $data['calendarDirection'] == 'next')
        {
            if ( $jump == 'yes' )
                $currentTimestamp = strtotime(date('j F Y',$currentTimestamp) . " + " . $totalCalendars . " month");
            else
                $currentTimestamp = strtotime(date('j F Y',$currentTimestamp) . " + 1 month");
        } 
        elseif(!empty($data['calendarDirection']) && $data['calendarDirection'] == 'prev')
        {
            if ( $jump == 'yes' )
                $currentTimestamp = strtotime(date('j F Y',$currentTimestamp) . " - " . $totalCalendars . " month");
            else
                $currentTimestamp = strtotime(date('j F Y',$currentTimestamp) . " - 1 month");
        }
    }

    if ( !empty($_POST) )
    {
        if(!empty($_POST['showTooltip']) && in_array($_POST['showTooltip'],array(1,2,3))) $showTooltip = $_POST['showTooltip']; else $showTooltip = 1;
        if(in_array($_POST['totalCalendars'],array(1,2,3,4,5,6,7,8,9,10,11,12))) $totalCalendars = $_POST['totalCalendars']; else $totalCalendars = 1;
        if(in_array($_POST['weekStart'],array(1,2,3,4,5,6,7))) $firstDayOfWeek = $_POST['weekStart']; else $firstDayOfWeek = 1;
        if(in_array($_POST['calendarSelectionType'],array('single','multiple','fixed','week', '8days'))) $calendarSelectionType = $_POST['calendarSelectionType']; else $calendarSelectionType = 'multiple';
        if(!empty($_POST['formPosition']) && in_array($_POST['formPosition'],array('below','side'))) $formPosition = $_POST['formPosition']; else $formPosition = 'below';
        
        if(!empty($_POST['currentTimestamp'])) $currentTimestamp = $_POST['currentTimestamp'];
        if(!empty($_POST['showDropdown'])) $showDropdown = $_POST['showDropdown'];
        if(!empty($_POST['calendarHistory'])) $calendarHistory = $_POST['calendarHistory'];
        if(!empty($_POST['calendarID'])) $calendarID = $_POST['calendarID'];
        if(!empty($_POST['autoPending'])) $autoPending = $_POST['autoPending'];
        if(!empty($_POST['minDays'])) $minDays = $_POST['minDays']; else $minDays = 0;
        if(!empty($_POST['maxDays'])) $maxDays = $_POST['maxDays']; else $maxDays = 0;
        if(!empty($_POST['jump'])) $jump = $_POST['jump']; else $jump = 0;
        if(!empty($_POST['weekNumbers'])) $weekNumbers = $_POST['weekNumbers'];
        if(!empty($_POST['formID'])) $formID = $_POST['formID']; else $formID = false;

        $calendarSelection = ''; if(!empty($_POST['calendarSelection'])) $calendarSelection = $_POST['calendarSelection'];
        
        if(!empty($_POST['calendarLanguage'])) $calendarLanguage = $_POST['calendarLanguage'];
        if(!empty($_POST['weekStart'])) $firstDayOfWeek = $_POST['weekStart'];

        if(!empty($_POST['calendarDirection']) && $_POST['calendarDirection'] == 'next')
        {
            if ( $jump == 'yes' )
                $currentTimestamp = strtotime(date('j F Y',$currentTimestamp) . " + " . $totalCalendars . " month");
            else
                $currentTimestamp = strtotime(date('j F Y',$currentTimestamp) . " + 1 month");
        } 
        elseif(!empty($_POST['calendarDirection']) && $_POST['calendarDirection'] == 'prev')
        {
            if ( $jump == 'yes' )
                $currentTimestamp = strtotime(date('j F Y',$currentTimestamp) . " - " . $totalCalendars . " month");
            else
                $currentTimestamp = strtotime(date('j F Y',$currentTimestamp) . " - 1 month");
        }
    }

    $currentTimestamp = intval($currentTimestamp);
    //hack $currentTimestamp to be the middle of the month.
    $currentTimestamp = strtotime("15 " . date(' F Y',$currentTimestamp));
    
    // Getting calendar information to be sent!
    if ( !$calendarData && !$calendarLegend )
    {
        $sql = $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'bs_calendars WHERE calendarID = %s', $calendarID );
        $calendarResults = $wpdb->get_results( $sql, ARRAY_A );

        $calendarLegend = new stdClass;
        $calendarData   = new stdClass;

        if ( $wpdb->num_rows > 0 )
        {
            $calendarLegend     = $calendarResults[0]['calendarLegend'];
            $calendarData       = stripslashes( $calendarResults[0]['calendarData'] );
        }
    }
    
    echo wpbs_calendar(
        array(
            'ajaxCall'                  => true, 
            'calendarLanguage'          => $calendarLanguage, 
            'calendarHistory'           => $calendarHistory, 
            'showDateEditor'            => $showDateEditor, 
            'calendarID'                => $calendarID, 
            'calendarData'              => $calendarData, 
            'currentTimestamp'          => $currentTimestamp, 
            'showTooltip'               => $showTooltip,  
            'showDropdown'              => $showDropdown, 
            'totalCalendars'            => $totalCalendars, 
            'firstDayOfWeek'            => $firstDayOfWeek, 
            'calendarLegend'            => $calendarLegend, 
            'calendarSelection'         => $calendarSelection, 
            'calendarSelectionType'     => $calendarSelectionType, 
            'autoPending'               => $autoPending, 
            'minDays'                   => $minDays, 
            'maxDays'                   => $maxDays, 
            'jump'                      => $jump, 
            'weekNumbers'               => $weekNumbers, 
            'formID'                    => $formID, 
            'formPosition'              => $formPosition
        )
    ); 
    
	die(); 
}


function wpbs_prettifyAjaxPayload( $payloadPart )
{
    if ( !$payloadPart )
        return false;

    $result = array();
    foreach ( $payloadPart as $payload )
    {
        $result[$payload['input']] = $payload['value'];
    }
    return $result;
}