$(document).ready(function() {
});

function editShipment(e) {
	e.preventDefault();
	
//	saveInvoiceCharges();
	$(e.currentTarget).parent('td').append('<div class="toolPallet" id="shipmentTools"><p></p><p id="dummy">Loading shipment...</p></div>')
	$('#shipmentTools').css('left', 100).css('width', 950).css('position', 'absolute').css('display', 'block');
	
	$.ajax({
		type: "GET",
		dataType: 'HTML',
		url: $(e.currentTarget).attr('href'),
		success: function(data) {
			$(data).replaceAll('p#dummy');
			bindHandlers();
			initAddressSelect();
			initShippingMethods();
			initShippingCarrier();
		},
		error: function(data) {
			alert('The server died of sticker shock while pulling the invoice charges. CPR has been administered. Please try again.');
		}
	});
}
	
function addressEditCancel() {
		$('#shipmentTools').remove();
}

function saveShipment(e) {
	e.preventDefault();
	var hook = 'shipping-' + $(e.currentTarget).attr('orderLink');
	var data = $(e.currentTarget).parents('form').serialize();
	
	$.ajax({
		type: "POST",
		dataType: 'JSON',
		data: data,
		url: webroot + 'shipments/' + 'saveOrderShipment',
		success: function(data) {
			if(data == false){
				alert('The shipment did not save.');
			} else {
				$('#' + hook).html(data[hook]);
				addressEditCancel();
			}
		},
		error: function(data) {
			alert('The attempt to save this shipment failed.');
		}
	});
}
