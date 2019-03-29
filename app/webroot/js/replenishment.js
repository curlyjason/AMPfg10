$(document).ready(function() {

	function initPage() {

		// stub in a vendor label for the po. Populate it if necessary
		//    $('div.view').prepend('<h3 class="vendor"></h3>');
		//    $('input[id*="VendorId"]:checked').each(function(){
		//	$('div.view > h3.vendor').html($('label[for="'+$(this).attr('id')+'"]').html().replace('PO for ', ''));
		//    });

		// analys starting checkboxes for table construction
		synchRows('div.sidebar');

	}

	/**
	 * Make select/deselect all-in-group tools
	 */
	function initItemCheckboxes() {
		$('input[id*="ReplenishmentItemGroup"]').off('click').on('click', function(e) {
			var trigger = $(this);
			if ($(this).is('input:checked')) {
				cascadeCheck(trigger);
			} else {
				cascadeUnCheck(trigger);
			}
		});
	}

	/**
	 * Show how many items are checked at page load
	 */
	function initRevealLines() {
		$('p.reveal').append('<span class="selectCount"></span>');
		$('div.items').each(function() {
			countCheckedBoxesIn(this);
		})
	}


	function initVendorRadioChoice() {
		$('input[id*="VendorId"]').off('click').on('click', function() {
			insertAddress(formData.vendorAccess[$(this).val()], 'div.vendorAddress');
		});
		var vendorChoice = $('fieldset#vendorSection input[type="radio"]:checked').val();
		if (typeof (vendorChoice) != 'undefined') {
			insertAddress(formData.vendorAccess[vendorChoice], 'div.vendorAddress');
		}
	}

	function insertAddress(data, parent) {
		$('div.address > .Company > span.text').html(data.name);
		$('div.address > .Address > span.text').html(data.address);
		$('div.address > .Address2 > span.text').html(data.address2);
		$('div.address > .Csz > span.text').html(data.csz);
		$('div.address > input').each(function() {
			$(this).val(data[$(this).attr('field_name')]);
		});
	}

	/**
	 * Check true (open) all decendent checkboxes
	 * 
	 * @param {type} box
	 * @returns {undefined}
	 */
	function cascadeCheck(trigger) {
		// fix the label for the select-all toggle
		var labelObj = $('label[for="' + $(trigger).attr('id') + '"]');
		var labelText = labelObj.html().replace('Select', 'Deselect');
		labelObj.html(labelText);

		// select all the items
		$('div.' + $(trigger).attr('selectAll')).next('div').find('input[type="checkbox"]').prop('checked', true);

		// fix the count for the 'reveal' information line
		countCheckedBoxesIn($('div.' + $(trigger).attr('selectAll')).next('div'));
		synchRows($('div.' + $(trigger).attr('selectAll')).next('div'));
	}

	/**
	 * Check false (close) all decendent checkboxes
	 * 
	 * @param {type} box
	 * @returns {undefined}
	 */
	function cascadeUnCheck(trigger) {
		// fix the label for the select-all toggle
		var labelObj = $('label[for="' + $(trigger).attr('id') + '"]');
		var labelText = labelObj.html().replace('Deselect', 'Select');
		labelObj.html(labelText);

		// select all the items
		$('div.' + $(trigger).attr('selectAll')).next('div').find('input[type="checkbox"]').prop('checked', false);

		// fix the count for the 'reveal' information line
		countCheckedBoxesIn($('div.' + $(trigger).attr('selectAll')).next('div'));
		synchRows($('div.' + $(trigger).attr('selectAll')).next('div'));
	}

	/**
	 * Scan a set of checkboxes and render the proper table rows
	 * 
	 * Provide a container as a source of checkboxes
	 * Add or delete table rows to match the check states
	 * 
	 * @param obj container The source of checkboxes for the scan
	 */
	function synchRows(container) {
		$(container).find('input[id*="ItemId"]').each(function() {
			//	if($(this).prop('checked')) {
			evaluateCheckState(this);
			//	}
		})
	}

	function evaluateCheckState(input) {
		var index = $(input).attr('index');
		if ($(input).prop('checked')) {
			addItemRow(index);
		} else {
			removeItemRow(index);
		}
	}


	/**
	 * Create, destroy or leave table alone as appropriate
	 * 
	 * If the table does not exist, make it
	 * If there are no rows remaing, remove it
	 * Otherwise leave everything alone
	 */
	function manageTable(mode) {
		var table = $('table.po');
		if (table.length == 0 && mode == 'add') { //<tr id="itemRowXX"><td></td> <td></td></tr>
			var html =
					'<table class="po">\n\
				<tbody>\n\
				<tr>\n\
				<th class="codeCol">Code</th>\n\
				<th class="nameCol">Name</th>\n\
				<th class="qtyCol">Quantity</th>\n\
				<th class="noteCol">Note</th>\n\
				<th class="costCol">Cost</th>\n\
				<th class="perCol">Per</th>\n\
				<th class="unitCol">Unit</th>\n\
				<th class="subtotalCol">Subtotal</th>\n\
				</tr>\n\
				</tbody>\n\
				</table>';
			$(html).appendTo('div.view');
			return;
		}
		var tr = $('tr[id*="itemRow"]');
		if (tr.length == 0 && mode == 'remove') {
			$('table.po').remove();
			return;
		}

	}

	function initAddressSelect() {
		$('.addressSelect').on('change', function(e) {
			$.getJSON(webroot + 'addresses/getAddress/' + $(this).val(), function(data) {
				insertAddress(data.Address, 'div.shipAddress');
			})
		});
	}

	initAddressSelect();
	initItemCheckboxes();
	initRevealLines();
	initPage();
	initVendorRadioChoice();
	manageTable('add');


});

