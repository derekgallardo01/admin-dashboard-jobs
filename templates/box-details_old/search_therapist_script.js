var geocoder;

var map;



var all_markers = {circles: [], markers: []};

var markers_results = [];

var _markerCluster = null;



var zoom_state = 6;

var zoom_city = 10;

var _zoom = 4;



var _ajax_started = false;



var _onlyInputZipCode;



/** Logic code **/

jQuery(document).on('ready', init_ready_box);



function init_ready_box() {

    var objTabs = jQuery('#tabs-therapist-box');

    if(typeof(objTabs.tabs) === 'function') {

        objTabs.tabs();

    }



    if(!mjobs.therapists) {

        //loadAllTherapists();

    }



    jQuery('#search-therapists').on('click', search_therapists);

    

    jQuery('#results-therapists .select-all label').on('click', function(){

        jQuery('#list-result').find('.found_therapists').attr('checked', jQuery(this).children('.found_therapists').is(':checked'));

    });

    

    

    jQuery('#asgn_location').on('change', mark_border);



    var modalTabs = jQuery('#popup-editor-notifications');

    if(typeof(modalTabs.tabs) === 'function') {

        modalTabs.tabs();

    }

    



    /*** Modal Dialog for Customize SMS ***/

    var _dialog = jQuery('#dialog-send-sms');

    _dialog.dialog({

        autoOpen: false,

        modal: true,

        minWidth: 600,

        minHeight: 380,

        close: function() {

            

        },

        buttons: [

            { text:'Send Notification', 'class':'button-primary', click: function(){ sendNotification(); } },

            { text:'Cancel', 'class':'', click: function(){ _dialog.dialog('close'); } }

        ]

    });

    /*** end sms modal dialog ***/





    /**

     * Tab 2

     ******/

     

    /** Upload file **/

    var custom_uploader;

    var upload_file = function(e) {

        e.preventDefault();

        /*

        If the uploader object has already been created, reopen the dialog

        if (custom_uploader) {

            custom_uploader.open();

            return;

        }

        */

        custom_uploader = wp.media.frames.file_frame = wp.media({

                title: 'Choose File',

                button: {

                    text: 'Choose File'

                },

                multiple: false

            });

        var input_file = jQuery('#path_file_'+jQuery(this).attr('user'));

        var uploader = function() {

            attachment = custom_uploader.state().get('selection').first().toJSON();

            input_file.val(attachment.url.replace(window.location.origin,''));

        };

        //When a file is selected, grab the URL and set it as the text field's value

        custom_uploader.on('select', uploader);

        //Open the uploader dialog

        custom_uploader.open();

    };

    jQuery('.upload_file_button').on('click', upload_file);

    

    jQuery('#send-invoice').on('click', send_invoice);



    var radio_method_payments = jQuery('input[name="method_payment"]');

    var change_method_payment = function() {

        radio_method_payments.parent('.wimg').removeClass('selected');

        jQuery(this).parent('.wimg').addClass('selected');

    };

    radio_method_payments.on('change', change_method_payment);



    /** end tab 2 *******************************/

}





function loadTherapistsForSearch(state, city, zip) {

    jQuery.ajax({

        data: { action: 'alltherapists', state:state, city:city, zip:zip },

        url: ajaxurl,

        type: 'post',

        dataType: 'json',

        error: function (request, status, error) {

            alert(request.responseText);

            _ajax_started = false;

        },

        success: function(response) {

            mjobs.therapists = response;



            _ajax_started = false;



            var _search_city = jQuery('#search_city').val();

            var _search_state = jQuery('#search_state').val();

            var zipcode = jQuery('#search_keyword').val();

             var keyword = zipcode.toLowerCase();
					
                    var users = getResultTherapistsByKeyword(keyword);
				//alert(users);
				//console.dir(users)
                    showResult(users);

            if( _onlyInputZipCode ) {

                var valid = /^\d{5}$/;

                if(valid.test(zipcode)) {

                    search = zipcode;

                } else {

                    var keyword = zipcode.toLowerCase();
					
                    var users = getResultTherapistsByKeyword(keyword);
				//alert(users);
                    showResult(users);

                    loadingImg.removeClass('show');

                    return;

                }

                

            } else {

                address = _search_city+','+_search_state;

                

                if(jQuery.trim(_search_city).length === 0 && jQuery.trim(_search_state).length > 0) {

                    address = _search_state;

                    _zoom = zoom_state;

                }

                

                search = address;

            }



            geocoder.geocode( { 'address': search}, searchingBaseLocation);

        }

    });

}



