function searchPreference(e) {
	e.preventDefault();

	$.ajax({
	type: "POST",
	dataType: "json",
	data: $('form#UserSearchForm').serialize(),
	url: webroot + 'search/searchFilterPreference',
	success: function(data) {
		$(e.currentTarget).append('<span id="filterSave"> Saved</span>');
		$('#filterSave').fadeOut(5000, function(){
			$('#filterSave').remove();
		});
	},
	error: function(data) {
		alert('Your preference change failed to save.')
	}
});

}