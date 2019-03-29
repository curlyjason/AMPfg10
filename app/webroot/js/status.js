/**
 * Record which elements are toggled to visibility on the status page
 * 
 * Status page has many toggle elemnets. When doing status changes
 * the page is refreshed and it is necessary to restore the 
 * element visibility pattern.
 * As toggle-clicks occur, record what elements must be triggered
 * to restore the visibility pattern
 * 
 * @param string id An element id attribute to add or remove
 * @returns void
 */
function statusMemory(id, e) {
    // cookie path
    var path = webroot + controller + 'status/';
    e.stopPropagation();
    
    // when all of a set of elements is hidden,
    // remove the controll id from the cookie
    // (a controll may effect many elements,
    // only do this when the last one goes dark)
    if ($('.' + id + ':visible').length == 0) {
	removeFromCookie('visibleStatus', '#'+id, path);

    // when all of a set of elements is showing,
    // add the controll id to the cookie
    // (a controll may effect many elements,
    // only do this when the final one appears)
    } else if ($('.' + id + ':visible').length == $('.' + id).length) {
	addToCookie('visibleStatus', '#'+id, path)
    }
}

$(document).ready(function(){
    
    /**
     * Restore the status page visibility pattern
     * 
     * When the status page is refreshed the visiblity pattern
     * should be restored and any status-changed element from
     * a new edit should be revealed too. 
     * 
     * @returns void
     */
    function initStatusVisiblity(){
	var here = location.href.split('/');
    var id = false;
	
	// a status changed element will have its order id
	// appended to the url. If present, add it to the 
	// cookie so it will be revealed with other visible elements
	if (here[here.length-1].length == 36) {
        id = here[here.length-1];
	    var fresh = $('#'+here[here.length-1]).parents('table').siblings('h3').attr('id');
	    addToCookie('visibleStatus', '#'+fresh);
	}
	var value = ReadCookie('visibleStatus');
	
	// nothing was previously visible
	if (value == null) {
	    return;
	}
	
	// The cookie value records all the control element IDs
	// that need to be clicked to establish the visiblity pattern
	var elements = value.split(',');
	for (var i = 0; i < elements.length; i++) {
	    $(elements[i]).trigger('click');
	}
    if (id) {
	$('#' + id).parent('td').parent('tr').animate({backgroundColor: "#ff8"}, 100).animate({backgroundColor: "#fff"}, 10000);
//        $('#' + id).parent('td').parent('tr').addClass('highlight');
    }
    }

    /**
     * Run page intializers
     */
    initStatusVisiblity();
	$('#collapseAll').on('click',function(e){
		e.preventDefault();
		toggleAll();
	});
})

function toggleAll(){
	$('.toggle').each(function(){
		if($('.'+$(this).attr('id')+':visible').length >0){
			$(this).trigger('click');
		}
	})
}

function backorderLink(e){
	e.preventDefault();
	
	var x = e.clientX;
	var y = e.clientY;
	var id = e.currentTarget.id;
	
	$('div[class*="backOrder"]').css('display', 'none');
	$('div.'+id).css('left', x - 20).css('margin-top', '-40px');
	
	$('.' + $(this).attr('id')).toggle(50, function() {
		// animation complete.
	});
}

function noteLink(e){
	e.preventDefault();
	
	var x = e.clientX;
	var y = e.clientY;
	var id = e.currentTarget.id;
	
	$('div[class*="statusNote"]').css('display', 'none');
	$('div.'+id).css('left', x - 20).css('margin-top', '-40px');
	
	$('.' + $(this).attr('id')).toggle(50, function() {
		// animation complete.
	});
}