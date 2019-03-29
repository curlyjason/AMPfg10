
/**
 * Activate or deactivate items based upon catalog in/reactivation
 * 
 * Button click will change the db record and return the replacement HTML <tr>
 * 
 * @param {event} e
 * @param {string} href catalogs/setActive/{catalog id}/{activate state to set}
 * @returns {html} '/Elements/Catalog/inactive_row'
 */
function setActive(e) {
	e.preventDefault();
	var tr = $(e.currentTarget).parents('tr')
	$.ajax({
		type: "GET",
		dataType: "HTML",
		data: '',
		url: $(e.currentTarget).attr('href'),
		success: function(data) {
			tr.replaceWith(data);
			bindHandlers();
		},
		error: function(data) {
			alert('Product change failed, please try again.')
			bindHandlers();
		}
	});
}

/**
 * page filter input bindings
 * 
 * I thought these three change events would do unique things 
 * so I left the original 3 call points just in case there 
 * is some differences that come up later
 * 
 * @returns {undefined}
 */
function customerFilter () {
	refreshPage();
}
function stateFilter() {
	refreshPage();
}
function limit() {
	refreshPage();
}

/**
 * Assmeble new url from old url + filter-input settings and load that page
 * 
 * @returns {document}
 */
function refreshPage () {
	var l = document.location.href;

	// page param may or may not be in url
	p = l.match(/page:\d+/i);
	var page = (p == null) ? '' : p ;
	// these three inputs may have user choices.
	// if not, they match the url
	var cust = ($('#customers').val() == '' ) ? '' : $('#customers').val() + '/';
	var state = $('input[id*="Active"]:checked').val() + '/';
	var limit = 'limit:' + $('#paginationLimit').val() + '/';

	var target = webroot + controller + action + cust + state + limit + page;
	location.assign(target);
}

// ================ READY FUNCTION =======================

$(document).ready(function() {
	bindHandlers();
});