$(document).ready(function() {
	
	/**
	 * Display invoice if coming from create invoice context
	 */
	function displayInvoicePDF() {
		if (typeof(showInvoicePDF) != 'undefined'){
			window.open(
				webroot + 'invoices/viewOldInvoice/' + showInvoicePDF + '.pdf',
				'_blank' // <- This is what makes it open in a new window
			); 
		}
	}
    
    function initGrainSectionButtons() {

        if (typeof(customer) != 'undefined' && typeof(customer) == 'string') {
            $('button.user.grainEdit').on('click', function() {
                suppressGrainButtons();
                validateAccess($('.userDisplay').attr('id'));
                $(this).next().prepend('<div class="ajaxLoading" id="newForm">Loading the edit form. Please wait ...</div>');
                $('div.view').load(webroot + 'customers/edit_customer/' + customer, function(){
                        bindHandlers(); //bindBasicCancelButton\(\$(this).find('.cancelButton'));
                        initToggles();
						initPullFeesLink();
                });
            })
        } else {
            $('button.user.grainEdit').on('click', function() {
                suppressGrainButtons();
                validateAccess($('.userDisplay').attr('id'));
                var params = setGrainButtonParameters('.userDisplay > p', 'Edit/', '');
//            var action = $(this).parent().attr('class').replace('Display', 'Edit/');
                $('div.userDisplay').prepend('<div class="ajaxLoading" id="newForm">Loading the edit form. Please wait ...</div>');
                $('div.userDisplay').load(webroot + controller + params.action + $('.userDisplay').attr('id'), function() {
//                $(this).load(webroot + controller + params.action + $('.userDisplay').attr('id'), function() {
                    if (params.section == 'user') {
                        initUserGrainForm($(this)); // *this* is the div at this point
                        initToggles();
                    } else if (params.section == 'userPermission') {
                        bindHandlers(); //bindBasicCancelButton\(\$(this).find('.cancelButton'));
                        initToggles();
                    } else if (params.section == 'catalogPermission') {
                        bindHandlers(); //bindBasicCancelButton\(\$(this).find('.cancelButton'));
                        initToggles();
                    }
                });
            })
        }	
        // User Section Edit Button
        $('div[class*="Display"] > .grainEdit').bind('click', function() {
            
            suppressGrainButtons();
            validateAccess($(this).parent().attr('id'));
            var params = setGrainButtonParameters(this, 'Edit/', '');
//            var action = $(this).parent().attr('class').replace('Display', 'Edit/');
            $(this).parent().children('div.target').prepend('<div class="ajaxLoading" id="newForm">Loading the add form. Please wait ...</div>');
            $(this).parent().children('div.target').load(webroot + controller + params.action + $(this).parent().attr('id'), function() {
                $(this).next('table').remove();
                if (params.section === 'user') {
                    initUserGrainForm($(this)); // *this* is the div at this point
                } else if (params.section === 'userPermission') {
                        bindHandlers(); //bindBasicCancelButton\(\$(this).find('.cancelButton'));
                } else if (params.section === 'catalogPermission') {
                        bindHandlers(); //bindBasicCancelButton\(\$(this).find('.cancelButton'));
                }
            });
        })

	// Both versions of Observer form
	// Button is in the div, over the header and
	// A table with at least a starter header row is waiting
        $('div[class*="bserverDisplay"] > .grainNew').bind('click', function() {
            
            suppressGrainButtons();
            validateAccess($('div.userDisplay').attr('id'));
            action = $(this).parent().attr('class').replace('Display', 'Add/');
            var tableBody = $(this).parent().find('tbody');
			var colspan = $(tableBody).children('tr:first-child').children('td').length;
            $(tableBody).prepend('<td class="ajaxLoading" id="newForm" colspan="' + colspan + '">Loading the add form. Please wait ...</td>');
//            $(tableBody).prepend('<tr><td class="ajaxLoading" id="newForm">Loading the add form. Please wait ...</td></tr>');
            $('#newForm').load(webroot + controller + action + $('div.userDisplay').attr('id'), function() {
				$('#newForm').removeClass('ajaxLoading');
				initObserverGrainForm($(this)); // *this* is the div at this point
            });
            // end of the primary section New or Edit button

        })

	// Edit an Observer or Address record
        $('tr > td > .grainEdit').bind('click', function() {

            suppressGrainButtons();
            var params = setGrainButtonParameters(this, 'Edit/', 'e');
            //set loading message
            var colspan = $(this).parents('tr').find('td').length;
            $(this).parents('tr').html('<td class="ajaxLoading" id="' + params.section + '" colspan="' + colspan + '">Loading the edit form. Please wait ...</td>');

            // The click on the primary section New or Edit button
            var editingRecordId = $(this).attr('id').replace(params.idPrefix, '');
			validateAccess($('.userDisplay').attr('id'));
            $('#' + params.section).load(webroot + controller + params.action + editingRecordId, function() {
                $('#' + params.section).attr('class', 'ajaxLoaded');
                if(params.section == 'address'){
                    initAddressGrainForm($(this));
                } else if (params.section.match(/bserver/)){
                    initObserverGrainForm($(this));
                } else if (params.section == 'vendor'){
                    initVendorGrainForm($(this));
                }
            });

        })

	// Add a new Address record
        $('tr > td > .grainNew').bind('click', function() {

            suppressGrainButtons();
            id = $(this).attr('id').replace('address', '');
            validateAccess(id);
            action = $(this).parents('div').attr('class').replace('Display', 'Add/');
            //set loading message
            var colspan = $(this).parents('tr').children('td').length;
			var colspansSet = $(this).parents('tr').children('td');
			var c = 0;
			var i = 0;
			
			while (i < colspansSet.length) {
				var a = (typeof($(colspansSet[i]).attr('colspan')) == 'undefined') ? 1 : parseInt($(colspansSet[i]).attr('colspan'));
				c = c + a - 1;
				i++;
			}
			
			colspan = colspan + c;
            $(this).parents('tr').html('<td id="editAddress" colspan="' + colspan + '"><p> class="ajaxLoading">Loading the edit form. Please wait ...</p></td>');

            // The click on the primary section New or Edit button
            $('#editAddress').load(webroot + controller + action + id, function() {
                    initAddressGrainForm($(this));
            });

        })
        
        //Bind new button for vendor grain page
        if(location.href.match('manageVendors')){
            $('button.grainNew').off('click').on('click', function(){
                    suppressGrainButtons();
                    var action = 'vendorAdd/';
                    $('div.view').load(webroot + controller + action, function() {
                        initVendorGrainForm($(this));
                    })
            })
        }
        
        
    }

    function suppressGrainButtons() {
        $('button.grainEdit, button.grainNew, button.grainDelete').css('display', 'none');
    }
    
    function initVendorGrainForm(wrapper) {
    // The Cancel button in the loaded form
    bindHandlers(); //bindBasicCancelButton\(\$(wrapper).find('.cancelButton'));
    $('#AddressName').select();
    }

    // This link brings in an ajax form to edit quantity pull prices
    // for companies. Other user grain nodes won't have this link
    // and it only appears for Staff/Admins Managers
    function initPullFeesLink(){
	$('#pullFees').on('click', function(e){
	    e.preventDefault();
	    suppressGrainButtons();
		validateAccess($(this).attr('customer_id'));
		$(this).parents('p.decoration').replaceWith('<div class="pull_fee"><p class="ajaxLoading">Loading the edit form. Please wait ...</p></div>');

	    $.ajax({
			type: "POST",
			dataType: "html",
			url: webroot + 'prices/pullFees',
			data: {
				id : $(this).attr('customer_id')
			},
			success: function(data) {
				$('div.pull_fee').html(data);
				initPullFeeInputChanges();
				initPullFeeRowDeletion();
				initPullFeeNewRowTool();
				bindHandlers(); //bindBasicCancelButton\(\$('.cancelButton'));
				initPullFeeSubmitButton();
				initTestMaxQty();
			},
			error: function(data) {
				alert('Unable to retrieve the form. Please try again');
			}
	    });
	});
    }
	
	/**
	 * Setup test max qty field
	 * 
	 * @returns {undefined}
	 */
	function initTestMaxQty(){
		$('input.HI').each(function(){
			$(this).parents('tr').find('input.MAX').val($(this).val());
		});
	}
	/**
	* Make the PullFee new record button generate new empty data row
	* 
	* and have the remove feature work too
	*
	* @returns {undefined}	
	*  */
	function initPullFeeNewRowTool() {
		$('#newFee').on('click', function(){
		var customer_id = $('#PricePullFeesForm > table').attr('customer_id');
		var count = $('#PricePullFeesForm > table > tbody > tr').length;
		$('#PricePullFeesForm > table > tbody').append(newRow.replace(/XXX/g, count).replace('ZZZ', customer_id));
		initPullFeeInputChanges();
		$('a.removeNew').off('click').on('click', function(e){
			e.preventDefault();
			$(this).parents('tr').remove();
		});
		});
	}
	/**
	* Init the garbage cans for existing data rows; pull fee data
	*
	 * @returns {undefined}	 */
	function initPullFeeRowDeletion() {
		$(".remove").each(function() {
			$(this).replaceWith('<a class="remove" id="' + $(this).attr('id') + '" href="' + webroot + 'prices/delete/' + $(this).attr('price_id') + '" title="Remove item"><img src="' + webroot + 'img/icon-remove.gif" alt="Remove" /></a>');
		});

		$('a.remove').on('click', function(e){
		e.preventDefault();
		$.post($(this).attr('href'), function(data){
			if (data) {
			$(e.currentTarget).parents('tr').remove();
			} else {
			alert('There was an error when deleting that record. Please try again.')
			}
		});
		});
	}

	/**
	* Init the Submit button
	*
	 * @returns {undefined}	 */
	function initPullFeeSubmitButton() {
		$("#validate").off('click').on('click', function(e){
			var mydata = $("#PricePullFeesForm").serialize();
			$.ajax({
				type: "POST",
				dataType: "json",
				url: webroot + 'prices/savePullFees',
				data: mydata,
				success: function(data) {
					if(data == 'true'){
						location.reload();
					} else if(data == 'false') {
						//the record did not save, but did validate
						alert('There was a save failure, please try again.');
					} else {
						//the record did not validate, data is the validation errors
						alert(data);
					}
				},
				error: function(data) {
					alert('There was a save failure. Please try again.');
				}
			});
		});
	}

    function setGrainButtonParameters(element, action, prefix) {
        var divId = $(element).parents('div').attr('class');
        var result = new Object();
        result.action = divId.replace('Display', action);
        result.section = divId.replace('Display', '');
        result.idPrefix = prefix + divId.replace('Display', '');
        return result;

    }
    
    /**
     * Transfer input changes to the label cell of the row
     */
    function initPullFeeInputChanges(){
	$('input.LO').off('change').on('change', function(){
	    $(this).parents('tr').find('span.LO').html($(this).val());
	});
	$('input.HI').off('change').on('change', function(){
	    $(this).parents('tr').find('span.HI').html($(this).val());
	    $(this).parents('tr').find('input.MAX').val($(this).val());
	});
	$('input.PRICE').off('change').on('change', function(){
	    $(this).parents('tr').find('span.PRICE').html($(this).val());
	});
    }
    
    initGrainSectionButtons();
    initPullFeesLink();
	displayInvoicePDF();

var newRow = '\n\
<tr>\n\
<td>\n\
<p>\n\
<span class="LO"></span> to <span class="HI"></span> for $<span class="PRICE"></span>\n\
<input type="hidden" value="ZZZ" name="data[XXX][Price][customer_id]">\n\
<input type="hidden" class="MAX" value="" name="data[XXX][Price][test_max_qty]">\n\
</p>\n\
</td> <td>\n\
<input class="LO" type="number" name="data[XXX][Price][min_qty]">\n\
</td> <td>\n\
<input class="HI" type="number" name="data[XXX][Price][max_qty]">\n\
</td> <td>\n\
<input class="PRICE" type="number" name="data[XXX][Price][price]">\n\
</td> <td>\n\
<a title="Remove item" href="" class="removeNew">\n\
<img alt="Remove" src="'+webroot+'img/icon-remove.gif">\n\
</a>\n\
</td>\n\
</tr>';
// if any of 3 inputs change, update the coresponding label line span

// delete button calls to kill a record then on success, kill the row

// new row button makes a new row with empty inputs
// 
});

