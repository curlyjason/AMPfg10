/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function editLocations(e){
	cancelLocations(e);
	var itemId = $(e.currentTarget).parents('div').attr('itemId');
	var divId = $(e.currentTarget).parents('div').attr('id');
	$.ajax({
		type: "POST",
		url: webroot + "locations/pullLocations/" + itemId,
		dataType: "html",
		async: false,
		success: function(data) {
			$('div#' + divId).children().addClass('hide');
			$('div#' + divId).append(data);
			initLocationDeletion();
			bindHandlers();
		},
		error: function(data) {
			alert('There was a problem with that call to the server. Your change was not made.')
		}
	});

}

function addNewLocation(e){
	var itemId = $(e.currentTarget).parents('div.locations').attr('itemId');

	$.ajax({
		type: "GET",
		dataType: "html",
		url: webroot + 'locations/fetchLocationRow/' + itemId,
		success: function(data) {
			$('#locationTable > tbody').append(data);
			initLocationDeletion();
			bindHandlers();
		}
    });    
}

function submitLocations(e){
	removeFlashMessages($(e.currentTarget).parents('td')[0]);
	var locData = $("#LocationPullLocationsForm").serialize();
	var itemId = $(e.currentTarget).parents('div.locations').attr('itemId');
	var ordId = $(e.currentTarget).parents('div.locations').attr('id').match(/ord-(.*)--/);
	$.ajax({
		type: "POST",
		dataType: "json",
		url: webroot + 'locations/saveLocations',
		data: locData,
		success: function(data) {
			$($(e.currentTarget).parents('td')[0]).prepend(data.message);
			initToggles();
			$(e.currentTarget).parents('div.locations').children('ul').removeClass('hide');
			$('div.locationForm').remove();
			refreshLocations(itemId, ordId[1]);
		},
		error: function(data) {
			alert('There was a save failure. Please try again.');
		}
	});
}

/**
* Init the garbage cans for existing data rows
*
 * @returns {undefined}	 
 * 
 **/
function initLocationDeletion() {
	$(".remove").each(function() {
		if ($(this).is('span')) {
			$(this).replaceWith('<a class="remove" id="' + $(this).attr('id') + 
					'" href="' + webroot + 'locations/delete/' + $(this).attr('location_id') + 
					'" location_id="' + $(this).attr('location_id') + 
					'" title="Remove item"><img src="' + webroot + 'img/icon-remove.gif" alt="Remove" /></a>');
		}		
	});

	$('a.remove').on('click', function(e){
		e.preventDefault();
		var itemId = $(e.currentTarget).parents('div.locations').attr('itemId');
		var locId = $(this).attr('location_id');
		if(locId == 'ZZZ'){
			$(e.currentTarget).parents('tr')[0].remove();
			return;
		}
		$.post($(this).attr('href'), function(data){
			if (data) {
				$(e.currentTarget).parents('tr')[0].remove();
			} else {
				alert('There was an error when deleting that record. Please try again.')
			}
		});
	});
}



function cancelLocations(e){
	//clear the form
	e.preventDefault();
	$(e.currentTarget).parents('div.locations').children().removeClass('hide')
	$('div.locationForm').remove();
	bindHandlers();
}

function refreshLocations(itemId, ordId){
	$.ajax({
		type: "GET",
		dataType: "html",
		url: webroot + 'locations/viewLocations/' + itemId + '/' + ordId,
		success: function(data) {
			$(data).replaceAll( "div[itemId='"+itemId+"']" );
			bindHandlers();
			$('div.locationForm').remove();
			$('#ajaxStart').addClass('hide');
		},
		error: function(data) {
			location.reload();
		}
	});
}

