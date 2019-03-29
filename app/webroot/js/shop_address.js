$(document).ready(function() {

	initAddressCopyToggle();
	initAddressSelect();
//	initSubmit();
	initShippingMethods();
	initShippingCarrier();
	bindHandlers();

	/**
	 * This is the ajax to store default shipping for a company's orders
	 */
	$('#shippingDefault').on('click', function(e) {
		e.preventDefault();

		$.ajax({
			type: "POST",
			dataType: "json",
			url: webroot + controller + 'shippingPreference',
			data: {
				customer: $('#OrderUserCustomerId').val(),
				address: $('#OrderSelectedAddress').val(),
				shipment: {
					carrier: $('#ShipmentCarrier').val(),
					method: $('#ShipmentMethod').val(),
					billing: $('#ShipmentBilling').val(),
					tax_rate_id: $('#ShipmentTaxRateId').val()
				}
			},
			success: function(data) {

			}
		});

	})

	validatePage();
});

/**
 * Bind the address copy toggle and setup data copy on property
 * 
 * The address copy toggle is a checkbox. If it is checked, it copies the
 * data from the billing address into the shipping address and updates the label
 * text th read "Clear billing address from shipping".
 * If is is unchecked, the data is cleared from the form, and the label is reverted.
 * 
 * @returns {undefined}
 */
function initAddressCopyToggle() {
	$('#OrderSameaddress').click(function(e) {
		if ($('#OrderSameaddress').prop("checked")) {
			$('#ShipmentCompany').val($('#OrderBillingCompany').val());
			$('#ShipmentFirstName').val($('#OrderFirstName').val());
			$('#ShipmentLastName').val($('#OrderLastName').val());
			$('#ShipmentEmail').val($('#OrderEmail').val());
			$('#ShipmentPhone').val($('#OrderPhone').val());
			$('#ShipmentAddress').val($('#OrderBillingAddress').val());
			$('#ShipmentAddress2').val($('#OrderBillingAddress2').val());
			$('#ShipmentCity').val($('#OrderBillingCity').val());
			$('#ShipmentState').val($('#OrderBillingState').val());
			$('#ShipmentZip').val($('#OrderBillingZip').val());
			$('#ShipmentCountry').val($('#OrderBillingCountry').val());
			$('#ShipmentFedexAcct').val($('#OrderFedexAcct').val());
			$('#ShipmentUpsAcct').val($('#OrderUpsAcct').val());
			$('label[for="OrderSameaddress"]').html('Clear billing address from shipping');
		} else {
            clearAddress(e);
		}
	});
}

/**
 * Clear ehtries from the shipping address
 * @param {type} e
 * @returns {undefined}
 */
function clearAddress(e){
    $('div.shippingAddress input, div.shippingAddress select').each(function(){
        $(this).val('');
    });
}

/**
 * Pulls the appropriate data to fill the form from user address selection
 * 
 * Using a JSON data format, the js pulls the address data matching the chosen
 * address and inserts it into the form for saving.
 * @returns {undefined}
 */