function revealItemsToggle(e){
	e.preventDefault();
	var target = '.'+$(this).attr('id');
    $('.'+$(this).attr('id')).toggle(50, function(){
		// if the the children are visible
		if ($('.'+$(e.currentTarget).attr('id')+':visible').length > 0) {
			// Set message to 'collapse'
			$(e.currentTarget).html('Collapse Items');
		} else {
			// Set message to 'reveal'
			$(e.currentTarget).html('Reveal Items');
		}
	});
}

function observerDelete(e){
	deleteRowRecord(e, 'observers');
}

function addressDelete(e){
	deleteRowRecord(e, 'addresses');
}
/**
 * Delete button for both observer section on user grain page
 * 
 * works if the delete button is in a tr > td
 * and if the button has a unique id with the record id encoded
 * and if the record id is numberic and the only numbers in the id
 * 
 * @param event e
 * */
function deleteRowRecord(e, controllerName){
	//get the record id and indicate process in the button look
	var id = $(e.currentTarget).attr('id').match(/[0-9]+/);
	$(e.currentTarget).css('font-weight', 'bold').css('color', 'firebrick').html('Deleting');
	
	$.ajax({	
		type: "POST",
		dataType: "json",
		data : {
			button : $(e.currentTarget).attr('id'),
			id: id
		},
		url: webroot + controllerName+'/delete/'+id,
		success : function(data){
//			var element = $('button[id*="bserver'+data.id+'"]')
			
			// ok! remove the row
			if(data.result === true){
				$('#'+data.button).parents('tr').remove();
			
			// server couldn't delete it. restore the row
			} else {
				var message = 'Please try again. This item was not deleted: ';
				restoreObserverRow('#'+data.button, message);
			}
		},
				
		// ajax failed. restore the row
		error : function(e, id){
//			var element = $('#duserObserver'+data.id)
			var message = 'There was an process error while trying to delete ';
			restoreObserverRow('#'+data.button, message);
		}
	});
}
/**
 * Handle alert and interface restoration on failed observer delete
 * 
 * @param object element The button that was clicked
 * @param string message The message to alert
 */