function customizeSMSBeforeSend() {



    var locationId = jQuery('#asgn_location').val();

    var therapists_selecteds = jQuery('#list-result .found_therapists:checked');



    if(locationId.length <= 0) {

        jQuery('#dropdown-location-message').text('Please, select one Location from the dropdown');

        jQuery('#asgn_location').trigger('focus');

        return;

    }

    

    if(therapists_selecteds.length <= 0) {

        jQuery('#therapist-select-validation').show();

        jQuery('#select-all-therapists').focus();

        return;

    }



    jQuery('#therapist-select-validation').hide();



    var _dialog = jQuery('#dialog-send-sms');

    _dialog.dialog('open');

}



/** Function to send notification ***/

function sendNotification() {



    var _this = this;

    

    var locationId = jQuery('#asgn_location').val();

    

    var therapists_selecteds = jQuery('#list-result .found_therapists:checked');

    

    var therapists = {};

    

    therapists_selecteds.each(function(idx, domElement){

        var _domThis = jQuery(domElement);

        var id = _domThis.val();

        var text = _domThis.parent().text();

        therapists[id] = text;

    });



    //read content messages for email and SMS

    var ifr = document.getElementById('email-invitation-content_ifr');

    var txt_email = ifr.contentDocument.body.innerHTML;

    var txt_sms = jQuery('#sms-message').val();
	
	var is_send_sms = '';
	if(jQuery('#is_send_sms_notify').prop('checked')) {
    is_send_sms = jQuery('#is_send_sms_notify').val();
	}
	
	var is_send_mail = '';
	if(jQuery('#is_send_mail_notify').prop('checked')) {
    is_send_mail = jQuery('#is_send_mail_notify').val();
	}
	
    var params = {
        action: 'assign_therapist',
        location_id: locationId,
        therapists_asgn: therapists,
        txt_email: txt_email,
        txt_sms: txt_sms,
		is_send_sms: is_send_sms,
        is_send_mail: is_send_mail,
    };

    

    var _dialog = jQuery('#dialog-send-sms');

    _dialog.dialog('close');



    jQuery('#tabs-therapist-box #tab-1 #results-therapists .ajax-loading').addClass('show');

    jQuery.ajax({

        data: params,

        url: ajaxurl,

        type: 'post',

        dataType: 'json',

        success: function(result) {

            jQuery('#tabs-therapist-box #tab-1 #results-therapists .ajax-loading').removeClass('show');

            if(result.status == 'OK') {

                alert(result.message);
				alert("message send successfully");
               // window.location.reload();

            } else if(result.status == 'error') {

                alert(result.message);

            }

            

        },

        error: function() {

            window.location.reload();

        }

    });

}



/** Search Functions **/

function search_therapists(e) {

    e.preventDefault();

    processLocationSearchTherapists();

}



function processLocationSearchTherapists() {



    if(_ajax_started) {

        alert('Please wait a minute, we are still processing results!');

        return false;

    } else {

        _ajax_started = true;

    }



    var address = '';

    var zipcode = '';

    var search = '';



    var _search_city = jQuery('#search_city').val();

    var _search_state = jQuery('#search_state').val();

    

    zipcode = jQuery('#search_keyword').val();

    _zoom = zoom_city;

    

    _onlyInputZipCode = _search_state.length === 0 && _search_city.length === 0 && jQuery.trim(zipcode).length > 0;



    var loadingImg = jQuery('#loading-search-therapist');

    loadingImg.addClass('show');



    //TODO: add here the ajax synchonous call to load the therapists only for the selected state and city

    loadTherapistsForSearch( _search_state, _search_city, zipcode );



}





