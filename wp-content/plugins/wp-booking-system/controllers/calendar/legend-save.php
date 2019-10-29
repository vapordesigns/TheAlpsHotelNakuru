<?php 
global $wpdb;

$calendarId = $_POST['calendarID'];


$sql = $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'bs_calendars WHERE calendarID=%d',$calendarId);
$calendar = $wpdb->get_row( $sql, ARRAY_A );
if($wpdb->num_rows > 0):
    $calendarLegend = json_decode($calendar['calendarLegend'],true);
    

    if(empty($_POST['legendID']))
    {
        $legendId = max(array_keys($calendarLegend));
        $i = 1; while(!empty($calendarLegend[$i])){
            $i++;
        }
        $legendId = $i;    
    }
    else
    {
        $legendId = $_POST['legendID'];
    }
    
    $availableLanguages = json_decode(get_option('wpbs-languages'),true);
    foreach($availableLanguages as $languageCode => $languageName):
        $calendarLegend[$legendId]['name'][$languageCode] = $_POST[$languageCode];
    endforeach;
    
    $calendarLegend[$legendId]['name']['default'] = $_POST['legendTitle'];
    $calendarLegend[$legendId]['color'] = $_POST['color'];
    
    //Split Color
    $splitColor = false;
    if(!empty($_POST['activeSplitColor']) && $_POST['activeSplitColor'] == 'on'){
        $splitColor = $_POST['splitColor'];
    }
    $calendarLegend[$legendId]['splitColor'] = $splitColor;
    

    if(!empty($_POST['bookable']) && $_POST['bookable'] == 'on'){
        $calendarLegend[$legendId]['bookable'] = 'yes';
    } else {
        $calendarLegend[$legendId]['bookable'] = false;
    }

    
    $wpdb->update( $wpdb->prefix.'bs_calendars', array('calendarLegend' => json_encode($calendarLegend)), array('calendarID' => $calendarId));     
endif;

wp_redirect(admin_url('admin.php?page=wp-booking-system&do=edit-legend&id='.$calendarId));
die();
?>     