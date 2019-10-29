<?php global $wpdb;?>
<script>
    wpbs(document).ready(function(){
        wpbs(".wpbs-calendars").css('min-height',wpbs('.wpbs-calendar-backend-wrap').height());
    })
    wpbs(window).bind('load resize',function(){
        wpbs(".wpbs-calendars").css('min-height',wpbs('.wpbs-calendar-backend-wrap').height());
    })
</script>
<div class="wrap wpbs-wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2><?php echo __("Edit Calendar",'wpbs') ;?></h2>

    <div id="wpbs-notification-wrapper">
        
    </div>
    <?php if(!empty($_GET['save']) && $_GET['save'] == 'ok'):?>
    <div id="message" class="updated">
        <p><?php echo __('The calendar was created','wpbs')?></p>
    </div>
    <?php endif;?>

    <?php if(!(!empty($_GET['id']))) $_GET['id'] = 'wpbs-new-calendar';?>
    <?php $sql = $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'bs_calendars WHERE calendarID=%d',$_GET['id']); ?>
    <?php $calendar = $wpdb->get_row( $sql, ARRAY_A );?>
    <?php if(($wpdb->num_rows > 0 || $_GET['id'] == 'wpbs-new-calendar') && (@in_array( get_current_user_id(), json_decode($calendar['calendarUsers'])) || current_user_can( 'manage_options' )) ):?>
    
        <?php if($_GET['id'] == 'wpbs-new-calendar') {$calendar['calendarLegend'] = wpbs_defaultCalendarLegend(); $calendar['calendarData'] = '{}';}?>
        <div class="postbox-container meta-box-sortables">
            <?php echo wpbs_print_legend_css($calendar['calendarLegend'],(!empty($calendar['calendarID'])) ? $calendar['calendarID'] : "", false); ?>
            <form action="<?php echo admin_url( 'admin.php?page=wp-booking-system&do=save-calendar&noheader=true');?>" method="post">
            <div class="wpbs-buttons-wrapper">
                <a class="button button-primary button-h2 wpbs_saveCalendar"><?php echo __('Save Changes','wpbs');?></a>
                
                <a class="button secondary-button button-h2 button-h2-back-margin" href="<?php echo admin_url( 'admin.php?page=wp-booking-system' );?>"><?php echo __("Back",'wpbs') ;?></a>
            </div>
            <input type="text" name="calendarTitle" class="fullTitle" id="calendarTitle" placeholder="<?php echo __("Calendar title",'wpbs') ;?>" value="<?php echo (!empty($calendar['calendarTitle'])) ? $calendar['calendarTitle'] : "" ;?>"/>
            
            <?php if (empty($_GET['id']) || $_GET['id'] !== 'wpbs-new-calendar'): ?>
            <div class="metabox-holder">
                <div class="postbox">
                    <div class="handlediv" title="<?php echo __("Click to toggle",'wpbs') ;?>"></div>
                    <h3 class="hndle">
                        <?php echo __("Bookings",'wpbs') ;?>
                    </h3>
                    
                    
                    
                    <div class="inside">
                        <?php wpbs_display_bookings((!empty($calendar['calendarID'])) ? $calendar['calendarID'] : "");?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="metabox-holder">
                <div class="postbox">
                    <div class="handlediv" title="<?php echo __("Click to toggle",'wpbs') ;?>"><br /></div>
                    <h3 class="hndle"><?php echo __("Availability",'wpbs') ;?></h3>
                    <div class="inside">  
                            <?php $wpbsOptions = json_decode(get_site_option('wpbs-options'),true);?>
                            <?php if(empty($wpbsOptions['backendStartDay'])) $wpbsOptions['backendStartDay'] = 1;?>
                                             
                            <?php echo wpbs_calendar( array( 'showDateEditor' => true, 'calendarData' => $calendar['calendarData'], 'calendarLegend' => $calendar['calendarLegend'], 'calendarID' => (!empty($calendar['calendarID'])) ? $calendar['calendarID'] : "", 'firstDayOfWeek' => $wpbsOptions['backendStartDay'] ) );?>
                            <input type="hidden" value="<?php echo (!empty($calendar['calendarID'])) ? $calendar['calendarID'] : "" ;?>" name="calendarID" />   
                            
                                          
                    </div>
                </div>
            </div>  
            <br />
            <a class="button button-primary wpbs_saveCalendar"><?php echo __('Save Changes','wpbs');?></a>
            </form>
        </div>
    <?php else:?>
        <?php echo __('Invalid calendar ID.','wpbs')?>
    <?php endif;?>     
    <?php if(!empty($_GET['goto']) && ($_GET['goto'] == 'trash' || $_GET['goto'] == 'accepted')):?>
        <script>
            wpbs(document).ready(function(){
                wpbs("#wpbs-bookings-tab-<?php echo $_GET['goto'];?>").click();
            })
        </script>
    <?php endif;?>
</div>