function restoreObserverRow(element, message){
	var identifier = $(element).parents('tr').children('td.name') + '/' + $(element).parents('tr').children('td.type')
	alert(message + identifier);
	$(element).css('font-weight', 'normal').css('color', 'black').html('Delete');
}

function liveInvoice(e) {
	location.replace(webroot + 'invoiceItems/fetchInvoiceLis/' + $(e.currentTarget).attr('customer') + '/Customer')
}

/**
 * Edit the Invoice Number on an invoice line
 */
function editInvoiceLine(e) {
	if($(e.currentTarget).children('input').length == 0){
		var val = $(e.currentTarget).html();
		$(e.currentTarget).html('<input id="InvoiceJobNumber" type="text" maxlength="6" name="data[Invoice][job_number]" style="width:70px" oldValue="' + val + '">');
		$(e.currentTarget).children('input').val(val).on('blur', saveInvoiceLine).select();
	}
}

/**
 * Save the invoice Number on an invoice line
 */
function saveInvoiceLine(e) {
	var target = $(e.currentTarget);
	var id = $(e.currentTarget).parent('span').attr('id');
	var val = $(e.currentTarget).val();
	var oldVal = $(e.currentTarget).attr('oldValue');
	if(oldVal === val){
		target.parent('span').html(val);
		return;
	}
	removeFlashMessages(target.parents('div'));
	
	$.ajax({
		type: "GET",
		dataType: "JSON",
		url: webroot + 'invoices/saveInvoiceNumber/' + id + '/' + val,
		success: function(data) {
			target.parent('span').html(val);
			$('div.invoiceDisplay h3').after(createFlashMessage('Invoice number saved.', 'alert-success'));
		},
		error: function(data) {
			target.parent('span').html(oldVal);
			$('div.invoiceDisplay h3').after(createFlashMessage('Invoice number did not save. Please try again.', 'alert-error'));
		}
	})

}

/**
 * Print the invoice based on the invoice line
 */
function printInvoiceLine(e) {
	location.window('load')
}