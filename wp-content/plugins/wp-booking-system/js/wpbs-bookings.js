
wpbs(document).ready(function(){
    
    wpbs('.wpbs-bookings-status').each(function(){
        var $tableInstance = wpbs(this);
        var oTable = $tableInstance.find('.wpbs-data-table').dataTable({
            "bPaginate": true,
            "bLengthChange": false,
            "bFilter": true,
            "iDisplayLength": 5,
            "bSort": false,
            "oLanguage": {
              "sEmptyTable": "No bookings in this section."
            },
            "bInfo": false,
            "bAutoWidth": false,
            "fnDrawCallback": function( oSettings ) {  
                //current page
                $tableInstance.find('.wpbs-data-table-current').val(Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1)
                //out of #
                $tableInstance.find(".wpbs-data-table-total").html(Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength));
                //total items
                $tableInstance.find('.wpbs-data-table-total-items').html(oSettings.fnRecordsDisplay() + " items");
            }
        });
        
        
        
        var oSettings = oTable.fnSettings();
        
        //change page number
        wpbs($tableInstance).on('keyup','.wpbs-data-table-current',function(){
            if((parseInt(wpbs(this).val()) - 1) * oSettings._iDisplayLength < oTable.fnSettings().fnRecordsTotal()){
                var iPage = (parseInt(wpbs(this).val()) - 1) * oSettings._iDisplayLength;
                oSettings._iDisplayStart = iPage;                 
                wpbs(oSettings.oInstance).trigger('page', oSettings);
                oSettings.oApi._fnCalculateEnd( oSettings );
                oSettings.oApi._fnDraw( oSettings );
            }
        })
        
        wpbs($tableInstance).on('click','.wpbs-data-table-next-page',function(){
            $tableInstance.find('.paginate_enabled_next').click();
        })
        wpbs($tableInstance).on('click','.wpbs-data-table-last-page',function(){
                oSettings._iDisplayStart = (Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;;    
                wpbs(oSettings.oInstance).trigger('page', oSettings);
                oSettings.oApi._fnCalculateEnd( oSettings );
                oSettings.oApi._fnDraw( oSettings );
        })
        wpbs($tableInstance).on('click','.wpbs-data-table-prev-page',function(){
            $tableInstance.find('.paginate_enabled_previous').click();
        })
        wpbs($tableInstance).on('click','.wpbs-data-table-first-page',function(){
                oSettings._iDisplayStart = 0;           
                wpbs(oSettings.oInstance).trigger('page', oSettings);
                oSettings.oApi._fnCalculateEnd( oSettings );
                oSettings.oApi._fnDraw( oSettings );
        })
    })
    
    wpbs(".wpbs-bookings-status").hide();

    wpbs('.wpbs-bookings-container').on('click','.wpbs-bookings-tabs a', function(e){
        e.preventDefault();
        
        
        wpbs(".wpbs-bookings-status").hide();
        wpbs(wpbs(this).attr('href')).show();
        
        wpbs(".wpbs-bookings-tabs a").removeClass('active');
        wpbs(this).addClass('active');
        
        if(wpbs(this).attr('href') == '#wpbs-bookings-accepted'){
            clickPage = Math.ceil(wpbs("#wpbs-past-accepted-bookings").html() / 5);
            if(clickPage < 1) clickPage = 1;
            wpbs("#wpbs-bookings-accepted .wpbs-data-table-current").val(clickPage).trigger('keyup');
        }
        
    })
    
    wpbs(".wpbs-bookings-tabs a:first").click();
    
    
    wpbs(".wpbs-bookings-container").on('click','.wpbs-booking-open-options',function(e){
        e.preventDefault();
        $parent = wpbs(this).parent();
        if($parent.hasClass('wpbs-booking-field-read-0')){
            $parent.removeClass('wpbs-booking-field-read-0');
            var data = {
                bookingID : $parent.attr('id').replace('wpbs-booking-field-',''),
                action: 'wpbs_bookingMarkAsRead',
        	};
            wpbs.post(ajaxurl, data);
        }
        wpbs(".wpbs-booking-field.open .wpbs-booking-field-options").slideToggle();  
        if($parent.hasClass('open')){
            wpbs(".wpbs-booking-field.open").removeClass("open");
        } else {
            wpbs(".wpbs-booking-field.open").removeClass("open");
            $parent.addClass('open');
            $parent.find('.wpbs-booking-field-options').slideToggle();    
        }
          
    })

    
    wpbs(".wpbs-button-delete").click(function(e){
        e.stopPropagation();
    })
    wpbs(".wpbs-accept-booking").click(function(e){
        wpbs('#wpbs_send_confirmation_message').val( wpbs('.wpbs-modal-box .send-confirmation-message').val() );
        wpbs('#wpbs_confirmation_message').val( wpbs('.wpbs-modal-box .send-confirmation-message-additional').val() );
        // wpbs(".saveCalendar").click();
        wpbs(".wpbs-modal-overlay").fadeTo(300,0,function(){
            wpbs(".wpbs-modal-overlay").hide();
        })
    })
    var originalData = '';
    
    wpbs(".wpbs-close-modal").click(function(e){
        wpbs(".wpbs-calendar-data").html(originalData);
        wpbs("#inputCalendarData").val(originalData);
        
        wpbs('#wpbs_booking_action, #wpbs_booking_id').val('')
        
        
        wpbs(".wpbs-modal-overlay").fadeTo(300,0,function(){
            wpbs(".wpbs-modal-overlay").hide();
        })
    });
    
    
    wpbs('.wpbs-bookings-container').on('click','.wpbs-button-accept', function(e){
        e.stopPropagation();
        e.preventDefault();
        originalData = wpbs(".wpbs-calendar-data").html();        
        
        $button = wpbs(this);

        $button.closest('td').addClass('wpbs-booking-selected');

        wpbs(".wpbs-modal-box-container").html('');
        
        wpbs('#wpbs_booking_action').val( $button.attr('data-action') )
        wpbs('#wpbs_booking_id').val( $button.attr('data-booking-id') )
        
        if($button.attr('data-action') == "accept") {
            wpbs('.wpbs-accept-booking').html(wpbs_accept_booking);   
            wpbs('.wpbs-modal-box-header h2').html(wpbs_accept_booking + ' - ' + wpbs_edit_availatility); 
        }
        if($button.attr('data-action') == "delete") {
            wpbs('.wpbs-accept-booking').html(wpbs_delete_booking);
            wpbs('.wpbs-modal-box-header h2').html(wpbs_delete_booking + ' - ' + wpbs_edit_availatility);
        }
        
        if($button.attr('data-action') == "edit") {
            wpbs('.wpbs-accept-booking').html(wpbs_edit_booking);
            wpbs('.wpbs-modal-box-header h2').html(wpbs_edit_booking + ' - ' + wpbs_edit_availatility);
        }
        

        var from    = $button.closest('div.wpbs-bookings-status').attr('id').replace('wpbs-bookings-','');

        var data = {
            startDate       : $button.attr('data-start'),
            endDate         : $button.attr('data-end'),
            calendarID      : $button.attr('data-id'),
            bookingID       : $button.attr('data-booking-id'),
            buttonAction    : $button.attr('data-action'),
            from            : from,
    		action          : 'wpbs_bookingModalData',
    	};
    	wpbs.post(ajaxurl, data, function(response) {
    		 wpbs(".wpbs-modal-box-container").html(response);
             wpbs(".wpbs-modal-overlay").css('opacity',0).show().fadeTo(300,1);
    	});
       
    })
    
    wpbs(document).on('change','.send-confirmation-message', function(e){

        if(wpbs(this).val() == 1){
            wpbs('.send-confirmation-message-options').show();
        } else {
            wpbs('.send-confirmation-message-options').hide();
        }
    })
    

    
})
    