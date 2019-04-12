function escapeIsCancel(){
		$(document).keydown(function(e) {
		if (e.which == '27') {
			$('button[bind="click.basicCancelButton"]').trigger('click');
		}
	});
}

/**
 * a unique sufix to make ajax call urls unique to stop ie caching
 * 
 * @returns {String} '/xxxxxxxxxxx'
 */
function getNow(){
	return '?_=' + Date.now().toString();
}

/**
 * Validates a users access to any function based upon id
 * 
 * @param {string} id
 * @returns {none} on valid, fall through, on non-valid, redirect to base version of page
 */
function validateAccess(id) {
	$.ajax({
		type: "POST",
		url: webroot + controller + "validateAccess" + getNow(),
		data: {
			id: id,
		},
		dataType: "json",
		async: false,
		success: function(data) {
			if(data.access == false){
				location.replace(webroot + controller + action);
			}
		},
		error: function(data) {
			location.replace(webroot + 'clients/status');
		}
	});
}

function moveRowId(element){
		var rowId = $(element).attr('id').match(/row-[a-fA-F0-9\-]*/);
		var rowType = $(element).attr('id').match(/-t-\d*/);
        $(element).parent('tr').attr('id' ,rowId);
		$(element).parent('tr').attr('class', rowType);
        $(element).attr('id' ,'');
    }

/**
 * Sweep the page for bindings indicated by HTML attribute hooks
 * 
 * Class any DOM element with event handlers.
 * Place a 'bind' attribute in the element in need of binding.
 * bind="focus.revealPic blur.hidePic" would bind two methods
 * to the object; the method named revealPic would be the focus handler
 * and hidePic would be the blur handler. All bound handlers
 * receive the event object as an argument
 * 
 * Version 2
 * 
 * @param {string} target a selector to limit the scope of action
 * @returns The specified elements will be bound to handlers
 */
function bindHandlers(target) {
    if (typeof(target) == 'undefined') {
        var targets = $('*[bind*="."]');
    } else {
		var targets = $(target).find('*[bind*="."]')
	}
	targets.each(function(){
		var bindings = $(this).attr('bind').split(' ');
		for (i = 0; i < bindings.length; i++) {
			var handler = bindings[i].split('.');
			if (typeof(window[handler[1]]) === 'function') {
				// handler[0] is the event type
				// handler[1] is the handler name
				$(this).off(handler[0]).on(handler[0], window[handler[1]]);
			}
		}
	});
}

/**
 * new jquery function to center something in the scrolled window
 * 
 * Sets the css left and top of the chained element
 */
jQuery.fn.center = function() {
//    this.css("position", "fixed");
    this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
            $(window).scrollTop()) + "px");
    this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
            $(window).scrollLeft()) + "px");
    return this;
}

/**
 * Toggle an element
 * 
 * @param string element selector of the element to toggle
 * @returns {undefined}
 */
//function toggleThis(element) {
//    $(element).toggle(50, function() {
//        // animation complete.
//    });
//}

/**
 * Set up the click on a node to control the display-toggle of another node
 * 
 * Any <item class=toggle id=unique_name> will toggle <item class=unique_name> on click
 */
function initToggles() {
    $('.toggle').unbind('click').bind('click', function(e) {
		var id = e.currentTarget.id;
        $('.' + $(this).attr('id')).toggle(50, function() {
            // animation complete.
			if (typeof(statusMemory) == 'function') {
				statusMemory(id, e);
			}
        });
    })
}

function initToggleHits() {
    $('.hit').trigger('click');
}

// *********************************************************
// REFRESH SCREEN WITH AJAX RETURN VALS
// *********************************************************

/**
 * Update all 'available' information lines on the page
 * 
 * An 'avaialable' information line has up to 3 spans with mutable data
 * Each is classed with a name + itemId + catalogId hook (eg: availQty-I121-C91-)
 * The data array deals with a single Item value and contains one entry 
 * for each catalog product based on that Item
 * 
 * @param object data to update 'available' labels on the page
 */
