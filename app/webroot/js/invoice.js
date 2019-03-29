$(document).ready(function() {
	setupSubmit();
	setupInvoicePage();
	initInvoiceRow();
	if (typeof(establishBaseline) == 'function') {
		establishBaseline();
	}
	initRemove();
	classRows();
	initCheckboxes();
	
	// Modify the page if we are in view (review) mode
	if (window.location.href.match(/\/view/)) {
		
		// take the 'row delete' tool off the page. Can't delete during review.
		$('td.invoiceRemove').html('');
		
		// modify the subtotal lable for the group to show this is an invoice line item
		var sub = $('td[id*="groupHeaderRow-"]');
		var str = sub.html();
		sub.html(str.replace('Subtotal', 'Invoice Line Item:'));
		
		// make the charges that go into a single Invoice line item less significant on the page
		$('tr[id*="invoiceRow"] > td').css('font-size', '80%').css('opacity', '.7').css('padding', '3px');
		
		// change Review button to show the next logical step, Submit
		$('.invoiceSubmitButton').html('Submit Invoice');
	}
});

/**
 * wrap the form in a div... not sure why
 */ 
function setupInvoicePage(){
	$('#InvoiceItemFetchInvoiceLisForm').wrap('<div id="chargeTools" />');
}

/**
 * prepare row hooks and element bindings for a new page
 */
function initInvoiceRow(){
    $('#invoice td:first-of-type').each(function(){
		moveInvoiceRowId($(this));
    });
	invoiceInputBindHandler();

}

/**
 * Move first cell ID attr to row ID attr
 */
function moveInvoiceRowId(element){
	var rowId = $(element).attr('id');
	$(element).parent('tr').attr('id' ,rowId);
	$(element).attr('id' ,'');
}



/**
 * Set the bindings for charge item inputs
 * 
 * When an input gets focus it gains attr touch=true
 * This will allow a later comparison of touched inputs to the values last know to be recorded
 * If there is no match, a save failed for the input and we can try to save again
 * Also, the input change event will trigger an ajax save of the field and
 * on successful return, we will store the last saved value (for possible future comparison as above)
 * and clear the attr touch to false
 */
function invoiceInputBindHandler() {
	$('#invoice input').each(function(){$(this).off('focus').off('change');});
	$('#invoice input').each(function(){$(this).on('focus', tagFieldAsTouched);});
//	$('#invoice input').each(function(){$(this).on('blur', processFieldBlur);});
	$('#invoice input').each(function(){$(this).on('change', saveChange);});
}

/**
 * A charge item's input focus event handler
 * 
 * To tell us which inputs have been entered so we can check to make sure they were saved
 * before leaving the page
 */
function tagFieldAsTouched() {
    $(this).attr('origValue', $(this).val());
	$(this).attr('touch', true);
}

/**
 * Currently unused.
 */
function processFieldBlur() {
	for (var x in $(this).change()){
		alert($(this).change()[x]);
	}
//	alert($(this).change());
//	var oldVal = $(this).attr('touch');
//	var newVal = $(this).val();
//	if (oldVal == newVal) {
//		$(this).attr('touch', false);
//	}
}

/**
 * Order/OrderItem CUD process Submit button handler
 * 
 * Prevent page reload. Save the new data. Update the page sub/total bits.
 * @param {event} e event object
 */
function saveChange(e) {

//	$('#shippingDefault').on('click', function() {
//		$(this).preventDefault();
		
	var row = $(e.currentTarget).parents('tr').attr('id');
	var self = $(e.currentTarget).attr('id');
	
	// Prepare the data to POST to the server
	// the input that changed
	// the IDs for the record (they're all packed into the first cell of the row)
	// the id attribute of the input that changed (so the server can figure out which field to save)
	var postData = $('#' + self + ', #' + row + ' > td.cellOne > input').serialize() + '&field='+$(e.currentTarget).attr('id');

	$.ajax({
		type: "POST",
		dataType: "json",
		data: postData,
//		data: '_method=POST&'+postData,
		url: webroot + 'invoice_items/saveInvoiceItemField',
		success: function(data) {
            if(!data.result){
                alert('The price change failed to save, please try again.');
                $('#' + self).val($('#' + self).attr('origValue'));
                $('#' + self).select();
            } else {
                $('#' + data.selector).attr('touch', false);
                $('#' + data.selector).attr('origValue', null);
                updateInvoiceTotals(data);
                clearEditIndicators(data);
            }
		},
		error: function(data){
			// the save failed.
			// Don't update $.baseline
			// The mis-match there will let us try and save again later
			alert('save failed due to ajax error');
		}
	});

//	})

}

/**
 * Update sub/totals in DOM after return from Order/OrderItem CUD ajax call
 */