var searchingBaseLocation = function(results, status) {

    var loadingImg = jQuery('#loading-search-therapist');



    loadingImg.removeClass('show');

    if (status == google.maps.GeocoderStatus.OK) {



        var base_location = results[0];

        

        map.setCenter(base_location.geometry.location);

        

        removeMarkers();

        

        var blueIcon = "http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png";

        

        markerCenter = addMarker(base_location.geometry.location, base_location.formatted_address, blueIcon);

        all_markers.markers.push(markerCenter);

        var base_circle = addCircle(markerCenter);

        var zipcode = jQuery('#search_keyword').val();

        

        var users = getResultTherapistsInRadius(base_circle,zipcode);

        //console.log( markers_results );

        _markerCluster = new MarkerClusterer(map, markers_results);

        //var markerCluster = new MarkerClusterer(map, users, mcOptions);



        var msg = msg_result_search.replace('{address}',base_location.formatted_address);

        msg = msg.replace('{radius}',mjobs.radius);

        jQuery('#msg-result').html(msg).fadeIn('fast');

        

        showResult(users);

        

    } else if(status == 'ZERO_RESULTS') {

        jQuery(document).trigger('zero_results_search');

        alert('No results were found');

    } else {

        alert('Geocode was not successful for the following reason: ' + status);

    }

};





if(typeof(showResult) !== 'function') {

    var loadGoogleAddress = function() {

        var btn = null, lat = null, lng = null;



        if(arguments.length == 3) {

            btn = arguments[0];

            btn = jQuery(btn);

            lat = arguments[1];

            lng = arguments[2];

        } else if(arguments.length == 2) {

            lat = arguments[0];

            lng = arguments[1];

        } else {

            return;

        }



        if(btn && btn.prop('disabled')) {

            return;

        }



        var show = function(message) {



            var eDiv = document.getElementById('jqueryshow');

                

            if(eDiv) {

                jQuery(eDiv).remove();

            }

            var divView = jQuery('<div id="jqueryshow" title="Address aproximate">');

            

            if( typeof(divView.dialog) === 'function' ) {

                jQuery(document.body).append(divView);

            }



            if( typeof(divView.dialog) === 'function' ) {

                divView.html(message);

                divView.dialog({modal: true});

            } else {

                alert(message);

            }

        };



        var resultAddress = function(results, status) {

            

            if (status == google.maps.GeocoderStatus.OK) {

                var base_location = results[0];



                show(base_location.formatted_address);

            } else {

                console.log(status);

            }

            if(btn) {

                btn.prop('disabled', false).text(btn.attr('txt'));

            }

        };





        if(btn) {

            btn.prop('disabled', true).attr('txt', btn.text()).text('Loading...');

        }

        var latlng = new google.maps.LatLng(lat, lng);



        geocoder.geocode( { 'latLng': latlng }, resultAddress);



    };

    var showResult = function(users) {

        var wrapperResult = jQuery('#results-therapists');

        

        if(users.length > 0) {

            

            wrapperResult.children().removeClass('hidden-block');

            

            var ul = jQuery('<ul id="list-result">');

            

            wrapperResult.children('.count-results').children('.count').html(users.length);

            var user, num, address;

            for(var i in users) {

                user = users[i];

                num = i%2 === 0 ? 'a' : 'b';



                address = '';

                if(user.user_address) {

                    address = '<small class="adrs">'+user.user_address+'</small>';

                } else {

                    address = '<small class="link"><a href="javascript:void(0);" onclick="loadGoogleAddress(this,'+user.latidude+','+user.longitude+')">View address</a></small>';

                }
				var substext = '';
				if(user.unsubscribeEmail=='yes'){
					substext = ' ( unsubscribed For Email ) ';
				}
				if(user.unsubscribeSMS=='yes'){
					substext += ' ( unsubscribed For SMS ) ';
				}

                ul.append('<li class="line-'+num+'"><label><input type="checkbox" name="therapists[]" class="found_therapists" value="'+user.ID+'" /><span>'+(user.full_name ? user.full_name : user.display_name)+'</span> '+substext+' '+address+'</label></li>');

            }

            wrapperResult.children('.box-results').html(ul);

        

        } else {

            wrapperResult.children().addClass('hidden-block');

            var count_results = wrapperResult.children('.count-results');

            count_results.removeClass('hidden-block');

            count_results.children('.count').html('0');

        }

    };

}

