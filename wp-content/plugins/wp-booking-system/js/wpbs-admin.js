var wpbs = jQuery.noConflict();
var $instance;

var $saveCalendarDisabled   = false;
var $saveFormDisabled       = false;

// Strip Slashes
String.prototype.stripSlashes = function(){
    return this.replace(/\\(.)/mg, "$1");
}

function wpbs_htmlEscape(str) {
    return String(str)
            .replace(/&/g, '--AMP--')
            .replace(/"/g, '--DOUBLEQUOTE--')
            .replace(/'/g, '--QUOTE--')
            .replace(/</g, '--LT--')
            .replace(/>/g, '--GT--');
}
function wpbs_customReplace(str) {
    return String(str)
            .replace(/--AMP--/g, '&')
            .replace(/--DOUBLEQUOTE--/g, '"')
            .replace(/--QUOTE--/g, '\'')
            .replace(/--LT--/g, '<')
            .replace(/--GT--/g, '>');
}
function showLoader(){
    wpbs('.wpbs-loading').fadeTo(0,0).css('display','block').fadeTo(200,1);
    wpbs('.wpbs-calendar ul').animate({
        'opacity' : '0.7'
    },200);
}
function hideLoader(){
    wpbs('.wpbs-loading').css('display','none');
}
function wpbs_changeDay(direction, timestamp)
{
    var data = {
        action:                 'wpbs_changeDayAdmin',
        calendarDirection:      direction,
        totalCalendars:         $instance.find(".wpbs-total-calendars").html(), 
        currentTimestamp:       timestamp,
        calendarData:           $instance.find(".wpbs-calendar-data").attr('data-info'),
        calendarHistory:        $instance.find(".wpbs-calendar-history").html(),
        calendarLegend:         $instance.find(".wpbs-calendar-legend").attr('data-info'),
        showDropdown:           $instance.find(".wpbs-show-dropdown").html(),
        autoPending:            $instance.find(".wpbs-calendar-auto-pending").html(),
        weekNumbers :           $instance.find(".wpbs-calendar-week-numbers").html(),
        calendarSelectionType:  $instance.find(".wpbs-calendar-selection-type").html(),
        calendarLanguage:       $instance.find(".wpbs-calendar-language").html(),
        weekStart :             $instance.find(".wpbs-calendar-week-start").html(),
        calendarID :            $instance.find(".wpbs-calendar-ID").html()
    };
    
    wpbs.ajax({
        type: 'POST',
        url: wpbs_ajaxurl,
        contentType: 'application/json; charset=utf-8',
        dataType: 'html',
        data: JSON.stringify(data),
        success: function ( response )
        {
            $instance.find('.wpbs-calendars').html(response);
            hideLoader();
            wpbs('.wpbs-chosen').chosen();
        }
    });
}

function wpbs_makeid()
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function wpbs_saveCalendar(refresh)
{
    if ( $saveCalendarDisabled == true )
        return false;

    var data = {
        action:             'wpbs_saveCalendar',
        users:              wpbs('[name="wpbs-calendar-users[]"]').map(function(){return wpbs(this).val();}).get(),
        title:              wpbs('[name="calendarTitle"]').val(),
        data:               wpbs('#inputCalendarData').val(),
        id:                 wpbs('[name="calendarID"]').val()
    }

    
    wpbs.ajax({
        type: 'POST',
        url: wpbs_ajaxurl,
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function ( response )
        {
            wpbs('body').scrollTop(0);

            wpbs('#wpbs-notification-wrapper').append('<div id="wpbs-notice" class="notice notice-' + response.class + ' is-dismissible"><p>' + response.msg + '</p></div>');
            

            var removeTimeout = setTimeout(function () {
                wpbs('#wpbs-notice').remove();
            }, 2000);
            
            if ( typeof response.url !== 'undefined' )
            {
                // disable "Save Changes" button till the calendar is created
                $saveCalendarDisabled = true;
                wpbs('.wpbs_saveCalendar').attr('disabled', true);

                // redirect to the newly created calendar
                var timeout = setTimeout(function () {
                    window.location.href = ''+response.url+'';
                }, 2050);
            } else {
                var timeout = setTimeout(function () {
                    window.location.reload();
                }, 1500);
                
            }
        }
    });
}

function objectifyForm(formArray) 
{//serialize data function
    var returnArray = {};
    for (var i = 0; i < formArray.length; i++)
    {
        returnArray[formArray[i]['name']] = formArray[i]['value'];
    }
    return returnArray;
}

function wpbs_saveForm()
{
    var getFields           = function ()
    {
        var fields;
        // var fields  = [];
        // var fieldTranslations = function ( el )
        // {
        //     var transs = [];
        //     el.find('.languageField').each(function () {
        //         trans = wpbs(this);
        //         transs.push({lang: trans.attr('name'), value: trans.val()});
        //     });
        //     return transs;
        // }
        // var fieldfieldOptionsTranslations = function ( el )
        // {
        //     var transs = [];
        //     el.find('.fieldOptionsLanguage').each(function () {
        //         trans = wpbs(this);
        //         transs.push({lang: trans.attr('name'), value: trans.val()});
        //     });
        //     return transs;
        // }
        // var j = 0;
        // wpbs('.wpbs-field-options').each(function (i, e) 
        // {
        //     j++;
        //     field   = wpbs( this );
        //     fields.push({
        //         fieldID:                j,
        //         fieldName:              field.find('.fieldName').val(), 
        //         fieldType:              field.find('.fieldType').val(),
        //         fieldRequired:          ( field.find('.fieldRequired').is(':checked') ) ? true : false,
        //         fieldOptions:           field.find('.fieldOptions').val(),
        //         fieldLanguages:         fieldTranslations(field),
        //         fieldOptionsLanguages:  fieldfieldOptionsTranslations(field)
        //     })
        // });
        
        if ( wpbs('#wpbs-form-json').length > 0 )
            fields = wpbs('#wpbs-form-json').val();
        return fields;
    }
    var getInputs           = function (selector)
    {
        var fields  = [];
        wpbs(selector + ' input, ' + selector + ' select, ' + selector + ' textarea').each(function (i, e) 
        {
            field   = wpbs( this );

            if ( field.attr('name') == 'autoreply_include_details' )
                fields.push({input: field.attr('name'), value: field.prop('checked')})
            else
                fields.push({input: field.attr('name'), value: field.val()})
        });
        return fields;
    }

    if ( $saveFormDisabled == true )
        return false;

    var data                = {
        id:                     wpbs('[name="formID"]').val(),
        title:                  wpbs('[name="formTitle"]').val(),
        fields:                 getFields(),
        translations:           getInputs('.translations'),
        formSettings:           getInputs('.form-settings'),
        autoreply:              getInputs('.auto-reply'),
        emailSettings:          getInputs('.email-settings')
    }

    var payload             = {
        action:                 'wpbs_saveForm',
        data:                   data
    }

    
    wpbs.ajax({
        type: 'POST',
        url: wpbs_ajaxurl,
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        data: JSON.stringify(payload),
        success: function ( response )
        {
            wpbs('body').scrollTop(0);


            wpbs('#wpbs-notification-wrapper').append('<div id="wpbs-notice" class="notice notice-' + response.class + ' is-dismissible"><p>' + response.msg + '</p></div>');
            
            if ( response.result == false )
                return false;


            var removeTimeout = setTimeout(function () {
                wpbs('#wpbs-notice').remove();
            }, 3000);

            if ( typeof response.url !== 'undefined' )
            {
                // disable "Save Changes" button till the form is created
                $saveFormDisabled = true;
                wpbs('.wpbs_saveForm').attr('disabled', true);

                // redirect to the newly created form
                var timeout = setTimeout(function () {
                    window.location.href = ''+response.url+'';
                }, 3350);
            }
            else
            {
                var timeout = setTimeout(function () {
                    window.location.reload();
                }, 1500);
                
            }

        }
    });
}

function wpbs_bookingAction()
{
    var info        = wpbs('#bookingInfo');
    var statuses    = wpbs('.wpbs-bookings-tabs');
    var data        = {
        action:             'wpbs_bookingAction',
        bookingAction:      info.attr('data-booking-action'),
        bookingId:          info.attr('data-booking-id'),
        calendarId:         info.attr('data-calendar-id'),
        from:               info.attr('data-booking-from'),
        dates:              JSON.stringify( wpbs('#wpbs_ModalDatesEditor').serializeArray() ),
        statuses:           {
            pending:            statuses.find('#status-pending').html().replace(/"/g, "").replace(/'/g, "").replace(/\(|\)/g, ""),
            accepted:           statuses.find('#status-accepted').html().replace(/"/g, "").replace(/'/g, "").replace(/\(|\)/g, ""),
            trash:              statuses.find('#status-trash').html().replace(/"/g, "").replace(/'/g, "").replace(/\(|\)/g, "")
        },
        send_confirmation:  wpbs('.send-confirmation-message').val(),
        confirmation_message: wpbs('.send-confirmation-message-additional').val()
    }

    
    wpbs.ajax({
        type: 'POST',
        url: wpbs_ajaxurl,
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function ( response )
        {
            wpbs_random_id = wpbs_makeid();
            
            if ( response.status )
            {
                wpbs('body').scrollTop(0);


                wpbs('#wpbs-notification-wrapper').append('<div id="' + wpbs_random_id + '" class="notice notice-' + response.class + ' is-dismissible"><p>' + response.msg + '</p></div>');
                
                wpbs('#wpbs-booking-field-' + response.id).removeClass('wpbs-booking-selected wpbs-booking-move wpbs-booking-delete').addClass(response.action_class);

                wpbs('#status-pending').html(response.statuses.pending);
                wpbs('#status-accepted').html(response.statuses.accepted);
                wpbs('#status-trash').html(response.statuses.trash);

                var removeTimeout = setTimeout(function () {
                    wpbs('#wpbs-booking-field-' + response.id).closest('tr').remove();
                    wpbs('#' + wpbs_random_id).remove();
                }, 1000);
                
                // wpbs('.wpbs_saveCalendar').first().click();
                wpbs_saveCalendar(true);
            }
            else
            {
                wpbs('#wpbs-notification-wrapper').append('<div id="' + wpbs_random_id + '" class="notice notice-error is-dismissible"><p>Something went wrong</p></div>');
            }

            var removeTimeout = setTimeout(function () {
                wpbs('#' + wpbs_random_id).remove();
            }, 1000);

        }
    });
}

wpbs(document).ready(function(){
    
    wpbs('.wpbs-chosen').chosen();  
    wpbs('.wpbs-wrap .meta-box-sortables').sortable({
        disabled: true
    });

    wpbs('.wpbs_saveCalendar').each(function () {
        wpbs(this).on('click', function () {
            wpbs_saveCalendar();
        });
    })

    wpbs('.wpbs_saveForm').each(function () {
        wpbs(this).on('click', function () {
            wpbs_saveForm();
        });
    })



    wpbs('#wpbs_bookingAction').each(function () {
        wpbs(this).on('click', function () {
            wpbs_bookingAction();
        });
    })
    
    // wpbs('.wpbs-dropdown').customSelect();
    wpbs('.wpbs-container').each(function(){
    $instance = wpbs(this);
    wpbs($instance).on('change','.wpbs-dropdown',function(e){
        showLoader();     
        e.preventDefault();        
        wpbs_changeDay('jump',wpbs(this).val())
    });
    
    wpbs($instance).on('click','.wpbs-prev',function(e){
        showLoader();
        e.preventDefault();
        if($instance.find(".wpbs-current-timestamp a").length == 0)
            timestamp = $instance.find(".wpbs-current-timestamp").html();
        else 
            timestamp = $instance.find(".wpbs-current-timestamp a").html();

        wpbs_changeDay('prev',timestamp);
    });
    
    
    wpbs($instance).on('click','.wpbs-next',function(e){  
        showLoader();
        e.preventDefault();        
        if($instance.find(".wpbs-current-timestamp a").length == 0)
            timestamp = $instance.find(".wpbs-current-timestamp").html();
        else 
            timestamp = $instance.find(".wpbs-current-timestamp a").html();

        wpbs_changeDay('next',timestamp);
    });
    
    })

    wpbs(document).on('click',"#calendarBatchUpdate",function(e){
        e.preventDefault();
        var wpbsCalendarData = wpbs(".wpbs-calendar-data").attr('data-info');
        if (!wpbsCalendarData)
            wpbsCalendarData = {};
        else {
            wpbsCalendarData = wpbsCalendarData.stripSlashes();
            wpbsCalendarData = JSON.parse(wpbsCalendarData);
        }
        var currentTimestamp = wpbs(".wpbs-current-timestamp").html();
        var currentDate = new Date(currentTimestamp * 1000);
       
        var startDay = wpbs("#startDay").val();
        var startMonth = wpbs("#startMonth").val();
        var startYear = wpbs("#startYear").val();
        
        var endDay = wpbs("#endDay").val();
        var endMonth = wpbs("#endMonth").val();
        var endYear = wpbs("#endYear").val();
        
        var selectStatus = wpbs("#changeStatus").val();
        
        var bookingDetails = wpbs("#bookingDetails").val();

        var startTime = (Date.parse(startDay + " " + startMonth + " " + startYear))/1000;
        var endTime = (Date.parse(endDay + " " + endMonth + " " + endYear))/1000;
        if(startTime < endTime){

            for(i=startTime; i <= endTime + 60*60*23; i = i + 60*60*24){
                var changeDate = new Date(i * 1000);
                            
                if(changeDate.getMonth() == currentDate.getMonth() && changeDate.getFullYear() == currentDate.getFullYear()){
                    if(!wpbs("select.wpbs-day-"+(changeDate.getDate())).find('option.wpbs-option-' + selectStatus).prop('selected')){
                        wpbs("select.wpbs-day-"+(changeDate.getDate())).find('option').prop("selected",false);
                        wpbs("select.wpbs-day-"+(changeDate.getDate())).find('option.wpbs-option-' + selectStatus).prop("selected",true);
                    }
                    wpbs("select.wpbs-day-"+(changeDate.getDate())).parents('li').find('span.wpbs-select-status').removeClass().addClass('wpbs-select-status status-' + selectStatus);
                    wpbs("select.wpbs-day-"+(changeDate.getDate())).parents('li').find('span.wpbs-day-split-top').removeClass().addClass('wpbs-day-split-top wpbs-day-split-top-' + selectStatus);
                    wpbs("select.wpbs-day-"+(changeDate.getDate())).parents('li').find('span.wpbs-day-split-bottom').removeClass().addClass('wpbs-day-split-bottom wpbs-day-split-bottom-' + selectStatus);
                    
                    wpbs("select.wpbs-day-"+(changeDate.getDate())).parents('li').find(".wpbs-input-description").val(bookingDetails);
                    
                    wpbs(".wpbs-calendars li.wpbs-day-" + changeDate.getDate()).removeClass().addClass('wpbs-day wpbs-day-' + changeDate.getDate() + ' status-' + selectStatus);
                    wpbs(".wpbs-calendars li.wpbs-day-" + changeDate.getDate() + " span.wpbs-day-split-top").removeClass().addClass('wpbs-day-split-top wpbs-day-split-top-' + selectStatus);
                    wpbs(".wpbs-calendars li.wpbs-day-" + changeDate.getDate() + " span.wpbs-day-split-bottom").removeClass().addClass('wpbs-day-split-bottom wpbs-day-split-bottom-' + selectStatus);
                }
               
                var currentYear = 'year' + changeDate.getFullYear();
        		var currentMonth = 'month' + (changeDate.getMonth()+1);
        		var currentDay = 'day' + (changeDate.getDate());
                
                var currentTimestamp = wpbs(".wpbs-current-timestamp").html();
                var currentDate = new Date(currentTimestamp * 1000);
                var currentMonth = changeDate.getMonth()+1;
                var currentYear = changeDate.getFullYear();
                var currentDay = changeDate.getDate();
                
                
                if (!(currentYear in wpbsCalendarData)) {
        			wpbsCalendarData[currentYear] = {};
        		}
        		
        		if (!(currentMonth in wpbsCalendarData[currentYear])) {
        			wpbsCalendarData[currentYear][currentMonth] = {};
        		}
                wpbsCalendarData[currentYear][currentMonth][currentDay] = selectStatus;
                wpbsCalendarData[currentYear][currentMonth]['description-' + currentDay] = wpbs_htmlEscape( bookingDetails );
                
                wpbs("span.error").css('display','none');
            }
        } else {
            wpbs("span.error").css('display','block');
        }
        wpbs(".wpbs-calendar-data").attr('data-info',JSON.stringify(wpbsCalendarData));
        wpbs("#inputCalendarData").val(JSON.stringify(wpbsCalendarData));        
    })
    

    wpbs(document).on('change',".wpbs-day-select",function(e){
        
        var wpbsCalendarData = wpbs(".wpbs-calendar-data").attr('data-info');
        if (!wpbsCalendarData || wpbsCalendarData == 'null' )
            wpbsCalendarData = {};
        else {
            wpbsCalendarData = wpbsCalendarData.stripSlashes();
            wpbsCalendarData = JSON.parse(wpbsCalendarData);
        }
        var currentTimestamp = wpbs(".wpbs-current-timestamp").html();
        var currentDate = new Date(currentTimestamp * 1000);
        var currentMonth =  wpbs(this).attr('data-month').replace('wpbs-month-','');
        var currentYear =  wpbs(this).attr('data-year').replace('wpbs-year-','');
        var currentDay = wpbs(this).attr('data-name').replace('wpbs-day-','');
        var selectStatus = wpbs(this).val();
        
        if (!(currentYear in wpbsCalendarData)) {
			wpbsCalendarData[currentYear] = {};
		}
		
		if (!(currentMonth in wpbsCalendarData[currentYear])) {
			wpbsCalendarData[currentYear][currentMonth] = {};
		}
        wpbsCalendarData[currentYear][currentMonth][currentDay] = selectStatus;

        //change colors
        
        wpbs(this).parent().find('span.wpbs-select-status').removeClass().addClass('wpbs-select-status status-' + selectStatus);
        wpbs(this).parent().find('span.wpbs-day-split-top').removeClass().addClass('wpbs-day-split-top wpbs-day-split-top-' + selectStatus);
        wpbs(this).parent().find('span.wpbs-day-split-bottom').removeClass().addClass('wpbs-day-split-bottom wpbs-day-split-bottom-' + selectStatus);
        
        if(wpbs(this).parents(".wpbs-modal-box").length == 0){     
            wpbs(".wpbs-calendar li.wpbs-day-" + currentDay).removeClass().addClass('wpbs-day wpbs-day-' + currentDay + ' status-' + selectStatus);
            wpbs(".wpbs-calendar li.wpbs-day-" + currentDay + " span.wpbs-day-split-top").removeClass().addClass('wpbs-day-split-top wpbs-day-split-top-' + selectStatus);
            wpbs(".wpbs-calendar li.wpbs-day-" + currentDay + " span.wpbs-day-split-bottom").removeClass().addClass('wpbs-day-split-bottom wpbs-day-split-bottom-' + selectStatus);
        }
        
        
        wpbs(".wpbs-calendar-data").attr('data-info',JSON.stringify(wpbsCalendarData));
        wpbs("#inputCalendarData").val(JSON.stringify(wpbsCalendarData));
       
    })
    
    wpbs(document).on('keyup',".wpbs-input-description",function(e){
        var wpbsCalendarData = wpbs(".wpbs-calendar-data").attr('data-info');

        if (!wpbsCalendarData)
            wpbsCalendarData = {};
        else {
            wpbsCalendarData = wpbsCalendarData.stripSlashes();
            wpbsCalendarData = JSON.parse(wpbsCalendarData);
        }
        var currentTimestamp = wpbs(".wpbs-current-timestamp").html();
        var currentDate = new Date(currentTimestamp * 1000);
        var currentMonth =  wpbs(this).attr('data-month').replace('wpbs-month-','');
        var currentYear =  wpbs(this).attr('data-year').replace('wpbs-year-','');
        var currentDay = wpbs(this).attr('data-name').replace('wpbs-day-','');
        var selectStatus = wpbs(this).val();
        
        if (!(currentYear in wpbsCalendarData)) {
			wpbsCalendarData[currentYear] = {};
		}
		
		if (!(currentMonth in wpbsCalendarData[currentYear])) {
			wpbsCalendarData[currentYear][currentMonth] = {};
		}
        wpbsCalendarData[currentYear][currentMonth]['description-' + currentDay] = wpbs_htmlEscape(selectStatus);
        
        wpbs(".wpbs-calendar-data").attr('data-info',JSON.stringify(wpbsCalendarData));
        wpbs("#inputCalendarData").val(JSON.stringify(wpbsCalendarData));
       
    })
    
    wpbs(".saveCalendar").click(function(){
        
        if (!wpbs.trim(wpbs(".fullTitle").val()).length) {
            wpbs(".fullTitle").addClass('error').focus();
            return false;
        }
        return true;
        
    })
    
    wpbs(document).on('click','.bulk-edit-legend-apply',function(){
        wpbs(".edit-dates-popup select").each(function(){
            wpbs(this).val(wpbs('.bulk-edit-legend-select').val()).trigger('change');
        })
        wpbs(".edit-dates-popup input").each(function(){
            wpbs(this).val(wpbs('.bulk-edit-legend-text').val()).trigger('keyup');
        })
    })
})

function wpbs_select_text(containerid) {
    if (document.selection) {
        var range = document.body.createTextRange();
        range.moveToElementText(containerid);
        range.select();
    } else if (window.getSelection) {
        var range = document.createRange();
        range.selectNode(containerid);
        window.getSelection().addRange(range);
    }
}