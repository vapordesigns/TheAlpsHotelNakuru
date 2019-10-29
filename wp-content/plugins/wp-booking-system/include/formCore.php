<?php


function wpbs_display_form_field($field,$language,$error){
    
    $value = null; 
    if(!empty($error['value']))
        $value = $error['value']; 
    $output = '';
    
    
    if(!empty($field['fieldLanguages'][$language])){
        $fieldName = esc_html(wpbs_replaceCustom($field['fieldLanguages'][$language]));
    } else {
        $fieldName =  esc_html(wpbs_replaceCustom($field['fieldName']));
    }
   
    if($field['fieldType'] != 'html'){
        $output .= '<label class="wpbs-form-label';
            $output .= (!empty($error['error'])) ? " wpbs-form-error" : "";
        $output .='" for="wpbs-field-'.$field['fieldId'].'">'. $fieldName;
            $output .= ($field['fieldRequired'] == 1) ? "*" : "";
        $output .= '</label>';
    }
        
    switch($field['fieldType']){
        case 'text':            
            $output .= '<input class="wpbs-form-field wpbs-form-field-'.$field['fieldType'].'" type="text" name="wpbs-field-'.$field['fieldId'].'" id="wpbs-field-'.$field['fieldId'].'" value="'.esc_html(wpbs_replaceCustom($value)).'" />';
            break;
        case 'email':
            $output .= '<input class="wpbs-form-field wpbs-form-field-'.$field['fieldType'].'" type="email" name="wpbs-field-'.$field['fieldId'].'" id="wpbs-field-'.$field['fieldId'].'" value="'.esc_html(wpbs_replaceCustom($value)).'" />';
            break;
        case 'textarea':
            $output .= '<textarea class="wpbs-form-field wpbs-form-field-'.$field['fieldType'].'" name="wpbs-field-'.$field['fieldId'].'" id="wpbs-field-'.$field['fieldId'].'">'.esc_html(wpbs_replaceCustom($value)).'</textarea>';
            break;
        case 'checkbox':
            $fieldOptions = (!empty($field['fieldOptionsLanguages'][$language])) ? $field['fieldOptionsLanguages'][$language] : $field['fieldOptions'];
            $options = explode('|',$fieldOptions);
            $i = 0;
            foreach(array_filter($options) as $option)
            {
                $checked = null;
                if(!empty($value) && in_array(esc_html(trim($option)),$value))
                    $checked = 'checked="checked"';
                
                $output .= '<label class="wpbs-form-label wpbs-form-label-checkbox" for="wpbs-field-'.$field['fieldId'].'-'.$i.'">';
                    $output .= '<input '.$checked.' class="wpbs-form-field wpbs-form-field-'.$field['fieldType'].'" value="'.esc_html(wpbs_replaceCustom(trim($option))).'" type="checkbox" name="wpbs-field-'.$field['fieldId'].'[]" id="wpbs-field-'.$field['fieldId'].'-'.$i.'" />';
                $output .= wpbs_replaceCustom(trim($option)).'</label>';
                $i++;
            }

            break;
        case 'html':
            $output .= (wpbs_replaceCustom($field['fieldHTML']));     
            break;
        case 'radio':

            $fieldOptions = (!empty($field['fieldOptionsLanguages'][$language])) ? $field['fieldOptionsLanguages'][$language] : $field['fieldOptions'];
            $options = explode('|',$fieldOptions);
            $i = 0; foreach(array_filter($options) as $option){
                $checked = null;
                if(esc_html(trim($option)) == $value) $checked = 'checked="checked"';
                
                $output .= '<label class="wpbs-form-label wpbs-form-label-radio" for="wpbs-field-'.$field['fieldId'].'-'.$i.'">';
                    $output .= '<input '.$checked.' class="wpbs-form-field wpbs-form-field-'.$field['fieldType'].'" value="'.esc_html(trim($option)).'" type="radio" name="wpbs-field-'.$field['fieldId'].'" id="wpbs-field-'.$field['fieldId'].'-'.$i.'" />';
                $output .= esc_html(trim($option)).'</label>';
                $i++;
            }
            break;
        case 'dropdown':
            $output .= '<select class="wpbs-form-field wpbs-form-field-'.$field['fieldType'].'" name="wpbs-field-'.$field['fieldId'].'" id="wpbs-field-'.$field['fieldId'].'" >';
            
            $fieldOptions = (!empty($field['fieldOptionsLanguages'][$language])) ? $field['fieldOptionsLanguages'][$language] : $field['fieldOptions'];
            $options = explode('|',$fieldOptions);
            foreach($options as $option){
                $selected = null;
                if($value == esc_html(trim($option))) $selected = 'selected="selected"';
                $output .= '<option '.$selected.' value="'.esc_html(trim($option)).'">'.esc_html(trim($option)).'</option>';
            }
            $output .= '</select>';
            break;
        default:
            $output .= __("Error: Invalid Field Type",'wpbs');
    }
    return $output;
}