//aqui se hace la busqueda por radio

function getResultTherapistsInRadius(base_circle) {

    var bounds = base_circle.getBounds();

    var keyword = null, user_name, full_name, user_position, user_department, regex, regstr_container;

    if(arguments.length == 2 && arguments[1].length > 0) {

        keyword = arguments[1];

        regstr_container = '^(.*)'+keyword+'(.*)$';

        regex = RegExp(regstr_container);

    }

    var regexZipCode = /^\d{5}$/;

    

    var users = [], user, latLng;

    for(var pos in mjobs.therapists) {

        user = mjobs.therapists[pos];

        latLng = new google.maps.LatLng(user.latidude, user.longitude);





        if(keyword !== null && !regexZipCode.test(keyword)) {



            user_name = user.display_name.toLowerCase();

            full_name = user.full_name ? user.full_name.toLowerCase() : user_name;

            user_position = user.user_position ? user.user_position.toLowerCase() : user_name;

            user_department = user.user_department ? user.user_department.toLowerCase() : user_name;

            

            if(bounds.contains(latLng) &&

                    (

                        regex.test(user_name) ||

                        regex.test(full_name) ||

                        regex.test(user_position) ||

                        regex.test(user_department)

                    )

                ) {

                resultMarker = addMarker(latLng, user.display_name);

                markers_results.push(resultMarker);

                users.push(user);

            }

        } else if(bounds.contains(latLng)) {

            resultMarker = addMarker(latLng, user.display_name);

            markers_results.push(resultMarker);

            users.push(user);

        }

    }

    return users;

}

function getResultTherapistsByKeyword(keyword) {

    

    var users = [];

    var regstr_container = '^(.*)'+keyword+'(.*)$';

    var regex = RegExp(regstr_container);

    var latLng, user_name, full_name, user_position, user_department;
	
	//console.dir(mjobs.therapists);

    for(var pos in mjobs.therapists) {

        user = mjobs.therapists[pos];

        latLng = new google.maps.LatLng(user.latidude, user.longitude);

        

        user_name = user.display_name.toLowerCase();

        full_name = user.full_name ? user.full_name.toLowerCase() : user_name;

        user_position = user.user_position ? user.user_position.toLowerCase() : user_name;

        user_department = user.user_department ? user.user_department.toLowerCase() : user_name;

        

        //if(regex.test(user_name) || regex.test(full_name) || regex.test(user_position) || regex.test(user_department)) {

            resultMarker = addMarker(latLng, user.display_name);

            markers_results.push(resultMarker);

            users.push(user);

        //}

    }

    return users;

}



/** Maps Functions **/

function addMarker(location,title_marker) {

    addMarker(location, title_marker, null);

}

function addMarker(location, title_marker, iconMarker) {

    marker = new google.maps.Marker({map: map,position: location,title: title_marker,icon: iconMarker});

    return marker;

}

function removeMarkers() {

    for(var i = 0; i < all_markers.markers.length; i++) {

        all_markers.markers[i].setMap(null);

        all_markers.circles[i].setMap(null);

    }

    for(var j = 0; j < markers_results.length; j++) {

        markers_results[j].setMap(null);

    }



    markers_results = [];

    

    if(_markerCluster!==null)

        _markerCluster.clearMarkers();

}

function addCircle(markerCenter) {

    var radio = (mjobs.radius/0.6214)*1000; // convert Miles to meters

    circle = new google.maps.Circle({

            map: map,

            clickable: false,

            // metres

            radius: radio,

            fillColor: '#fff',

            fillOpacity: 0.6,

            strokeColor: '#313131',

            strokeOpacity: 0.4,

            strokeWeight: 0.8

        });

    // attach circle to marker

    circle.bindTo('center', markerCenter, 'position');

    all_markers.circles.push(circle);

    map.setZoom(_zoom);

    return circle;

}



/** Onchange function on Location dropdown **/

function mark_border() {

    //remove text validation

    jQuery('#dropdown-location-message').text('');





    var _this = jQuery(this);

    var id = _this.val();

    jQuery('#view_location').attr('href', id.length > 0 ? '#info_location_'+id : '#tab-1');

    jQuery('#info_location_'+id).addClass('b-selected');

    var _options = _this.children('option');

    _options.each(function(idx, domEle){

        var _val = jQuery(domEle).val();

        if(_val != id) {

            jQuery('#info_location_'+_val).removeClass('b-selected');

        }

    });

}