function refreshAvailable(data, prefix) {
	// number indexes converted to words for us humans
	var hook = 0;
	var avail = 1;
	var sell = 2;
	var unit = 3;
	
	if (typeof(prefix)=='undefined'){
		var prefix = '.avail';
	}
	
	// update all occurances of each product
//	var len = Object.keys(data).length;
	for (var key in data) {
        var product = data[key];
		
		$(prefix+product[hook]).html(product[avail]);
		$('.unit'+product[hook]).html(product[unit]);
		$('.sell'+product[hook]).html(product[sell]);
		$('.input'+product[hook]).val(product[avail]);
		
		if(product.avail < 0){
			$(prefix+product[hook]).addClass('overCommitted');
		} else {
			$(prefix+product[hook]).removeClass('overCommitted');
		}
		
		var c = $(prefix+product[hook]).parent().css('backgroundColor');
		$(prefix+product[hook]).parent().animate({ backgroundColor: "#ff8" }, 100).animate({ backgroundColor: c }, 500);
    }
}

// *********************************************************
// ADDRESS FORM FIELD MANAGERS
// *********************************************************

/**
 * Get the proper state/provence input for the chosen country
 * 
 * @param boolean synch asynchronous/synchronous [optional]
 * @returns string changes DOM input element
 */
function countryCode(e, sync){
	if(typeof(sync)==='undefined') sync = true;
	//call the server to get state/provence codes for the new country
	var element = $(e.currentTarget);
	var model = $(element).attr('id').match(/[A-Z]{1}[a-z]+/);
	$('#' + model + 'State').css('display', 'none').parent('div').children().css('display', 'none');
	$('#' + model + 'State').parent('div').prepend('<div class="ajaxLoading" id="newForm">Loading the proper state/province list. Please wait ...</div>');
	$.ajax({
		type: "GET",
		url: webroot + controller + "getStateInput/" + $(element).val() + "/" + model,
		dataType: "html",
		async: sync,
		cache: false,
		success: function(data) {
			$('#' + model + 'State').parent('div').replaceWith(data);
		},
		error: function() {
			//put in a text input here
			alert('The countryCodeBlur js function failed.')
		}
	});


	//place the returned state/provence input in the form
}

/**
 * Insure the proper state input condition is in place given a country value and optional state value
 * 
 * When landing on an address form with data for the form,
 * the proper state input must be loaded for the country
 * and the state value then placed in that new input.
 * And even if only a country value is know, the proper input should be loaded
 * 
 * @param {string} country The starting value for the country input
 * @param {string} state The desired starting value for the state/provence input
 * @returns string changes DOM input element
 */
function synchCountryState(country, state){
	if(typeof(state)==='undefined') state = '';
	//set the country value and trigger its blur
	//how do you only do the state insertion AFTER the blur success? possibly call with synchronous?
}
// *********************************************************
// GLOBAL COOKIE STUFF
// *********************************************************

var some = 'x';

/**
 * Read the named cookie
 * 
 * @return string|null The cookie value or null
 */
function ReadCookie(name){
    name += '=';
    var parts = document.cookie.split(/;\s*/);
    for (var i = 0; i < parts.length; i++) {
        var part = parts[i];
        if (part.indexOf(name) == 0) {
            return part.substring(name.length)
	}
    }
    return null;
}

/**
 * Add a value to a comma delimeted or single value cookie
 * 
 * Assumes the cookie has a comma delimited list as a value
 * But will also work for a single value cookie
 * 
 * @param string name The name of the cookie to modify
 * @param string entry The value to add to the cookies comma delimited list
 * @param string path The cookie path value
 * @returns void
 */
function removeFromCookie(name, entry, path) {
	if (typeof(path) === 'undefined') { var path = '/'; }
	
    value = ReadCookie(name);
    
    if (value != null) {
	// remove the value and preceeding comma
	// /[,]?your-cookie-value/
	regex = new RegExp('[,]?' + entry);
	value = value.replace(regex, '');
	
	// remove any leading , that may have been left in the cookie value
	// and write the cookie and path
	ready = name + '=' + value.replace(/^[,]?/, '') + ";path=" + path;
	document.cookie = ready;
    }
}

/**
 * Remove a value from a comma delimeted or single value cookie
 * 
 * Assumes the cookie has a comma delimited list as a value
 * But will also work for a single value cookie
 * 
 * @param string name The name of the cookie to modify
 * @param string entry The value to remove from the cookies comma delimited list
 * @param string path The cookie path value
 * @returns void
 */
