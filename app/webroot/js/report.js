$(document).ready(function(){
	
    $('table td:first-of-type').each(function(){
		var rowClass = $(this).attr('class');
		$(this).parent('tr').attr('class' ,rowClass);
		$(this).attr('class' ,'');
    });
	
});

function differentCustomer(e) {
	var page = location.href.replace(/\d+$/, '')
	var id = $(e.currentTarget).val().match(/^\d+/);
	location.assign(page + id);
}

function reportValidation(e) {
	
	if ( chosen('UserReport') && chosen('UserCustomers') && chosen('UserStartMonthMonth') && chosen('UserStartYearYear') && chosen('UserEndMonthMonth') && 'UserEndYearYear') {
		return true;
	} else {
		alert('All fields are required');
		return false
	}
}

function chosen(input) {
	return ($('#'+input).val() != '');
}