/**

 *

 * Function tab 2

 *

 ***********************/



function send_invoice(e) {

    e.preventDefault();

    

    var payment = jQuery('input[name="method_payment"]:checked').val();



    var list = jQuery('.upload_file_button');

    var valid = true;

    var current_valid;



    var datos = [];



    for(var user_id in usersInvoices) {



        var listMinutes = usersInvoices[user_id];

        //var _this = jQuery(domElement);

        //var user_id = _this.attr('user');



        if(listMinutes.length > 0) {

            var listItems = [];



            for(var idx in listMinutes) {



                var dat = listMinutes[idx];



                current_valid = true;



                var objPriceInvoice = jQuery('#price_invoice_'+user_id+'_'+dat.location_date_id);

                var price_invoice = jQuery.trim(objPriceInvoice.val());

            

                if( valid && !(/^\d+(\.\d+)?$/.test(price_invoice)) ) {

                    alert('The price must be a number');

                    objPriceInvoice.addClass('sm-error');

                    current_valid = valid = false;

                } else {

                    objPriceInvoice.removeClass('sm-error');

                }

                

                if(current_valid) {

                    listItems.push({price: price_invoice, location_date_id: dat.location_date_id, description: dat.description});

                }

            }// end for

            var objPathFile = jQuery('#path_file_'+user_id);

            var path_file = jQuery.trim(objPathFile.val());

            /* if(payment !== 'freshbooks') {

                if( valid && path_file.length <= 0 ) {

                    alert('You are\'t select file');

                    objPathFile.addClass('sm-error');

                    current_valid = valid = false;

                } else {

                    objPathFile.removeClass('sm-error');

                }

            } else {

                path_file = '';

            } */

            if(valid) {

                datos.push({user: user_id, file: path_file, items: listItems});

            }

        }

    }



    payment = jQuery('input[name="method_payment"]:checked').val();



    if(!payment) {

        alert('You are\'t select payment method');

        jQuery('#wrapper-payment-method').addClass('sm-error');

        valid = false;

    } else {

        jQuery('#wrapper-payment-method').addClass('sm-error');

    }



    if(!valid) {

        return;

    }

    

    var objMsgBody = jQuery('#body-message');

    var msg_body = jQuery.trim(objMsgBody.val());

    if( msg_body.length <= 0 ) {

        alert('The Content Message is empty');

        objMsgBody.addClass('sm-error');

        return;

    }

    

    if( !(/.*\{link_invoice}.*/.test(msg_body)) ) {

        alert('The tag {link_invoice} was not found on the content message');

        objMsgBody.addClass('sm-error');

        return;

    }

    objMsgBody.removeClass('sm-error');



    if(datos.length <= 0) {

        alert('The employee users has not selected a time slot for the service');

        return;

    }



    var params = {

        action: 'send_invoice_to_client',

        items: datos,

        body_message: msg_body,

        entry_id: jQuery('#entry_id').val(),

        method_payment: payment

    };



    var _this = jQuery(this);

    var old_text = _this.text();

    _this.prop('disabled', true).text('Sending...');

    jQuery.ajax({

        data: params,

        type: 'post',

        url: ajaxurl,

        dataType: 'json',

        success: function(reponseJson) {

            _this.prop('disabled', false).text(old_text);

            if(reponseJson.status == 'OK') {

                alert(reponseJson.message);

                renderResults(reponseJson.resultsStatus, 'reSta');

            }else if(reponseJson.status == 'error') {

                alert("ERROR:\n\n"+reponseJson.message);

            }

        },

        error: function() {

            alert('Please, check your internet connection.');

            //window.location.reload();

        }

    });

}