function addToCookie(name, entry, path) {
	if (typeof(path) === 'undefined') { var path = '/'; }

	value = ReadCookie(name);
    
    // add the value [and preceeding comma]
    // /[,]?your-cookie-value/
    regex = new RegExp('[,]?'+entry);
    if (value == null) {
	value = entry;
    } else {
	value = value.replace(regex, '') + ',' + entry;
    }
    // remove any leading , that may have been introduced to the cookie value
    // and write the cookie and path
    ready = name + '=' + value.replace(/^[,]?/,'') + ";path=" + path;
    document.cookie = ready;
    return true;
}

function forgotPassword(e) {
	if ($(e.currentTarget).prop('checked')) {
        $('legend').html("Entered your registered email address and we'll email you a new password")
		$('div.password').addClass('hide').removeClass('required');
		$('label[for="UserUsername"]').html('Email address');
		$('form#UserLoginForm').attr('action', webroot + 'users/forgotPassword');
		$('input[type="submit"]').val('Submit');
		$('input#UserPassword').remove();
	} else {
		location.reload();
	}
}

/**
 * On selection of an invoice id from an input, display the invoice pdf in a new window
 */
function invoicePdf(e) {
	var id = $(e.currentTarget).val();
	if (id != '') {
		window.open(webroot + 'invoices/viewOldInvoice/' + id + '.pdf');
	}
}

function moveScroller() {
//	$.snapTo = new BinaryHeap(function(x){return -x;}); // sorted descending
//	$.snapTo.push(0); // top of screen starts at zero
	$(window).scroll(function(){
		$('*[bind*="snap"]').each(function(){
			$(this).trigger('snap');
		})
	})
}

function scrollSnap(e) {
	
	var s = $(e.currentTarget);
	var st = $(window).scrollTop();
	var ot = $(s).parent().offset().top + parseInt(s.attr('offset'));
	var bot = parseInt(s.css('top')) + parseInt(s.css('height'));
	
	if(st > ot) {
		s.removeClass('unsnap');
		s.addClass('snap');
		
	} else if (st <= ot){
		s.removeClass('snap');
		s.addClass('unsnap');
	}
}

function createFlashMessage(message, cls){
	var flashMessage = $('<div class="alert ' + cls + ' flash-msg"> <button data-dismiss="alert" class="close toggle" type="button" id="flash-msg">×</button>' + message + '</div>');
	flashMessage.children('button').on('click', function(){
		$(this).parent('div').remove();
	})
	return flashMessage;
}

function removeFlashMessages(parent){
	if (parent == 'undefined'){
		parent = 'window';
	}
	$(parent).find('div.alert').remove();
}

 var entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': '&quot;',
    "'": '&#39;',
    "/": '&#x2F;'
  };

  function escapeHtml(string) {
    return String(string).replace(/[&<>"'\/]/g, function (s) {
      return entityMap[s];
    });
  }


$(document).ready(function() {

//    $('.flash-msg, .message').delay(5000).fadeOut('slow');


    function initDevLogin() {
        $('#header #UserLoginForm > .submit').css('display', 'none');
        $('#devUserUsername').bind('change', function() {
            $('#UserLoginForm input[type="submit"]').trigger('click');
        })
    }

    // Sets the position of revealed drop-down menus
    // for the main site navigation strip
    function initMainMenu() {
        $('#MainMenu > li').hover(function(e) {
            var x = 5;
            $('#MainMenu > li > ul').css('left', 0);
        })
    }
    
    if ($('#cartCount').html() != 0) {
	$('#cartbutton').css('display', 'inline-block');
    }
    
    $('#homePref').on('click', function(e){
        e.preventDefault();
	$('#homePref > span').load(webroot + 'preferences/homePreference/' + $(this).attr('controller') + '/' + $(this).attr('action'));
    })

    $('a[href*="item_peek"]').on('click', function(e){
		e.preventDefault();
		$(this).next().load($(this).attr('href'), function(){
			// div > div distinguishes these from backorder tools
			// this is makes for high COUPLING between various
			// dom element behaviors. The toolPallet class is used
			// initially as a style hook. As the page evolved it became more
			// widely used, bringing nice, consistent styling. But it was
			// also used as a behavior hook. That brought the inappropriate COUPLING of behaviors
			$('div > div.toolPallet').css('display', 'block'); 
			$('div > div.toolPallet > p.close').on('click', function(){
				$(this).parent().remove();
			})
		});
    })
    
    initDevLogin();
    initMainMenu();
    initToggles();
    initToggleHits();
	bindHandlers();
	escapeIsCancel();
	moveScroller();
})