function initAddressSelect() {
	$('select.addressSelect').on('change', function(e) {
		$('#OrderSelectedAddressSource').val($(this).attr('id'));//set the source to the selector's id attribute
		updateSaveAddressCheckLabel();
		$('#OrderSelectedAddress').val($(this).val()); // id pick-up point for shipping prefs save
		$.getJSON(webroot + 'addresses/getAddress/' + $(this).val() + '/' + $('#OrderUserCustomerId').val(), function(data) {
			$('#ShipmentFirstName').val(data.Address.first_name);
			$('#ShipmentLastName').val(data.Address.last_name);
			$('#ShipmentEmail').val(data.Address.email);
			$('#ShipmentPhone').val(data.Address.phone);
			$('#ShipmentCompany').val(data.Address.company);
			$('#ShipmentAddress').val(data.Address.address);
			$('#ShipmentAddress2').val(data.Address.address2);
			$('#ShipmentCity').val(data.Address.city);
			$('#ShipmentState').val(data.Address.state);
			$('#ShipmentZip').val(data.Address.zip);
			$('#ShipmentFedexAcct').val(data.Address.fedex_acct);
			$('#ShipmentUpsAcct').val(data.Address.ups_acct);
			$('#ShipmentCountry').val(data.Address.country);
			$('#ShipmentTaxRateId').val(data.Address.tax_rate_id);
			$('label[for="OrderSameaddress"]').html('Copy billing address to shipping');
			$('#OrderSameaddress').prop("checked", false);
			// check to see if prefs were save for this situation
			if (data.Shipment != null) {
				$('#ShipmentCarrier').val(data.Shipment.carrier).trigger('change');
				$('#ShipmentMethod').val(data.Shipment.method);
				$('#ShipmentBilling').val(data.Shipment.billing);
				$('#ShipmentTaxRateId').val(data.Shipment.tax_rate_id);
			} else {
//				$('#ShipmentCarrier').val('');
//				$('#ShipmentMethod').val('');
//				$('#ShipmentBilling').val('0');
			}
		})
        //unbind js
        $('select.addressSelect').val('');
        //rebind js        
	});
}

function updateSaveAddressCheckLabel(){
	var source = $('#OrderSelectedAddressSource').val();
	var access = $('#OrderAccess').val();
	var label = ''
	if(source=='OrderConnectedAddresses' && access == 'Manager'){
		label = 'Update Connected Address';		
	} else if (source=='OrderMyAddresses'){
		label = 'Update My Address';
	} else {
		label = 'Save to My Address Book';
	}
	$('label[for="ShipmentSaveToMyAddressBook"]').html(label);
}

function addressCancel() {
		window.location.replace(webroot + 'shop/cart');
}

function initShippingMethods() {
	$('#ShipmentBilling').trigger('mouseup');
//	var method = {
//			'':'',
//			'UPS': 'select#Methods > optgroup[label="UPS"] option',
//			'FedEx': 'select#Methods > optgroup[label="FedEx"] option'
//	}
//	
//var six = 9;	
//	$(method).find('optgroup').attr('label', '');
//	$('select#Methods').remove();
//	$('select#ShipmentMethod').append('<optgroup></optgroup>');
//	$('select#ShipmentMethod').on('change', function() {
//	})
}

function initShippingCarrier() {
	$('select#ShipmentCarrier').on('change', newMethodOptions)
//	$(method[$('select#ShipmentCarrier').val()]).appendTo('select#ShipmentMethod optiton');
}
function newMethodOptions(e){
	$('select#ShipmentMethod').html($('select#'+$(e.currentTarget).val()+' option').clone());
//	$('#ShipmentTpbSelector').val('');
//	$('#ShipmentBilling').val('Sender');
//	$('#ShipmentBilling').trigger('mouseup');
}

function prevalidateSubmit(e) {
	validatePage();
	if($.valid == false){
		return false;
	} else {
		return true;
	}
}