function send_invoice_auto(e) {

    e.preventDefault();





    var price_minute = jQuery('#price_minute');

    var price = jQuery.trim(price_minute.val());

    if( price.length <= 0 ) {

        alert('The price per minute is empty');

        price_minute.addClass('sm-error');

        return;

    }



    price_minute.removeClass('sm-error');



    var objMsgBody = jQuery('#body-message');

    var msg_body = jQuery.trim(objMsgBody.val());

    if( msg_body.length <= 0 ) {

        alert('The Content Message is empty');

        objMsgBody.addClass('sm-error');

        return;

    }

    

    if( !(/.*\{link_invoice}.*/.test(msg_body)) ) {

        alert('The Content Message not found the tag {link_invoice}');

        objMsgBody.addClass('sm-error');

        return;

    }

    objMsgBody.removeClass('sm-error');



    var payment = jQuery('input[name="method_payment"]:checked').val();



    if(!payment) {

        alert('You are\'t select payment method');

        jQuery('#wrapper-payment-method').addClass('sm-error');

        return;

    } else {

        jQuery('#wrapper-payment-method').removeClass('sm-error');

    }

    

    var _this = jQuery(this);

    



    var ferror = function(jqXHR, textStatus, error) {

        console.log(jqXHR);

        console.log(textStatus);

        console.log(error);

        

        _this.prop('disabled', false).text(old_text);

        alert('Please, check your internet connection.');

        window.location.reload();

    };



    var new_users = [];

    

    for(var user_id in usersInvoices) {

        

        var listMinutes = usersInvoices[user_id];



        if(listMinutes.length > 0) {

            var listItems = [];

            for(var idx in listMinutes) {

                //if(listMinutes[idx].minutes > 0)

                listItems.push({

                    location_date_id: listMinutes[idx].location_date_id,

                    minutes: listMinutes[idx].minutes,

                    description: listMinutes[idx].description

                    //minutes: 5

                });

            }

            new_users.push({

                userid: user_id,

                items: listItems

                //minutes: 5

            });

        }

    }

    var lead_id = jQuery('#entry_id').val();



    if(new_users.length <= 0) {

        alert('The employee users has not selected a time slot for the service');

        return;

    }

    

    var old_text = _this.text();

    _this.prop('disabled', true).text('Sending...');



    jQuery.ajax({

        type: 'post',

        data: {

            action: 'generated_invoices',

            users: new_users,

            priceMinute: price,

            lead: lead_id

        },

        url: ajaxurl,

        dataType: 'json',

        success: function(response) {

            if(response.status == 'OK') {

                var params = {

                    action: 'send_invoice_to_client',

                    items: response.datos,

                    body_message: msg_body,

                    entry_id: lead_id,

                    path_full: true,

                    method_payment: payment

                };



                jQuery.ajax({

                    type: 'post',

                    data: params,

                    url: ajaxurl,

                    dataType: 'json',

                    success: function(result) {

                        _this.prop('disabled', false).text(old_text);

                        if(result.status == 'OK') {

                            alert(result.message);

                            renderResults(result.resultsStatus, 'reSta');

                        }

                    },

                    error: ferror

                });

            } else {

                _this.prop('disabled', false).text(old_text);

                alert(response.message);

                renderResults(response.resultsStatus, 'reSta');

            }

        },

        error: ferror

    });

}





function renderResults(results,html_id) {

    var element = document.getElementById(html_id);

    if(element === null || results.length <= 0) {

        return;

    }

    var box_success = "background-color: #dff0d8;border-color: #d6e9c6;color: #468847;";

    var box_error = "background-color: #f2dede;border-color: #eed3d7;color: #b94a48;";

    var re = {};



    var bol = false;



    var idUl = 'render-results-tsm2014';

    var output = '<ul id="'+idUl+'" style="width: 100%;display: none;">';

    for(var i in results) {

        re = results[i];

        if( typeof(re.status) == 'undefined' ) {

            continue;

        }

        bol = true;

        output += '<li style="padding: 10px 5px;'+(re.status == 'OK' ? box_success : box_error)+'">';

            output += '<b>'+re.user_email+'</b>&nbsp;(&nbsp;<span>'+re.message+'</span>&nbsp;)';

        output += '</li>';

    }

    output += '</ul>';



    element.innerHTML = output;



    var eUl = document.getElementById(idUl);

    eUl.style.display = 'block';

    var tout = function() {

        element.removeChild(eUl);

    };

    window.setTimeout(tout,10*1000);//10 seconds

}