function updateInvoiceTotals(data) {
	
	if (data.selector.match(/Quantity|Price/)) {
		if (typeof(data.invoiceItemId) != 'undefined') {
			var c = $('span#chargeSubtotal-' + data.invoiceItemId).css('backgroundColor');
			$('span#chargeSubtotal-' + data.invoiceItemId).html(data.subtotal).animate({backgroundColor: "#ff8"}, 100).animate({backgroundColor: c}, 500);
		}		
		c = $('#groupHeaderTotal-' + data.headerId).css('backgroundColor');
		$('#groupHeaderTotal-' + data.headerId).html(data.headerTotal).animate({backgroundColor: "#ff8"}, 100).animate({backgroundColor: c}, 500);
		
		c = $('#invoiceTotal').html(data.invoiceTotal).css('backgroundColor');
		$('#invoiceTotal').html(data.invoiceTotal).animate({backgroundColor: "#ff8"}, 100).animate({backgroundColor: c}, 500);
	}}

/**
 * Clear all indication that a field was changed
 * 
 * When a field gets focus, it gains attr('touch', true)
 * Change will trigger a save.
 * Successful save comes here to record the new value in $.baseline
 * and set touch = false, thus removing the item from the 'unsaved-data' list
 * 
 * @param json data return from server saveInvoiceItemField
 */
function clearEditIndicators(data) {
	$.baseline[data.selector] = data.value;
	$('#'+data.selector).attr('touch', false);
}

/**
 * Bring in existing Order/OrderItem charges for CUD processes
 * 
 * @param event e
 * @returns html
 */
function fetchChargeItems(e) {
	var charge = e.currentTarget;
	e.preventDefault();
	
	var x = e.clientX;
	var y = e.clientY;
	var labelToolPallet = 'Add charges to the invoice for ' + $(e.currentTarget).attr('customer');
//	var id = e.currentTarget.id;
	
	saveInvoiceCharges();
	$(e.currentTarget).parent('td').append('<div class="toolPallet" id="chargeTools"><p>'+labelToolPallet+'</p><p id="dummy">Loading charges...</p></div>')
	$('#chargeTools').css('left', x).css('position', 'absolute').css('display', 'block');
	
	$.ajax({
		type: "GET",
		dataType: 'HTML',
		url: $(e.currentTarget).attr('href'),
		success: function(data) {
			$(data).replaceAll('p#dummy');
			bindHandlers();
			establishBaseline();
			initInvoiceRow();
			initRemove();
		},
		error: function(data) {
			alert('The server could not return the charges table. Please try again.');
		}
	});
}

/**
 * Create and send back one new charge record
 * 
 * Also sends basline data so the page can insure saves of changed data
 * 
 * @param event e
 */
function addNewCharge(e){
	e.preventDefault();
	var row = $(e.currentTarget).parents('tr').attr('id');
	var postIndex = $('span.rowNumberIndex').length; // the next new row number
	var postData = $('#' + row + ' > td.cellOne > input').serialize() + '&index='+postIndex;
//	alert('adding New Charge');
	$.ajax({
		type: "POST",
		dataType: "html",
		data: postData,
		url: webroot + 'invoice_items/newInvoiceCharge',
		success: function(data) {
			// data includes the new row and the baseline values for change verification
			var newRow = $(data).find('tr');
			var values = jQuery.parseJSON($(data).filter('p').html());
			
			// insert the new input row
			$('#' + row).before(newRow);
			
			moveInvoiceRowId($(newRow).children('td:first-of-type'));
			updateBaseline(values);
			invoiceInputBindHandler();
			initRemove();
			
		},
		error: function(data){
			// the save failed.
			// Don't update $.baseline
			// The mis-match there will let us try and save again later
			alert('failed, as expected');
		}
	});

}

/**
 * Copy baseline values for new record into the main object
 * 
 * @param object newEntries
 */
function updateBaseline(newEntries) {
	for (var property in newEntries) {
		$.baseline[property] = newEntries[property];
	}
}

/**
 * Verify all data was changed and close the Charges pallet
 */
function saveInvoiceCharges() {
	// get out if the charge tools are not on the page
	if($('#chargeTools').length == 0){
		return;
	}
	// detect and remove any empty InvoiceItems
	
	// before removing the existing form, see if there are any save to be done
	$.resave = {};
	$.resaveData = [];
	$.postData = false;
	$('input[touch=true]').each(function(){
		var id = $(this).attr('id');
		if ($(this).val() != $.baseline[id]) {
			var index = id.match(/\d/);
			if(typeof($.resave[index])=='undefined'){
				$.resave[index] = {};
			}
			$.resave[index][id] = $(this).serialize();
			$.resaveData.push($(this).serialize());
			
		}
	});
	for(var target in $.resave){
		$.resaveData.push($('td[row='+target+'] input').serialize());
	}
	$.postData = $.resaveData.join("&");
	delete $.resaveData;
	delete $.resave;
	
	if ($.postData.length == 0) {
		delete $.postData;
		$('#chargeTools').remove();
	} else {
		$.ajax({
			type: "POST",
			dataType: "json",
			data: $.postData,
			async: false,
			url: webroot + 'invoice_items/resave',
			success: function(data) {
				delete $.postData;
				if (data.return) {
					$('#chargeTools').remove();
					delete $.baseline;
				} else {
					alert('Save failed, please try again');
				}
			},
			error: function(data) {
				alert('Save javascript failed, please try again');
			}
		});
	}	
}