function shippingBilling(e){
    switch ($('#ShipmentBilling').val()) {

        // bill the shipping to a third party
        case 'ThirdParty':
            //choice block
            //reveal acct number & address block
            $('div.thirdParty').removeClass('hide');
            $('div.tpbAddress').removeClass('hide');
			$('div.addressClosingButtons > button').css('margin-top', '60px');
            break;

        // bill the shipping to the sender (ie AMP)
        case 'Sender':
            //clear all tpb fields
			$('#ShipmentBillingAccount').val('');
			$('#ShipmentTpbCompany').val('');
			$('#ShipmentTpbAddress').val('');
			$('#ShipmentTpbCity').val('');
			$('#ShipmentTpbState').val('');
			$('#ShipmentTpbZip').val('');
			$('#ShipmentTpbPhone').val('');
            //hide both blocks
            $('div.thirdParty').addClass('hide');
            $('div.tpbAddress').addClass('hide');
			$('div.addressClosingButtons > button').css('margin-top', '20px');
            break;

        // bill the shipping to the shipment address
        case 'Receiver':
            //set data
            if($('#ShipmentCarrier').val() == 'UPS'){
                var acct = $('#ShipmentUpsAcct').val();
            } else {
                var acct = $('#ShipmentFedexAcct').val();
            }
			if ($('#ShipmentBillingAccount').val() == '') {
				$('#ShipmentBillingAccount').val(acct);
			} else {
				
			}     
			//reveal account block
            $('div.tpbAddress').addClass('hide');
            $('div.thirdParty').removeClass('hide');
			$('div.addressClosingButtons > button').css('margin-top', '20px');
            break;

        // bill the shipment to the billing address
        case 'Customer':
            //set data
            if($('#ShipmentCarrier').val() == 'UPS'){
                var acct = $('#OrderUpsAcct').val();
            } else {
                var acct = $('#OrderFedexAcct').val();
            }
			if ($('#ShipmentBillingAccount').val() == '') {
				$('#ShipmentBillingAccount').val(acct);
			} else {
				
			}			
            //reveal account block
            $('div.tpbAddress').addClass('hide');
            $('div.thirdParty').removeClass('hide');
			$('div.addressClosingButtons > button').css('margin-top', '20px');
            break;
    }

}

function thirdPartyBillingSelector(e){
	var addrId = $('#ShipmentTpbSelector').val();
	$.getJSON(webroot + 'addresses/getAddress/' + addrId + '/' + $('#ShipmentTpbSelector').val().match(/\d*/)[0], function(data) {
		$('#ShipmentTpbPhone').val(data.Address.phone);
		$('#ShipmentTpbCompany').val(data.Address.company);
		$('#ShipmentTpbAddress').val(data.Address.address);
		$('#ShipmentTpbCity').val(data.Address.city);
		$('#ShipmentTpbState').val(data.Address.state);
		$('#ShipmentTpbZip').val(data.Address.zip);
		
		if ($('#ShipmentCarrier').val() == 'UPS') {
			$('#ShipmentBillingAccount').val(data.Address.ups_acct);
		} else {
			$('#ShipmentBillingAccount').val(data.Address.fedex_acct);
		}		
	})
}

function nextAddressSection(e) {
	$(e.currentTarget).parent('div').trigger('validate');
}

/**
 * Initialize a global variable to track validation of the address sections
 */
$.valid = true;

/**
 * Validate each page section, possibly stopping on the one that didn't validate
 * 
 * During page entry or exit we need to see if any section still needs work. 
 * This cascades through, running each validator in turn. If the section passes 
 * It will be closed up and the next will be opened.
 * 
 * If all pass, the last one will be left open and we'll get and return a true. 
 * If any fails, it will be left open and we'll get and return a false.
 * 
 * @returns void
 */
function validatePage(){
	if(controller == 'shop/'){
	$.valid = true;
		$('div.addressSection').children('div').each(function(){
			$(this).trigger('validate');
//			alert('$.valid is '+$.valid);
			if($.valid == false){
				return false;
			}
		})
		return true;
	}
}

/**
 * Validation of Billing Address section
 * 
 * Billing address currently has no data input requirements 
 * so it just closes itself up and returns true (which will 
 * allow the next validation to run if we are in a sequential process)
 * 
 * @param event e
 * @returns {Boolean}
 */
function validateBillingAddress(e){
	initValidator(e);
	if (true) {
		sectionIsValid(e);
	} else {
		sectionIsInvalid(e);
	}
}

/**
 * Attempt to process the Reference Section optional field data
 * 
 * In addition to saving the data, we set a flag value to show this section 
 * has been reviewed even though no data need be entered
 * 
 * @param {event} e
 * @returns {undefined}
 */
