$(document).ready(function(){
//	initHelp();
});

/**
 * Binding to edit a help entry
 * 
 * @param {type} e
 * @returns {undefined}
 */
function editHelp(e) {
	e.preventDefault();
	var tag = $(e.currentTarget).attr('href').match(/[a-zA-Z0-9]+$/);
	$.ajax({
        type: "POST",
        url: webroot + "helps/editHelp/" + tag,
        dataType: "html",
        success: function(data) {
			$('div#helpText').remove();
			$('div#content').append(data);
			$('div#helpText').center();
			bindHandlers();
		},
		error: function() {
			alert('Ajax failed to display the help. Please try again.')
		}
	});
	
}

function initHelp(){
	// get all the help doc names for this page
	var helpDocs = new Object();
	$('.help').each(function(){
		helpDocs[$(this).attr('help')] = $(this).attr('help');
	});
	
	$.ajax({
        type: "POST",
        url: webroot + "helps/getHelpLinks",
        data: helpDocs,
        dataType: "html",
        success: function(data) {
			if(data == ''){
				//if there is no help available on this page, hide the help menu
				$('div#help').addClass('hide');
			} else {
				//if here is help available on this page, display the help menu
				$('div#help').removeClass('hide');
				$('div#help > div.tools').html(data);
				bindHandlers();
			}
		},
		error: function() {
			if (data != '') {
				alert('Ajax failed to return Help Links. Please reload the page.')
			}
		}
	});
}

function displayHelp(e) {
	e.preventDefault();
	var tag = $(e.currentTarget).attr('href').match(/[a-zA-Z0-9]+$/);
	$.ajax({
        type: "POST",
        url: webroot + "helps/displayHelp/" + tag,
        dataType: "html",
        success: function(data) {
			$('div#helpText').remove();
			$('div#content').append(data);
			$('div#helpText').center();
			bindHandlers();
		},
		error: function() {
			alert('Ajax failed to display the help. Please try again.')
		}
	});
}

function closeHelp(e){
	$('div#helpText').remove();
}

function submitHelp(e){
	e.preventDefault();
	$.ajax({
        type: "POST",
        url: webroot + "helps/editHelp/",
        dataType: "html",
		data: $('#HelpEditHelpForm').serialize(),
        success: function(data) {
			$('div#helpText').remove();
			$('div#content').append(data);
			$('div#helpText').center();
			bindHandlers();
		},
		error: function() {
			alert('Ajax failed to display the help. Please try again.')
		}
	});
}