function wpbs_display_form($ID,$language = 'en',$errors = false, $calendarID, $autoPending = 'no', $minDays = 0, $maxDays = 0){
    global $wpdb;
    $output = '';
    $sql = $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'bs_forms WHERE formID=%d',$ID);
    $form = $wpdb->get_row( $sql, ARRAY_A );
    $wpbsOptions = json_decode(get_option('wpbs-options'),true);

    
        
    $formOptions = json_decode($form['formOptions'],true);
    
    if(!empty($formOptions['submitLabel'][$language]))
        $submitLabel = esc_html($formOptions['submitLabel'][$language]);
    else
        $submitLabel = esc_html($formOptions['submitLabel']['default']);  
    
    if(!empty($formOptions['selectDate'][$language]))
        @$selectDate = esc_html($formOptions['selectDate'][$language]);
    else
        @$selectDate = esc_html($formOptions['selectDate']['default']);          
    
    
    if(!empty($errors['noDates']) && $errors['noDates'] == true){
        $output .= '<div class="wpbs-form-item">';    
            $output .= '<label class="wpbs-form-label wpbs-form-error">'.$selectDate.'</label>';
        $output .= '</div>';
    } 

    if(!empty($errors['failedCaptcha']) && $errors['failedCaptcha'] == true){
        $output .= '<div class="wpbs-form-item">';    
            $output .= '<label class="wpbs-form-label wpbs-form-error">'; 
            $output .= (!empty($wpbsOptions['translationCaptchaMessage'][$language])) ? $wpbsOptions['translationCaptchaMessage'][$language] : __('Captcha verification has failed.');
            $output .= '</label>';
        $output .= '</div>';
    }

    /**
     * Error labels for minDays, MaxDays and betweenDays.
     * @since 3.5
     */
    if(!isset($errors['noDates']) && !empty($errors['minDays']) && $errors['minDays'] == true)
    {
        $minDaysMessage = (!empty($wpbsOptions['translationMinDays'][$language])) ? $wpbsOptions['translationMinDays'][$language] : __('Please select a minimum of %x days');
        $minDaysMessage = str_replace('%x',$minDays,$minDaysMessage);
        
        $output 
            .= '<div class="wpbs-form-item">'
            . '<label class="wpbs-form-label wpbs-form-error">'.$minDaysMessage.'.</label>'
            . '</div>';
    }

    if(!empty($errors['maxDays']) && $errors['maxDays'] == true)
    {
        $maxDaysMessage = (!empty($wpbsOptions['translationMaxDays'][$language])) ? $wpbsOptions['translationMaxDays'][$language] : __('Please select a maximum of %x days');
        $maxDaysMessage = str_replace('%x',$maxDays,$maxDaysMessage);

        $output 
            .= '<div class="wpbs-form-item">'
            . '<label class="wpbs-form-label wpbs-form-error">'.$maxDaysMessage.'.</label>'
            . '</div>';
    }


    if(!empty($errors['betweenDays']) && $errors['betweenDays'] == true)
    {
    
        $betweenDaysMessage = (!empty($wpbsOptions['translationBetweenDays'][$language])) 
            ? $wpbsOptions['translationBetweenDays'][$language] 
            : __('Please select between a minimum of %x days and a maximum of %y days');
        
        $betweenDaysMessage = str_replace(
                array(
                    '%y',
                    '%x'
                ),
                array(
                    $maxDays,
                    $minDays
                ),
                $betweenDaysMessage
        );

        $output 
            .= '<div class="wpbs-form-item">'
            . '<label class="wpbs-form-label wpbs-form-error">'.$betweenDaysMessage.'.</label>'
            . '</div>';
    }
    
     
    
    
    
    
    if(count($form) > 0): 
   
        
        $fields = json_decode($form['formData'],true); 

        if(!empty($fields)) foreach($fields as $field):
            $field_name = preg_replace('/[^a-zA-Z0-9_.]/', '', $field['fieldName']);

            $error = null; 
            if(!empty($errors[$field['fieldId']])) $error = $errors[$field['fieldId']];

            $output .= '<div class="wpbs-form-item wpbs-form-item-'.$field['fieldType'].' '.$field['fieldType'].' '.(($field['fieldRequired'] == 'true') ? 'required' : '').' '.strtolower($field_name).'-name '.(($error != null) ? 'wpbs-form-item-error' : '').'">';
                $output .= wpbs_display_form_field($field,$language,$error);
            $output .= '</div>';
        endforeach;

        $output .= '<input type="hidden" name="wpbs-form-id" value="'.$form["formID"].'" />';
        $output .= '<input type="hidden" name="wpbs-form-calendar-ID" value="'.$calendarID.'" />';
        $output .= '<input type="hidden" name="wpbs-form-language" value="'.$language.'" />';
        $output .= '<input type="hidden" name="wpbs-form-auto-pending" value="'.$autoPending.'" />';
        $output .= '<input type="hidden" name="wpbs-form-minimum-days" value="'.$minDays.'" />';
        $output .= '<input type="hidden" name="wpbs-form-maximum-days" value="'.$maxDays.'" />';
        $output .= '<input type="hidden" name="wpbs-form-start-date" class="wpbs-start-date" value="'.$errors['startDate'].'" />';
        $output .= '<input type="hidden" name="wpbs-form-end-date" class="wpbs-end-date" value="'.$errors['endDate'].'" />';
        $output .=  wp_nonce_field( 'wpbs_submit_form_'.$form["formID"] );
        
        if(isset($wpbsOptions['enableReCaptcha']) && $wpbsOptions['enableReCaptcha'] == 'yes'){
            $output .= '<span class="zn-recaptcha" id="recaptcha-'.$form['formID'].'-'.$calendarID.'" style="clear:both;" data-colorscheme="light" data-sitekey="'.$wpbsOptions['recaptcha_public'].'"></span>';
        }
        $output .= '<div class="wpbs-form-item wpbs-submit-button">';          
            $output .= '<input type="button" name="wpbs-form-submit" value="'.$submitLabel.'" class="wpbs-form-submit" />';
            $output .= '<div class="wpbs-form-loading"><img src="'.WPBS_PATH.'/images/ajax-loader.gif" /></div>';
        $output .= '</div>';
        return $output;
    else:
        return __("WP Booking System: Invalid form ID.",'wpbs');
    endif;
}

