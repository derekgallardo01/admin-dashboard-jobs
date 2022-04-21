/**
 *
 * Plugin to search users in the radio
 * Required: 
 * 		-> jQuery
 * 		-> Google maps Api (Geocoder)
 *
 ******/
var USPgl_defaults = {
    geocoder: null,
    allUsers: [],
    urlGetUsers: null,
    requestParams: null,
    radius: 2,
    messageResult: 'It has established the following address as search center: "{address}", with a radius of {radius} mi.',
    eMessageResult: 'msg-result',
    btnTriggerActionSearch: 'search-therapists',
    inputKeyword: 'search_keyword',
    inputCity: 'search_city',
    inputState: 'search_state',
    eLoading: 'loading-search-users',
    usersFound: []
};


if(typeof(_configUSPgl) === 'undefined') {
	var _configUSPgl = USPgl_defaults;
} else {
    _configUSPgl = jQuery.extend({}, USPgl_defaults, _configUSPgl);
}

google.maps.event.addDomListener(window, 'load', initUsersSearchPgl);
//jQuery(document).on('ready', initUsersSearchPgl);

function initUsersSearchPgl() {
	_configUSPgl.geocoder = new google.maps.Geocoder();

	var btnTriggerAction = document.getElementById(_configUSPgl.btnTriggerActionSearch);

	if(btnTriggerAction !== null) {
		jQuery(btnTriggerAction).on('click', USPgl_search_therapists);
	}
	

	//Load users
	USPgl_loadUsers();
}

function USPgl_loadUsers() {
    if(_configUSPgl.urlGetUsers && _configUSPgl.requestParams) {
    	jQuery.ajax({
    		type: 'post',
    		url: _configUSPgl.urlGetUsers,
    		data: _configUSPgl.requestParams,
    		dataType: 'json',
    		success: function(response) {
    			if(response instanceof Array) {
    				_configUSPgl.allUsers = response;
    			}
    		}
    	});
    }
}


/** Search Functions **/
function USPgl_search_therapists(e) {
    e.preventDefault();
    USPgl_processLocationSearchUsers();
}
function USPgl_processLocationSearchUsers() {
    var address = '';
    var zipcode = '';
    var search = '';
    
    var elementKeyword = document.getElementById(_configUSPgl.inputKeyword);

    zipcode = jQuery(elementKeyword).val();
    
    if(jQuery.trim(zipcode).length > 0) {
        
        var valid = /^\d{5}$/;
        if(valid.test(zipcode)) {
            search = zipcode;
        } else {
            var keyword = zipcode.toLowerCase();
            var users = USPgl_getResultUsersByKeyword(keyword);
            USPgl_showResult(users);
            return;
        }
        
    } else {
        
        var elementCity = document.getElementById(_configUSPgl.inputCity);
        var _search_city = jQuery(elementCity).val();

        var elementState = document.getElementById(_configUSPgl.inputState);
        var _search_state = jQuery(elementState).val();
        address = _search_city+','+_search_state;
        
        if(jQuery.trim(_search_city).length === 0 && jQuery.trim(_search_state).length > 0) {
            address = _search_state;
        }
        
        search = address;
    }
    var elementLoading = document.getElementById(_configUSPgl.eLoading);
    if(elementLoading !== null) {
        jQuery(elementLoading).show();
    }
    _configUSPgl.geocoder.geocode( { 'address': search }, USPgl_searchingBaseLocation);
}
function USPgl_searchingBaseLocation(results, status) {
    var elementLoading = document.getElementById(_configUSPgl.eLoading);
    if(elementLoading !== null) {
        jQuery(elementLoading).hide();
    }
    
    if (status == google.maps.GeocoderStatus.OK) {
        var base_location = results[0];
        
        //map.setCenter(base_location.geometry.location);
        
        //removeMarkers();
        
        //var blueIcon = "http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png";
        
        //markerCenter = addMarker(base_location.geometry.location, base_location.formatted_address, blueIcon);
        //all_markers.markers.push(markerCenter);
        //base_circle = addCircle(markerCenter);

        var markerCenter = new google.maps.Marker({position: base_location.geometry.location,title: ''});
        var radius = parseInt(_configUSPgl.radius);
        var base_circle = new google.maps.Circle({
            radius: (radius/0.6214)*1000// metres
        });
	    // attach circle to marker
	    base_circle.bindTo('center', markerCenter, 'position');

        

        var elementMR = document.getElementById(_configUSPgl.eMessageResult);
        if(elementMR !== null) {
            var msg = _configUSPgl.messageResult.replace('{address}',base_location.formatted_address);
            msg = msg.replace('{radius}',radius);
            jQuery(elementMR).addClass('USPgl_islook').html(msg).fadeIn('fast');
        }
        
        USPgl_getResultUsersInRadius(base_circle);// init process search users
    } else {
        if(status == 'ZERO_RESULTS') {
            jQuery(document).trigger('uspgl_zero_results');
            jQuery('.USPgl_islook').hide();
            alert('No results were found');
        } else {
            alert('Geocode was not successful for the following reason: ' + status);
        }
    }
}

