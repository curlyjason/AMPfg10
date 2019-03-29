var Review = {
	itemIndex: 0,
	labelIndex: 0,
	perPage: 10,
	page: 1,
	/*
	 * Setting this value will allow us to keep the user within 
	 * valid page/count range. Using the perPage value, we can 
	 * check and force sample page number to a value.
	 * ItemRegistry can write the value to DOM for access or 
	 * we can get it while getting other json sample data.
	 * 
	 * Possibly on page ready we should get the map and the count 
	 * and take that process out of the sampling cycle altogether.
	 * 
	 * And maybe we should always bring in sample data
	 */
	itemCount: null,
	data: [],
	map: {}
}

function getSamples(){
	var header = $('input#header_row').is(":checked");
	var length = $('input#number_of_sample').val();
	var page = $('input#sample_page').val();
	if (Review.data.length == 0) {
		$.ajax({
			dataType: 'json',
			url: webroot + "itemImports/getMapTemplate",
			async: false,
			success: function (data) {
				Review.map = data;
			}
		})
	}
	$.ajax({
		dataType: 'json',
		url: webroot + "itemImports/getSampleData/" + header 
				+ "/" + length + "/" + page,
		async: false,
		success: function(data){
			Review.data = [];
			for(i=0;i < data.length;i++){
				Review.data.push($.parseJSON(data[i]));
			}
		}
	})
	renderBlock(Review.itemIndex);
}

function renderBlock(itemIndex) {
	Review.rendered = true;
	var destination = $('div.sample');
	destination.html('');
	destination.append('<h1>Previewing #' + (itemIndex + 1) + ' of ' 
			+ Review.perPage + '. Page ' + Review.page + '</h1>');
	for (var i = 0; i < Review.map.indexes.length; i++) {
		$('div.sample').append(
			renderMapLine(i)
		);
	}
}

function renderMapLine(mapIndex) {
	var item = Review.data[Review.itemIndex].item;

	var content = '<p node="' + mapIndex + '">' + 
			'<span class="label">' + 
			'<em>' + Review.map.labels[mapIndex] + '</em>: ' + 
			'</span>' + item[Review.map.indexes[mapIndex]] + '</p>';
	return content;
}

function nextBlock(){
	checkRendered();
	if (Review.itemIndex < Review.data.length - 1) {
		Review.itemIndex++;
		renderBlock(Review.itemIndex);
	}
}

function previousBlock(){
	checkRendered();
	if (Review.itemIndex > 0) {
		Review.itemIndex--;
		renderBlock(Review.itemIndex);
	}
}

function updateMap(e) {
	checkRendered();
	var mapIndex = $(e.currentTarget).attr('node');
	var itemColumn = $(e.currentTarget).val();
	Review.map.indexes[mapIndex] = itemColumn;
	$('p[node="' + mapIndex + '"]').replaceWith(renderMapLine(mapIndex));
}

function checkRendered(){
	if(!Review.rendered){
		getSamples();
	}
}

function updatePageSettings() {
	Review.perPage = $('input#number_of_sample').val();
	Review.page = $('input#sample_page').val();
	if (Review.itemIndex + 1 > Review.perPage) {
		Review.itemIndex = Review.perPage - 1
	}
	if (Review.itemIndex + 1 > Review.data.length) {
		Review.itemIndex = Review.data.length - 1;
	}
	getSamples();
}

$(document).ready(function(){
	getSamples();
});
