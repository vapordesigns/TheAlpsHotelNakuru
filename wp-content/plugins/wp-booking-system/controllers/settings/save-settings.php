<?php

/** Save Languages **/
$languages = array('en' => 'English','bg'=>'Bulgarian','ca' => 'Catalan','zh' => 'Chinese','hr' => 'Croatian','cz' => 'Czech','da' => 'Danish','nl' => 'Dutch','et' => 'Estonian','fi' => 'Finnish','fr' => 'French','de' => 'German','el' => 'Greek','hu' => 'Hungarian','it' => 'Italian', 'no' => 'Norwegian','pl' => 'Polish','pt' => 'Portugese','ro' => 'Romanian','sr' => 'Serbian', 'ru' => 'Russian','sk' => 'Slovak','sl' => 'Slovenian','es' => 'Spanish','sv' => 'Swedish','tr' => 'Turkish','uk' => 'Ukrainian');

foreach($languages as $code => $language):
    if(!empty($_POST[$code]))
        $activeLanguages[$code] = $language;
endforeach;
if(empty($activeLanguages)) $activeLanguages['en'] = 'English';

update_option('wpbs-languages',json_encode($activeLanguages));

$activeLanguages = json_decode(get_option('wpbs-languages'),true); 
foreach ($activeLanguages as $code => $language)
{
    $wpbsOptions['translationBookingId'][$code]             = esc_html(trim(stripslashes($_POST['translation_bookingid_' . $code])));
    $wpbsOptions['translationYourBookingDetails'][$code]    = esc_html(trim(stripslashes($_POST['translation_yourbookingdetails_' . $code])));
    $wpbsOptions['translationCheckIn'][$code]               = esc_html(trim(stripslashes($_POST['translation_checkin_' . $code])));
    $wpbsOptions['translationCheckOut'][$code]              = esc_html(trim(stripslashes($_POST['translation_checkout_' . $code])));
    $wpbsOptions['translationBookingStatusUpdated'][$code]  = esc_html(trim(stripslashes($_POST['translation_booking_status_updated_' . $code])));
    $wpbsOptions['translationMinDays'][$code]               = esc_html(trim(stripslashes($_POST['translation_min_days_' . $code])));
    $wpbsOptions['translationMaxDays'][$code]               = esc_html(trim(stripslashes($_POST['translation_max_days_' . $code])));
    $wpbsOptions['translationBetweenDays'][$code]           = esc_html(trim(stripslashes($_POST['translation_between_days_' . $code])));
    $wpbsOptions['translationCaptchaMessage'][$code]        = esc_html(trim(stripslashes($_POST['translation_recaptchamessage_' . $code])));
    $wpbsOptions['translationPoweringBy'][$code]            = esc_html(trim(stripslashes($_POST['translation_poweringby_' . $code])));
    $wpbsOptions['translationWebsite'][$code]               = esc_html(trim(stripslashes($_POST['translation_website_' . $code])));
    $wpbsOptions['translationCalendar'][$code]              = esc_html(trim(stripslashes($_POST['translation_calendar_' . $code])));
}

 

if(!empty($_POST['selectedColor']))
    $wpbsOptions['selectedColor'] = $_POST['selectedColor'];
else $wpbsOptions['selectedColor'] = '#3399cc';

if(!empty($_POST['selectedBorder']))
    $wpbsOptions['selectedBorder'] = $_POST['selectedBorder'];
else $wpbsOptions['selectedBorder'] = '#336699';

if(!empty($_POST['historyColor']))
    $wpbsOptions['historyColor'] = $_POST['historyColor'];
else $wpbsOptions['historyColor'] = '#eaeaea';

$wpbsOptions['dateFormat'] = $_POST['dateFormat'];

if(isset($_POST['enable_ical'])) $wpbsOptions['enableiCal'] = $_POST['enable_ical'];
if(isset($_POST['enable_recaptcha'])) $wpbsOptions['enableReCaptcha'] = $_POST['enable_recaptcha'];
if(isset($_POST['recaptcha_public'])) $wpbsOptions['recaptcha_public'] = $_POST['recaptcha_public'];
if(isset($_POST['recaptcha_secret'])) $wpbsOptions['recaptcha_secret'] = $_POST['recaptcha_secret'];

$wpbsOptions['backendStartDay'] = $_POST['backend-start-day'];

update_option('wpbs-options',json_encode($wpbsOptions));


wp_redirect(admin_url('admin.php?page=wp-booking-system-settings&save=ok'));
die();