function wpbs_edit_form($options = array()){
    $default_options = array('formData' => '{}');
    foreach($default_options as $key => $value){
        if(empty($$key))
            $$key = $value;
    }    
    extract($options);
    
    
    $activeLanguages = json_decode(get_option('wpbs-languages'),true);
    if(empty($formData)) $formData = "{}";

    $elem = '';
    
    $elem .= '<div id="wpbs-form-container">';
        //$i = 1;
        foreach(json_decode($formData,true) as $i => $field):
            $fieldTypeFancy = str_replace(array('text','email','textarea','checkbox','radio','dropdown'),array('Text','Email','Textarea','Checkboxes','Radio Buttons','Dropdown'),$field['fieldType']);
            $elem .= '<div class="wpbs-form-field wpbs-form-field-'. $field['fieldId'] .'" data-order="'. $i .'" id="wpbs-form-field-'. $field['fieldId'] .'">';
            $elem .=     '<a href="#" class="wpbs-form-move" title="'.__('Move').'"><!-- --></a>';
            $elem .=     '<a href="#" class="wpbs-form-delete" title="'.__('Delete').'"><!-- --></a>';
            
            $elem .=     '<span class="wpbs-field-name">';
            if(strlen(wpbs_replaceCustom($field['fieldName'])) > 30) {
                
                $fieldName = substr(wpbs_replaceCustom($field['fieldName'] ),0,27) . '...' ;
            } else{
                $fieldName = wpbs_replaceCustom( $field['fieldName'] );
            }

            $elem .= $fieldName;

            $elem .=    '&nbsp;</span><span class="wpbs-field-type">'.$fieldTypeFancy.'</span>';
            
            $elem .=     '<div class="wpbs-field-options" style="display:none;">';
            $elem .=         '<p><label>'.__("Title",'wpbs').'</label><input type="text" name="fieldName" class="fieldName" value="'.esc_html(wpbs_replaceCustom($field['fieldName'])).'"></p>';
            $elem .=         '<p><label>'.__("Type",'wpbs').'</label><select class="fieldType" name="fieldType">
                            <option'; if($field["fieldType"] == 'text') $elem .= " selected='selected'";  $elem .=' value="text">'.__("Text",'wpbs').'</option>
                            <option'; if($field["fieldType"] == 'email') $elem .= " selected='selected'";  $elem .=' value="email">'.__("Email",'wpbs').'</option>
                            <option'; if($field["fieldType"] == 'textarea') $elem .= " selected='selected'";  $elem .=' value="textarea">'.__("Textarea",'wpbs').'</option>
                            <option'; if($field["fieldType"] == 'checkbox') $elem .= " selected='selected'";  $elem .=' value="checkbox">'.__("Checkboxes",'wpbs').'</option>
                            <option'; if($field["fieldType"] == 'radio') $elem .= " selected='selected'";  $elem .=' value="radio">'.__("Radio Buttons",'wpbs').'</option>
                            <option'; if($field["fieldType"] == 'dropdown') $elem .= " selected='selected'";  $elem .=' value="dropdown">'.__("Dropdown",'wpbs').'</option>
                            <option'; if($field["fieldType"] == 'html') $elem .= " selected='selected'";  $elem .=' value="html">'.__("HTML",'wpbs').'</option>
                         </select></p>';

            
            $elem .=         '<p style="'; if(!($field["fieldType"] == 'dropdown' || $field["fieldType"] == 'radio' || $field["fieldType"] == 'checkbox')) $elem .= "display:none";  $elem .='" class="fieldOptionsContainer"><label>'.__("Options",'wpbs').'</label><input type="text" value="'.esc_html(wpbs_replaceCustom($field['fieldOptions'])).'" name="fieldOptions" class="fieldOptions"><small><em>'.__('Separate values with an | (eg. Option 1|Option 2|Option 3)','wpbs').'</em></small>
                
                <br /><a class="wpbs-show-dropdown-translations" href="#">show translations</a>';
                $elem .= '</p>';

                
                $elem .= '
                <span class="wpbs-dropdown-translations" style="display:none;">';
                
                foreach ($activeLanguages as $code => $language) {
                    $val = (!empty($field["fieldOptionsLanguages"][$code])) ? esc_html(wpbs_replaceCustom($field["fieldOptionsLanguages"][$code])) : '';
                    $elem .=         '<p><label>'.$language.'</label><input type="text" name="'.$code.'" value="'. $val .'" class="fieldOptionsLanguage fieldOptionsLanguage-'.$code.'"></p>';
                }
                
            $elem .= '</span>';
            
            $elem .=         '<p style="'; if(!($field["fieldType"] == 'html')) $elem .= "display:none";  $elem .='" class="fieldHtmlContainer"><label>'.__('Content','wpbs').'</label><textarea name="fieldHTML" class="fieldHTML" rows="10" cols="80">'.@esc_html(wpbs_replaceCustom($field['fieldHTML'])).'</textarea></p>';
            $elem .=         '<p style="'; if(($field["fieldType"] == 'html')) $elem .= "display:none";  $elem .='" class="fieldRequiredParent"><label>'.__("Required",'wpbs').'</label><input'; if($field["fieldRequired"] == 'true') $elem .= " checked='checked'";  $elem .=' type="checkbox" name="fieldRequired" class="fieldRequired"></p>';
            $elem .=         '<div class="wpbs-form-line"><!-- --></div>';
            
            foreach ($activeLanguages as $code => $language) {
                $val = (!empty($field["fieldLanguages"][$code])) ? esc_html(wpbs_replaceCustom($field["fieldLanguages"][$code])) : '';
            $elem .=         '<p><label>'.$language.'</label><input type="text" name="'.$code.'" value="'. $val .'" class="languageField languageField-'.$code.'"></p>';
            }
            $elem .=     '</div>';
            $elem .= '</div>';
        endforeach;
    $elem .= '</div>';
    
    $elem .= '<input type="button" id="add-field" value="'.__("Add New Field",'wpbs').'" class="button button-secondary">';
    
        
    $elem .= "<input type='hidden' id='wpbs-form-json' name='formData' value='".$formData."' />";
    
    $elem .= '<script>';    
    foreach($activeLanguages as $code => $language):
        $elem .= "activeLanguages['".$code."'] = '".$language."';";                     
    endforeach;
    $elem .= '</script>';

    echo $elem;
}

function wpbs_form_get_email_field($formData){
    $formData = json_decode($formData);
    if($formData) foreach($formData as $form){
        if($form->fieldType == 'email'){
            return true;
        }
    }
    return false;
}