//aqui se hace la busqueda por radio
function USPgl_getResultUsersInRadius(base_circle) {
    var bounds = base_circle.getBounds();
    _configUSPgl.usersFound = [];
    USPgl_next(0,bounds)
}
function USPgl_next(index,bounds) {
	
	if(index >= _configUSPgl.allUsers.length) {
		USPgl_showResult(_configUSPgl.usersFound);
		return;
	}

	var user = _configUSPgl.allUsers[index];

	if(user.latidude && user.longitude) {
    	var latLng = new google.maps.LatLng(user.latidude, user.longitude);
    	if(bounds.contains(latLng)) {
	        _configUSPgl.usersFound.push(user);
	    }
	    USPgl_next(index+1,bounds);
    } else if(user.user_address) {
    	/********/
    	var USPgl_searchingLatLng = function(results, status) {
            
		    if (status == google.maps.GeocoderStatus.OK) {
		    	var base_location = results[0];
        		var location = base_location.geometry.location;

		    	var latLng = new google.maps.LatLng(location.lat(), location.lng());

		    	if(bounds.contains(latLng)) {
		    		user.latidude = latLng.lat();
		    		user.longitude = latLng.lng();
                    console.log('Add.. '+user.user_login);
			        _configUSPgl.usersFound.push(user);
			    }
		    }
		    USPgl_next(index+1,bounds);
		};//------------------------------------
    	_configUSPgl.geocoder.geocode( { 'address': user.user_address }, USPgl_searchingLatLng);
    } else {
    	USPgl_next(index+1,bounds);
    }
	
}

if(typeof(USPgl_getResultUsersByKeyword) !== 'function') {
	var USPgl_getResultUsersByKeyword = function(keyword) {
	    
	    var users = [];
	    var regstr_container = '^(.*)'+keyword+'(.*)$';
	    var regex = RegExp(regstr_container);
	    
	    var user;
	    for(var index in _configUSPgl.allUsers) {
	        user = _configUSPgl.allUsers[index];
	        
	        var user_name = user.display_name.toLowerCase();
	        var full_name = user.full_name ? user.full_name.toLowerCase() : user_name;
	        var user_position = user.user_position ? user.user_position.toLowerCase() : user_name;
	        var user_department = user.user_department ? user.user_department.toLowerCase() : user_name;
	        
	        if(regex.test(user_name) || regex.test(full_name) || regex.test(user_position) || regex.test(user_department)) {
	            users.push(user);
	        }
	    }
	    return users;
	};
}




if(typeof(USPgl_showResult) !== 'function') {

    var USPgl_showResult = function(users) {
        var wrapperResult = jQuery('#results-users');
        
        if(users.length > 0) {
            
            wrapperResult.children().removeClass('hidden-block');
            
            var ul = jQuery('<ul id="list-result">');
            
            wrapperResult.children('.count-results').children('.count').html(users.length);
            
            for(var i in users) {
                user = users[i];
                num = i%2 === 0 ? 'a' : 'b';
                ul.append('<li class="line-'+num+'"><label><input type="checkbox" name="therapists[]" class="found_therapists" value="'+user.ID+'" />'+(user.full_name ? user.full_name : user.display_name)+'</label></li>');
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