/**
 * Calculate subtotals for replenishment line items
 * 
 * @param {object} input
 * @returns {undefined}
 */
function calcSubtotal(input) {
	var id = $(input).attr('data-id');
	$('#subTotal-' + id).html('$' + ($('#quantity-' + id + ' input').val() * $('#price-' + id + ' input').val()));
}



/**
 * Submit a replenishment for save
 * 
 * Performs vendor choice and item choice validation
 * 
 * @param {object} e, the event object
 * @returns {Boolean} 
 */
function submitReplenishment(e) {
//	e.preventDefault();
	if ($('input#ReplenishmentVendorCompany').val().length == 0) {
		alert('You must select a vendor for this PO');
		return false;
	} else if ($('tr[id*="itemRow"]').length == 0) {
		alert('You must select some items for this PO');
		return false;
	} else {
		return true;
//	    $(this).submit();
	}
//	e.stopImmediatePropagation();
}

function findItemsForReplenishments(e) {
	var formData = $('#ReplenishmentCreateReplenishmentForm').serialize();
	$.ajax({
		type: "POST",
		url: webroot + "replenishments/findItemsForReplenishments",
		data: formData,
		dataType: "html",
		success: function(data) {
			$('#findResult').html(data);
			bindHandlers();
		},
		error: function() {
			alert('Error with findItemsForReplenishments.')
		}
	});
}

/**
 * Make individual checkboxes behave properly
 */
function itemChoiceCheckboxes() {
	countCheckedBoxesIn($(this).parents('div.items'));
	var index = $(this).attr('index');
	if ($(this).prop('checked')) {
		addItemRow(index);
	} else {
		removeItemRow(index);
	}
}

/**
 * Get the number of checkboxes checked in the provided div
 */
function countCheckedBoxesIn(div) {
	selectCount = $(div).find('input:checked').length;
	$(div).parents('div.vendorSection').find('span.selectCount').html(' (' + selectCount + ')')
			.animate({backgroundColor: "blue"}, 200)
			.animate({backgroundColor: "#ff0"}, 200)
			.animate({backgroundColor: "#fff"}, 500);
}

/**
 * Add Replen Item row for checkbox item 'index'
 * 
 * @param int index
 */
function addItemRow(index) {
	var row = $('tr[id="itemRow' + index + '"]');
	if (row.length > 0) {
		return;
	}

	$.ajax({
		type: "POST",
		dataType: "json",
		url: webroot + 'replenishments/fetchItemRow',
		data: formData[index],
		async: false,
		success: function(data) {
			$('table.po > tbody').append(data.row);
			var row = $('table[class="po"] > tbody > tr:last-of-type');
			var cell = row.children('td:first-of-type');
			row.attr('id', cell.attr('id'));
			cell.attr('id', '');

			$('table.po input').off('click');
			$('td[id*="quantity-"] input').on('change', function() {
				calcSubtotal(this);
			});
			$('td[id*="price-"] input').on('change', function() {
				calcSubtotal(this);
			});
		}
	});
}

function removeItemRow(index) {
	$('tr[id="itemRow' + index + '"]').remove();
}

function expandCreateReplenishmentScope(e) {
	var scope = $(this).val();
	var webLoc = location.href.replace(/\/\d+$/, '');
	location.replace(webLoc + '/' + scope);
}

/**
 * Select vendor items for replenishment page
 * 
 * @param {event} e
 * @returns redirect to same page with vendor items
 */
function selectVendor(e){
	var replace = true;
	if($('table.po tr').length > 1){
		replace = confirm('Are you sure you want to discard the unsaved replenishment?');
	}
	if(replace){
		var id = $(e.currentTarget).val();
		location.replace(webroot+controller+action+id);
	}
	$(e.currentTarget).val('');
	
}