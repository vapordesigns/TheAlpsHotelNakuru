<?php
$data = file_get_contents("php://input");

if ( !$data )
	die;

$data = json_decode($data, true);


switch ($data['action'])
{
	case 'wpbs_changeDayAdmin':
		echo wpbs_changeDayAdmin_callback($data);
		break;
	case 'wpbs_changeDay':
		echo wpbs_changeDay_callback($data);
		break;
	case 'wpbs_saveCalendar':
		global $wpdb;
		if( empty( $data['wpbs-calendar-users'] ) )
			$data['wpbs-calendar-users'] = array();

		if( !empty($data['id']) )
		{
		    $wpdb->update(
		    	$wpdb->prefix.'bs_calendars', 
		    	array(
		    		'calendarTitle' 	=> $data['title'],
		    		'modifiedDate' 		=> time()
		    	), 
		    	array(
		    		'calendarID' 		=> $data['id']
		    	)
		    );
		    
		    if( json_decode( stripslashes( $data['data'] ) ) )
		    {
		        $wpdb->update(
		        	$wpdb->prefix.'bs_calendars',
		        	array(
		        		'calendarData' 	=> $data['data'],
		        	),
		        	array(
		        		'calendarID' 	=> $data['id']
		        	)
		        ); 
		    }
		    
		    if( current_user_can( 'manage_options' ) )
		    {
		        $wpdb->update(
		        	$wpdb->base_prefix.'bs_calendars',
		        	array(
		        		'calendarUsers' => json_encode( $data['users'] )
		        	), 
		        	array(
		        		'calendarID' 	=> $data['id']
		        	)
		        );
		    }
		       
		    // wp_redirect(admin_url('admin.php?page=wp-booking-system&do=edit-calendar&id='.$data['id'].'&save=ok' . $goto));
		    echo json_encode(array('result'=>true, 'class'=>'success', 'msg' => 'Calendar updated.'));
		}
		else
		{

			if ( empty( $data['title'] ) )
			{
				echo json_encode(
			    	array(
			    		'result'=>true, 
			    		'class'=>'error',
			    		'msg' => 'Please enter a title'
			    	)
			    );
			    die;
			}
		    
		    $random_string = wpbs_generateRandomString(32);
		    
		    $wpdb->insert(
		    	$wpdb->prefix.'bs_calendars',
		    	array(
		    		'calendarTitle' 	=> $data['title'], 
		    		'modifiedDate' 		=> time(), 
		    		'createdDate' 		=> time(), 
		    		'calendarLegend' 	=> wpbs_defaultCalendarLegend(), 
		    		'calendarHash' 		=> $random_string
		    	)
		    );    
		    
		    if( json_decode( stripslashes( $data['data'] ) ) )
		    {
		        $wpdb->update(
		        	$wpdb->prefix.'bs_calendars',
		        	array(
		        		'calendarData' 	=> $data['data']
		        	),
		        	array(
		        		'calendarID' 	=> $wpdb->insert_id
		        	)
		        ); 
		    }
		    
		    if( current_user_can( 'manage_options' ) )
		    {
		        $wpdb->update(
		        	$wpdb->base_prefix.'bs_calendars',
		        	array(
		        		'calendarUsers'	=> json_encode( $data['users'] )
		        	),
		        	array(
		        		'calendarID' 	=> $wpdb->insert_id
		        	)
		        );    
		    }
		    
		    
		    // wp_redirect(admin_url('admin.php?page=wp-booking-system&do=edit-calendar&id='.$wpdb->insert_id.'&save=ok'));
		    echo json_encode(
		    	array(
		    		'result'=>true, 
		    		'class'=>'success', 
		    		'url'=> admin_url('admin.php?page=wp-booking-system&do=edit-calendar&id='.$wpdb->insert_id.'&save=ok'),
		    		'msg' => 'Creating calendar...'
		    	)
		    );
		}
		die();
		break;
	case 'wpbs_bookingAction':
		global $wpdb;
		$response = array();
		if( !empty($data['bookingId']) )
		{
			$goto = '';

		    if( !empty( $data['bookingAction'] ) && !empty( $data['bookingId'] ) )
		    {
		        if ( $data['bookingAction'] == 'accept' )
		        {
		            $wpdb->update(
		            	$wpdb->prefix.'bs_bookings', 
		            	array(
		            		'bookingStatus' => 'accepted'
		            	), 
		            	array(
		            		'bookingID' 	=> $data['bookingId']
		            	)
		            );

		            $response = array(
						'status' 		=> true,
						'id' 			=> $data['bookingId'],
						'class' 		=> 'success',
						'action_class' 	=> 'wpbs-booking-move',
						'msg' 			=> 'Booking accepted!',
						'statuses'  	=> array(
							'pending' 		=> '(' . ( (int)$data['statuses']['pending'] - 1) . ')',
							'accepted' 		=> '(' . ( (int)$data['statuses']['accepted'] + 1) . ')',
							'trash' 		=> '(' . (int)$data['statuses']['trash'] . ')'
						)
					);
		        }
		        elseif ( $data['bookingAction'] == 'delete' )
		        {
		            $wpdb->update(
		            	$wpdb->prefix.'bs_bookings', 
		            	array(
		            		'bookingStatus' => 'trash'
		            	),
		            	array(
		            		'bookingID' 	=> $data['bookingId']
		            	)
		            );

		            $fromTab 	= $data['from'];

		            $statuses 	= array();

		            switch ( $fromTab )
		            {
		            	case 'pending':
		            		$statuses 	= array(
		            			'pending' 		=> '(' . ( (int)$data['statuses']['pending'] - 1 ) . ')',
		            			'accepted' 		=> '(' . (int)$data['statuses']['accepted'] . ')',
								'trash' 		=> '(' . ( (int)$data['statuses']['trash'] + 1) . ')'
		            		);
		            		break;
		            	case 'accepted':
		            		$statuses 	= array(
		            			'pending' 		=> '(' . (int)$data['statuses']['pending'] . ')',
		            			'accepted' 		=> '(' . ( (int)$data['statuses']['accepted'] - 1 ) . ')',
								'trash' 		=> '(' . ( (int)$data['statuses']['trash'] + 1) . ')'
		            		);
		            		break;
		            }

		            $response = array(
						'status' 		=> true,
						'id' 			=> $data['bookingId'],
						'class' 		=> 'success',
						'action_class' 	=> 'wpbs-booking-delete',
						'msg' 			=> 'Booking deleted!',
						'statuses'  	=> $statuses
					);
		        } 
		        elseif ( $data['bookingAction'] == 'edit' )
		        {
		            $wpdb->update(
		            	$wpdb->prefix.'bs_bookings', 
		            	array(
		            		'bookingStatus' => 'accepted'
		            	), 
		            	array(
		            		'bookingID' 	=> $data['bookingId']
		            	)
		            );

		            $response = array(
						'status' 		=> true,
						'id' 			=> $data['bookingId'],
						'class' 		=> 'success',
						'action_class' 	=> 'wpbs-booking-move',
						'msg' 			=> 'Booking accepted!',
						'statuses'  	=> array(
							'pending' 		=> '(' . (int)$data['statuses']['pending'] . ')',
							'accepted' 		=> '(' . (int)$data['statuses']['accepted'] . ')',
							'trash' 		=> '(' . (int)$data['statuses']['trash'] . ')'
						)
					);
		        }
		        
		        if( !empty( $data['send_confirmation'] ) && $data['send_confirmation'] == '1' )
		        {
		            $sql = 'SELECT * FROM ' . $wpdb->prefix . 'bs_bookings WHERE bookingID = '. $data['bookingId'] .'';
		            $booking = $wpdb->get_row( $sql, ARRAY_A );
		            $bookingData = json_decode($booking['bookingData'],true);     
		            
		            $sql = 'SELECT formData,formOptions FROM ' . $wpdb->prefix . 'bs_forms WHERE formID = '. $booking['formID'] .'';
		            $form = $wpdb->get_row( $sql, ARRAY_A );
		            $formData = json_decode($form['formData'],true);

		                    
		            if( !empty($formData) )
		            {
		            	foreach ( $formData as $field )
		            	{
			                if( $field['fieldType'] == 'email' && !isset( $autoReplyEmailField ) )
			                {
			                    $autoReplyEmailField = $field['fieldName'];
			                    break;
			                }            
			            }
			        }

		            $sendMessageTo 	= $bookingData[$autoReplyEmailField];
		            
		            $wpbsOptions 	= json_decode(get_option('wpbs-options'),true);
		            $formOptions 	= json_decode($form['formOptions'],true);

		            $headers  		= 'MIME-Version: 1.0' . "\r\n";
		            $headers 		.= 'Content-type: text/html; charset=utf-8' . "\r\n";
		            $headers 		.= "X-Priority: 1\r\n"; 

		            if( !empty( $formOptions['replyFromEmail'] ) )
		            {
		                $headers 	.= 'From: '. stripslashes(html_entity_decode($formOptions['replyFromName'])) . ' <' . stripslashes(html_entity_decode($formOptions['replyFromEmail'])) . '>' . "\r\n";
		                $headers 	.= "Reply-To: ". stripslashes(html_entity_decode($formOptions['replyFromName'])) . ' <' . stripslashes(html_entity_decode($formOptions['replyFromEmail'])) . '>' . "\r\n";
		                $headers 	.= "X-Mailer: PHP/" . phpversion();
		            }
		            else
		            {
		                $headers 	.= 'From: '.get_option('blogname').' <'.get_option('admin_email').'>' . "\r\n";   
		                $headers 	.= "Reply-To: ".get_option('blogname').' <'.get_option('admin_email').'>' . "\r\n";   
		                $headers 	.= "X-Mailer: PHP/" . phpversion(); 
		            }
		            
		            $wpbsOptions = json_decode( get_option( 'wpbs-options' ), true );

		            /*=========================================
		            =            Labels Translated            =
		            =========================================*/
		            
		            $labelBookingId = (!empty($wpbsOptions['translationBookingId'][$bookingData['submittedLanguage']])) 
		                ? $wpbsOptions['translationBookingId'][$bookingData['submittedLanguage']] 
		                : 'Booking ID';
		            $labelCheckIn = (!empty($wpbsOptions['translationCheckIn'][$bookingData['submittedLanguage']])) 
		                ? $wpbsOptions['translationCheckIn'][$bookingData['submittedLanguage']] 
		                : 'Check-In';
		            $labelCheckOut = (!empty($wpbsOptions['translationCheckOut'][$bookingData['submittedLanguage']])) 
		                ? $wpbsOptions['translationCheckOut'][$bookingData['submittedLanguage']] 
		                : 'Check-Out';
		            $labelBookingDetails = (!empty($wpbsOptions['translationYourBookingDetails'][$bookingData['submittedLanguage']])) 
		                ? $wpbsOptions['translationYourBookingDetails'][$bookingData['submittedLanguage']] 
		                : 'Your Booking Details';
		            
		            /*=====  End of Labels Translated  ======*/
		            


		            $subject = (!empty($wpbsOptions['translationBookingStatusUpdated'][$bookingData['submittedLanguage']])) 
		            	? $wpbsOptions['translationBookingStatusUpdated'][$bookingData['submittedLanguage']] 
		            	: __('Booking status updated','wpbs');

		            $message = '<p>' . nl2br($data['confirmation_message']) . '</p>';

		            $message .= $labelBookingDetails;
		            $message .= '<br /><br /><strong>'.$labelBookingId.' </strong>' . $booking['bookingID'] . '<br /><br />';
		        
		            $message .= '<strong>'.$labelCheckIn.' </strong>' . wpbs_timeFormat($booking['startDate']) . '<br />';
		            $message .= '<strong>'.$labelCheckOut.' </strong>' . wpbs_timeFormat($booking['endDate']) . '<br /><br />';

		            
		            // Translate Form Fields for Confirmation Email
		            if( !empty( $formData ) )
		            {
		                foreach( $formData as $field )
		                {
		                    $translatedField = ( !empty($field['fieldLanguages'][$bookingData['submittedLanguage'] ] ) ) 
		                        ? $field['fieldLanguages'][$bookingData['submittedLanguage']] 
		                        : $field['fieldName'];
		                    @$translatedBookingData[ $translatedField ] = $bookingData[ $field['fieldName'] ];
		                }
		            } 
		            
		            // Altering message/appending to the message
		            if(!empty($translatedBookingData))
		            {
		                foreach($translatedBookingData as $formField => $formValue)
		                { 
		                    if(!is_array($formValue))
		                        $message .= '<strong>'.wpbs_replaceCustom(($formField)).': </strong> '.wpbs_replaceCustom(($formValue)).'<br />';
		                    else
		                        $message .= '<strong>'.wpbs_replaceCustom(($formField)).': </strong> '.wpbs_replaceCustom((implode(', ',$formValue))).'<br />';
		                }
		            }           

		            // Sending the Confirmation Email
		            wp_mail($sendMessageTo, $subject, $message, $headers);

		            $response['msg'] .= '&nbsp;Confirmation email sent!';

		        }
		        

		        
		    }
		}
		else
		{
			$response = array(
				'status' => false
			);
		}
		
		// outputing response
		echo json_encode( $response );
		
		die();
		break;

	case 'wpbs_saveForm':
		global $wpdb, $enc;

		// $enc = new Encoding();

		$formVars = $data['data'];
		$formData = array();

		// foreach ( json_decode($formVars['fields'], true) as $field )
		// {
		// 	// Field Languages
		// 	$fieldLanguages = $field['fieldLanguages'];
		// 	$languages = array();
		// 	// $enc = mb_detect_encoding($field['fieldName']);

		// 	$field['fieldName'] = $field['fieldName'];

		// 	// foreach ( $fieldLanguages as $language ) { $languages[$language['lang']] = $language['value']; }

		// 	$field['fieldLanguages'] = $languages;

		// 	// Field Options Languages
		// 	$fieldOptionsLanguages = $field['fieldOptionsLanguages'];
		// 	$languages = array();
		// 	// foreach ( $fieldOptionsLanguages as $fieldLanguage ) { $languages[$fieldLanguage['lang']] = $fieldLanguage['value']; }

		// 	$field['fieldOptionsLanguages'] = $languages;

		// 	// Set the new field object
		// 	$formData[] = $field;
		// }

		$formData 	= $formVars['fields'];

		$redirect 	= false;

		if ( !empty($formVars['id']) )
		{
			$wpdb->update( 
		        $wpdb->prefix.'bs_forms', 
		        array(
		            'formTitle' => stripslashes( esc_html($formVars['title']) ), 
		            'formData' => stripslashes( $formData )
		        ), 
		        array('formID' => intval($formVars['id']) )
		    );     
		    $formID = $formVars['id'];
		    echo json_encode(array('result'=>true, 'class'=>'success', 'msg' => 'Form updated.'));
		}
		// If no formID is present it is a new form so save it!
		else
		{
			if ( empty( $formVars['title'] ) )
			{
				echo json_encode(
			    	array(
			    		'result'=>true, 
			    		'class'=>'error',
			    		'msg' => 'Please enter a title'
			    	)
			    );
			    die;
			}

			$wpdb->insert( 
		        $wpdb->prefix.'bs_forms', 
		        array(
		            'formTitle' => stripslashes( esc_html($formVars['title']) ),
		            'formData' => stripslashes( $formData )
		        )
		    );
		    $formID = $wpdb->insert_id;

		    $redirect 	= true;
		    echo json_encode(
		    	array(
		    		'result'=>true, 
		    		'class'=>'success', 
		    		'url'=> admin_url('admin.php?page=wp-booking-system-forms&do=edit-form&id='.$formID.'&save=ok'),
		    		'msg' => 'Creating form...'
		    	)
		    );
		}


		$formSettings 	= wpbs_prettifyAjaxPayload($formVars['formSettings']);
		$autoReply 		= wpbs_prettifyAjaxPayload($formVars['autoreply']);
		$translations 	= wpbs_prettifyAjaxPayload($formVars['translations']);
		$emailSettings 	= wpbs_prettifyAjaxPayload($formVars['emailSettings']);


		$emails = '';
		if(!empty($formSettings['receive_emails']) && $formSettings['receive_emails'] == 'yes' && !empty($formSettings['sendto'])){
		    $emails = explode(",",$formSettings['sendto']);
		    foreach($emails as $email){
		        if(is_email($email))
		            $emailList[] = sanitize_email($email);
		    }
		    $emails = implode(",",$emailList);
		}

		$formOptions['sendTo'] = $emails;

		$formOptions['trackingScript'] = $formSettings['tracking_script'];

		$formOptions['enableAutoReply'] = $autoReply['enable_autoreply'];




		$formOptions['submitLabel']['default'] 					= esc_html($translations['submitLabel']);
		$formOptions['thankYou']['default'] 					= esc_html($translations['thankYou']);
		$formOptions['selectDate']['default'] 					= esc_html($translations['selectDate']);
		/* @since   3.7.2 */ 
		$formOptions['emailSubject']['default'] 				= esc_html($translations['emailSubject']);
		$formOptions['emailHeading']['default'] 				= esc_html($translations['emailHeading']);

		$formOptions['replyFromName'] 							= esc_html($emailSettings['reply_from_name']);
		$formOptions['replyFromEmail'] 							= esc_html($emailSettings['reply_from_email']);

		if($autoReply['enable_autoreply'] == 'yes')
		{    
		    $formOptions['autoReplyEmailBody']['default'] 		= esc_html($autoReply['autoreply_email_body']);
		    $formOptions['autoReplyEmailSubject']['default'] 	= esc_html($autoReply['autoreply_email_subject']);
		    $formOptions['autoReplyIncludeDetails'] 			= $autoReply['autoreply_include_details'];
		}
		$activeLanguages = json_decode(get_option('wpbs-languages'),true); 
		foreach ($activeLanguages as $code => $language)
		{
		    $formOptions['submitLabel'][$code] = esc_html($translations['submitLabel_' . $code]);
		    $formOptions['thankYou'][$code] = esc_html($translations['thankYou_' . $code]);
		    $formOptions['selectDate'][$code] = esc_html($translations['selectDate_' . $code]);

		    /* @since   3.7.2 */
		    $formOptions['emailSubject'][$code] = esc_html($translations['emailSubject_' . $code]);
		    $formOptions['emailHeading'][$code] = esc_html($translations['emailHeading_' . $code]);
		    /* @since 	4.0 */
		    $formOptions['translationPoweringBy'][$code] = esc_html($translations['translationPoweringBy_' . $code]);
		    $formOptions['translationWebsite'][$code] = esc_html($translations['translationWebsite_' . $code]);
		    $formOptions['translationCalendar'][$code] = esc_html($translations['translationCalendar_' . $code]);

		    if($autoReply['enable_autoreply'] == 'yes')
		    {
		        $formOptions['autoReplyEmailBody'][$code] = esc_html($autoReply['autoreply_email_body_' . $code]);
		        $formOptions['autoReplyEmailSubject'][$code] = esc_html($autoReply['autoreply_email_subject_' . $code]);
		    }
		}





		if(empty($formOptions['submitLabel']['default'])) $formOptions['submitLabel']['default'] = "Book";
		if(empty($formOptions['thankYou']['default'])) $formOptions['thankYou']['default'] = "The form was successfully submitted.";
		if(empty($formOptions['selectDate']['default'])) $formOptions['selectDate']['default'] = "Please select a date.";
		/* @since   3.7.2 */
		if(empty($formOptions['emailSubject']['default'])) $formOptions['emailSubject']['default'] = "New booking";
		if(empty($formOptions['emailHeading']['default'])) $formOptions['emailHeading']['default'] = "A new booking was made via your website!";
		/* @since 	4.0 */
		if(empty($formOptions['translationPoweringBy']['default'])) $formOptions['translationPoweringBy']['default'] = "Powered by WP Booking System";
		if(empty($formOptions['translationWebsite']['default'])) $formOptions['translationWebsite']['default'] = "Website";
		if(empty($formOptions['translationCalendar']['default'])) $formOptions['translationCalendar']['default'] = "Calendar";

		if(empty($formOptions['autoReplyEmailBody']['default'])) $formOptions['autoReplyEmailBody']['default'] = "Thanks for your booking";
		if(empty($formOptions['autoReplyEmailSubject']['default'])) $formOptions['autoReplyEmailSubject']['default'] = "Your booking";

		$wpdb->update( $wpdb->prefix.'bs_forms', array('formOptions' => json_encode($formOptions)), array('formID' => $formID) );
		die();
		break;
}
die();