/**
 * Add 'remove' cans to the ends of rows and bind their actions
 */
function initRemove(){
	$("#chargeTools .remove").each(function() {
		$(this).replaceWith('<a class="remove" id="' + $(this).attr('id') + '" href="' + webroot + 'invoice_items/jsDelete/' + $(this).attr('id').replace('delete_', '') + '" title="Remove item"><img src="' + webroot + 'img/icon-remove.gif" alt="Remove" /></a>');
	});
	$('#chargeTools a.remove').on('click', function(e){
		e.preventDefault();
		$.ajax({
			type: "POST",
			dataType: "json",
			url: $(e.currentTarget).attr('href'),
			success: function(data) {
				if (data.return) {
					var rowId = '#invoiceRow-' + data.return;
					$(rowId).find('input').remove();
					$(rowId).css('display', 'none');
					data.selector = 'Price';
					updateInvoiceTotals(data)
				} else {
					alert('Delete failed, please try again');
				}
			},
			error: function(data) {
				alert('Delete javascript failed, please try again');
			}
		});
	})
}

function invoiceSubmit(e){
	if (!window.location.href.match(/\/view/)) {
		window.location.assign(window.location.href + '/view');
	} else {
		var id = window.location.href.match(/\/[\d]+/);
		window.location.assign(webroot + 'invoices/submitInvoice' + id);
	}
}

function setupSubmit(){
	if(window.location.href.match(/submitInvoice/)){
		var id = $('#InvoiceId').val();
		window.location.assign(webroot + 'clients/status');
		window.open(webroot + 'invoices/viewOldInvoice/' + id + '.pdf');
	}
}

function invoiceBackButton(e){
	var cust = $(e.currentTarget).attr('cust');
	window.location.replace(webroot+'invoiceItems/fetchInvoiceLis/'+cust+'/Customer');
}

/**
 * click event handler for exclusion checkboxes
 * 
 * Make display reflect the choice
 * Clear leftover flash messages
 * Save the new choice to the db
 * 
 * @param object e the event
 */
function exclusionChoice(e) {
	var target = $(e.currentTarget);
	toggleOrder(target)
	$('div.alert').remove();
	
	var orderId = $(target).parents('div').next().val();
	var exclusion = ($(target).prop('checked')) ? '1' : '0';
	$.ajax({
		type: "GET",
		dataType: "HTML",
		url: webroot+'orders/updateExclusion/'+orderId+'/'+exclusion,
		success: function (data) {
			var cells = $(target).parents('tr').find('td');
			$(cells[2]).append(data);
			$(cells[2]).find('#flash-msg').on('click', function(){
				$(this).parent('div.alert').remove();
			})
		},
		error: function (data) {
			alert('There was a failure when attempting to save the inclusion/exclusion setting.');
		}
	})

}

/**
 * Given an exclusion checkbox, make the screen display reflect the choice
 * 
 * @param obj target An inclusion/exclusion checkbox
 */
function toggleOrder(target){
	var oc_class = '.oc_' + $(target).parents('div').next().val();
	var first_row = $(target).parents('tr');
	if ($(target).prop('checked')) {
		$(oc_class).each(function() {
			$(first_row).css('color', '#aaa');
			$(this).addClass('hide');
		})
	} else {
		$(oc_class).each(function() {
			$(first_row).css('color', 'black');
			$(this).removeClass('hide');
		})
	}
}

/**
 * Walk the table rows, classing them to aid in later inclusion/exclusion css tweaks
 */
function classRows() {
	var rows = $('table#invoice > tbody > tr');
	var c = 4; // skip rows for general charges. Only need ORDER related stuff
	var orderClass = '';
	var id = '';
	while (c < rows.length) {
		if ($(rows[c]).attr('id') == 'section') {
			id = $(rows[c]).find('input[id*="OrderId"]').val();
			orderClass = 'oc_' + id;
			// in the first row, only set the items to hide
			// we don't want the whole thing disappearing!
			$(rows[c]).find('table').addClass(orderClass);
		} else {
			$(rows[c]).addClass(orderClass);
		}
		c++;
	}
}

/**
 * Make sure inclusion/exclusion checkboxes have proper bindings
 * 
 * These were late page additions and have inappropriate focus/change bindings. 
 * Remove these and leave only the proper 'click' binding.
 * 
 * Also, as we walk the checkboxes, bring the page display into alignment with the choices
 */
function initCheckboxes() {
	$('input[id*="OrderExclude"]').each(function(){
		$(this).off('focus').off('change');
		toggleOrder(this);
	})
}