function approveReferencesAddress(e){
	$('#OrderReferenceApproval').val(1);
//	var inputs = $('#OrderAddressForm').serialize();
//	var inputs = $('div.referencesAddress').serialize();
	var inputs = $('#optionalFields').serialize();
	$.ajax({
		type: "POST",
		dataType: "json",
		data: inputs,
		url: webroot + controller + 'saveNoteAndReference',
		success: function(data) {
			if (data.save) {
				$(e.currentTarget).parent().trigger('validate');
			} else {
				return false;
			}	
		}
	});
	
}

/**
 * Validate optional-data section
 * 
 * Reference number, Note and Documents are all optional data 
 * so once the section has been viewed by the user, the OrderReferenceApproval 
 * field will get a 1. ('Next' button runs approveReferenceAddress() to do this) 
 * 
 * The validation is simply a check of this flag value and will close this section, 
 * open the next and return true if it passes. Leave this section open and return 
 * false if we fail validation
 * 
 * @param {event} e
 * @returns {Boolean}
 */
function validateReferencesAddress(e){
	initValidator(e);
	//	
	if($('#OrderReferenceApproval').val() == 1){
		return sectionIsValid(e)
	} else {
		return sectionIsInvalid(e);
	}
}

/**
 * Validate the Shipping address section
 * 
 * @param {event} e
 * @returns {Boolean}
 */
function validateShippingAddress(e){
	var ev = e;
	initValidator(e);
	
	var i = $(e.currentTarget).find('input[required="required"]');
	i.each(function(){
		if($(this).val() == ''){
			m = createFlashMessage('All required Address entries must be filled', 'alert-error');
			$('div.shippingAddress').before(m);
			return sectionIsInvalid(ev);
		} else {
			$.valid = true;
		}
	})
	if ($.valid == true) {
		sectionIsValid(e);
	}
}

/**
 * Validate the Shipper data
 * 
 * @param {event} e
 * @returns {Boolean}
 */
function validateShippingMethod(e){
	initValidator(e);

	var message = '';
	if ($('#ShipmentCarrier').val() === '') {
		message += 'Carrier is required. ';
	}
	if ($('#ShipmentMethod').val() === '') {
		message += 'Method is required. ';
	}
	if ($('#ShipmentTaxRateId').val() === '') {
		message += 'Tax jurisdiction is required. ';
	}

	if (message != '') {
		m = createFlashMessage(message, 'alert-error');
		$(this).prev().after(m);
		return sectionIsInvalid(e);
	} else {
		return sectionIsValid(e);
	}
}

/**
 * Prepare conditions for all Section validators
 * 
 * Initialize the global signal value
 * Remove any old flash messages
 * 
 * @param {element} e
 * @returns {void}
 */
function initValidator(e) {
	$.valid = true;
	removeFlashMessages($(e.currentTarget).parent('div'));
}

/**
 * Do all the procedures for successful validation of a Section
 * 
 * Take care of visiblity
 * Set the global signal value
 * Set the h2 to green
 * 
 * @param {event} e
 * @returns {Boolean}
 */
function sectionIsValid(e){
	nextShippingStep(e);
	$.valid = true;
	$(e.currentTarget).siblings('h2').css('color', '#5CB85C');
	return true;
}

/**
 * Do all the procedures for a failed Section validation
 * 
 * Set the global signal value
 * Set the h2 to red
 * 
 * @param {event} e
 * @returns {Boolean}
 */
function sectionIsInvalid(e) {
	$.valid = false;
	$(e.currentTarget).siblings('h2').css('color', '#e32');
	return false;
}

/**
 * Basic Next button behavior
 * 
 * Close up the current section and open the next section
 * 
 * @param {event} e
 * @returns {void}
 */
function nextShippingStep(e){
	$(e.currentTarget).parents('div.addressSection').children('h2').next().attr("style", "display: none;");
	var next = $(e.currentTarget).parents('div.addressSection').next().children('h2');
//	if(next.next().attr('style').match('none') != null){
	if(next.next().css('display') == 'none'){
		next.trigger('click');
	}
}
