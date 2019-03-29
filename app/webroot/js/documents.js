/**
 * When the upload-file field changes, set the title field to the name of the file
 * 
 * @param {event} e
 * @returns {void}
 */
//function captureName(e) {
//	var alias = '#' + $(e.currentTarget).attr('id').replace('ImgFile', '');
//	$(alias + "Title").val($(alias + "ImgFile").val());
//}

/**
 * Add a new Document Input row to the table of doc inputs
 * 
 * @returns {html}
 */
function newDocInput() {
	var i = $('td.document-row').length;
		$.ajax({
			type: "GET",
			dataType: "html",
			url: webroot + 'documents/new_doc',
//			data: {index: i}, switched to time() for indexing the rows.
			success: function(data) {
				$('#doc-tools').parent('tr').before(data);
				bindHandlers();
				initToggles();
				var d = data.match(/tr id="row(\d*)/);
				var num = d[1];
				$('#Document'+num+'OrderId').val($('#OrderId').val());
			}
		});
} 

/**
 * When the upload-file field changes, set the title field to the name of the file
 * 
 * @param {event} e
 * @returns {void}
 */function captureName(e) {
	var img_file = '#' + $(e.currentTarget).attr('id')
	var title = img_file.replace('ImgFile', 'Title');
	$(title).val($(img_file).val());
	indicateChange();
}

function deleteRow(e) {
	var rowNumber = $(e.currentTarget).parent('span').attr('id').match(/-(t\d*)/)[1];
	var rowId = '#row' + rowNumber;
	var recordId = $('#Document' + rowNumber.toUpperCase() + 'Id').val();
	if (recordId != '') {
		$.ajax({
			type: "DELETE",
			dataType: "JSON",
			url: webroot + 'documents/delete/' + recordId,
			success: function(data) {
				if(data.result){
					$(rowId).remove();
					unindicateChange();
				} else {
					m = createFlashMessage('The document did not delete, please try again.', 'error');
					$(rowId).find('span').after(m);
				}
			},
			error: function(data) {
				m = createFlashMessage('The document did not delete, please try again.', 'error');
				$(this).prev().after(m);
			}
		})
	} else {
		$(rowId).remove();
		unindicateChange();
	}
}

/**
 * Bring in existing documents for pallet
 * 
 * @param event e
 * @returns html
 */
function fetchDocuments(e) {
	
	$('#docTools').remove();
	e.preventDefault();
	
	var x = e.clientX;
	var y = e.clientY;
	var labelToolPallet = 'Add documents to the order ';
	
	$(e.currentTarget).parent('td').append('<div class="toolPallet" id="docTools"><p id="DocPalletLabel">'+labelToolPallet+'</p><p id="dummy">Loading documents...</p></div>')
	$('#docTools').css('left', x).css('position', 'absolute').css('display', 'block');
	
	$.ajax({
		type: "GET",
		dataType: 'HTML',
		cache: false,
		url: $(e.currentTarget).attr('href'),
		success: function(data) {
			$(data).replaceAll('p#dummy');
			$('p#DocPalletLabel').html(labelToolPallet + $('#OrderOrderNumber').val());
			bindHandlers();
			initToggles();
		},
		error: function(data) {
			alert('The server could not return the documents table. Please try again.');
		}
	});
}

/**
 * Indicate that data changed on the form
 */
function indicateChange() {
	$('table#Documents').attr('change', true);
	$('.docDoneButton').html('Save').addClass('green');
}

/**
 * Remove the change indicator from the form
 */
function unindicateChange() {
	if ($('tr[id*="rowt"]').length == 0) {
		$('table#Documents').attr('change', false);
		$('.docDoneButton').html('Done').removeClass('green');
	}
}
/**
 * Comment
 */
function docDone(e) {
	if($('table#Documents').attr('change') != 'false' && $('tr[id*="rowt"]').length > 0){
		return true;
	} else {
		$('div#docTools').remove();
		return false;
	}
}
