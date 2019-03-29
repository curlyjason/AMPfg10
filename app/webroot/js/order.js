$(document).ready(function() {
    /* 
     * Javascript for orders
     */
    function initQuantity() {
        $('.numeric').on('change', function(event) {

            var quantity = Math.round($(this).val());

            var id = $(this).attr("data-id");

            ajaxOrder(id, quantity);

        });
    }

    function initRemove() {
        $(".remove").each(function() {
            $(this).replaceWith('<a class="remove" id="' + $(this).attr('id') + '" href="' + webroot + 'order/remove/' + $(this).attr('id') + '" title="Remove item"><img src="' + webroot + 'img/icon-remove.gif" alt="Remove" /></a>');
        });

        $(".remove").click(function() {
            ajaxOrder($(this).attr("id"), 0);
            return false;
        });
    }

    function initOrderStatus() {
        $("input[id*='OrderStatus']").off('click').on('click', null, $(this), orderStatusClick)
    }

    function ajaxOrder(id, quantity) {

        if (quantity === 0) {
            $('#row-' + id).fadeOut(1000, function() {
                $('#row-' + id).remove();
            });
        }
        
//        if($('#row-' + id).parents('table').parents('table').attr('class').match(/Replenishments/)){
        if($('#row-' + id).parents('table').attr('class').match(/Replenishment/)){
				var action = 'replenishments/updateReplenishmentItem/';
				var alias = 'Replenishment';
			} else {
				var action = "orders/updateOrderItem/";
				var alias = 'Order';
			}

			$.ajax({
	//            type: "POST",
				url: webroot + action + id + '/' + quantity,
	//            data: {
	//                id: id,
	//                quantity: quantity
	//            },
				dataType: "JSON",
				success: function(data) {
					

					if (alias == 'Order') {
						refreshAvailable(data.Available, '.avail');
						updateBudget(data);
					} else {
						refreshAvailable(data.Pending, '.pend');
					}

					// Remove the deleted order and leave
					if(typeof(data.deletedOrder) != 'undefined' || typeof(data.deletedReplenishment) != 'undefined'){
						if (typeof(data.deletedOrder) != 'undefined') {
							var alias = 'Order';
						} else {
							var alias = 'Replenishment';
						}
						// fix the count of items in the header
						var count = $('#'+data[alias]['id']).parents('table').siblings('table').length;
						$('#'+data[alias]['id']).parents('table').siblings('h3').children('span.count').html(count);
						
						// if deleting the first order, make the next one show the header-label row
						if ($('#'+data[alias]['id']).parents('table').prev('table').length == 0 && $('#'+data[alias]['id']).parents('table').next('table').length > 0) {
							$('#'+data[alias]['id']).parents('table').next('table').removeClass('notFirst').addClass('First');
						}
						$('#'+data[alias]['id']).parents('tbody').remove();
//						document.location.reload();
						return

					// Update the changed item
					} else if (typeof(data[alias]) != 'undefined') {
						$('#total-' + data[alias]['id']).html(data[alias]['total']).animate({backgroundColor: "#ff8"}, 100).animate({backgroundColor: "#fff"}, 250);
						$('#itemCount-' + data[alias]['id']).html(data[alias]['order_item_count']).animate({backgroundColor: "#ff8"}, 100).animate({backgroundColor: "#fff"}, 250);
					}

					// Remove the deleted item and leave
					if (typeof(data.deletedItem) != 'undefined') {
						$('#row-' + data.deletedItem.id).fadeOut(300, function() {
							$('#row-' + data.deletedItem.id).remove();
						});
						return;

					// Update the changed item
					} else if (typeof(data.Item) != 'undefined') {
						$('#subtotal-' + data.Item[alias+'Item']['id']).html(data.Item[alias+'Item']['subtotal']).animate({backgroundColor: "#ff8"}, 100).animate({backgroundColor: "#fff"}, 250);
						if (typeof(data.Order) != 'undefined') {
							$('span.availableQty'+data.Item.Item.id).html(data.Item.CalcQty);
						} else if (typeof(data.Replenishment) != 'undefined') {
							$('span.pendingQty'+data.item_id).html(data.pending);
						}
					}
				}, // end of success method
		    
            error: function(data) {
                alert("There was a problem updating the order. Please try again.");
            }
        });
    }

    function orderStatusClick(data) {
//    alert(data);
        var id = data.currentTarget.id.replace("OrderStatus", "");
        var status = $('label[for="' + data.currentTarget.id + '"]').html();
        window.location.replace(webroot + "orders/statusChange/" + id + "/" + status);
    }
    
    /**
     * ===========================================================================
     * REPLENISHMENT PRICE/UNIT/POQTY CHANGE TOOLS
     * ===========================================================================
     */
    
//<div class="po_price">
//<input type="text" value="1" class="po_quantity" id="po_quantity-52b3a6db-cf00-45b1-9d93-4f5647139427" name="data[po_quantity]"> 
//per 
//<input type="text" value="pc" class="unit" id="unit-52b3a6db-cf00-45b1-9d93-4f5647139427" name="data[po_unit]"> 
//at $<input type="text" value="0.00" class="price" id="price-52b3a6db-cf00-45b1-9d93-4f5647139427" name="data[price]"> 
//<span class="po_unit">pc</span>\n\
//    
//</div>

    function initReplenPriceInputs(){
	$('div.po_price > input.po_quantity').on('change', function(){
	    writeNewPoQty($(this).attr('id').replace('po_quantity-', ''), $(this).val());
	})
	$('div.po_price > input.unit').on('change', function(){
	    writeNewPoUnit($(this).attr('id').replace('unit-', ''), $(this).val());
	})
	$('div.po_price > input.price').on('change', function(){
	    writeNewPoPrice($(this).attr('id').replace('price-', ''), $(this).val());
	})
    }

    function writeNewPoUnit(id, unit){
	$.ajax({
	    url: webroot + "replenishmentItems/writeNewPoUnit",
	    type: "POST",
	    data: {
		id: id,
		po_unit: unit
	    },
	    dataType: "json",
	    error: function(data){ alert("There was a problem changing the unit. Please try again."); },
	    success: function(data) {
		if (data.save) {
		    // the value may be multiple places in this line item
		    // the id is the ReplenItem id, it has its own unique unit
		    $('.po_unit'+data.id).html(data.po_unit);
		}
	    }
	})
    }

    function writeNewPoQty(id, qty){
	$.ajax({
	    url: webroot + "replenishmentItems/writeNewPoQty",
	    type: "POST",
	    data: {
		id: id,
		po_quantity: qty
	    },
	    dataType: "json",
	    error: function(data){ alert("There was a problem changing the unit. Please try again."); },
	    success: function(data) {
		if (data.Pending) {
		    // the pending value for this Item may be in many line items. Update them all
			refreshAvailable(data.Pending, '.pend');
//		    $('.pendingQty'+data.pendingItem).html(data.pending);
		    // update the line subtotal
		    refreshReplenishmentItemSubtotal(data);
		    // update the header record
		    refreshReplenishmentHeader(data);
		}
	    }
	})
    }
    
    /*
     * Page event changed the ReplenishmentItem price per unit
     * 
     * Call to update the database
     * refresh all the status page values
     */
    function writeNewPoPrice(id, price){
//	alert(id + ' ' + price);
	$.ajax({
	    url: webroot + "replenishments/writeNewPoPrice",
	    type: "POST",
	    data: {
		id: id,
		price: price
	    },
	    dataType: "json",
	    error: function(data){ alert("There was a problem changing the price. Please try again."); },
	    success: function(data) {
		if (data.save) {
		    // update the line subtotal
		    refreshReplenishmentItemSubtotal(data);
		    // update the header record
		    refreshReplenishmentHeader(data);
		}
	    }
	})
    }
    
    function refreshReplenishmentItemSubtotal(data){
	if (typeof(data.Item.ReplenishmentItem.subtotal) != 'undefined') {
	    $('#subtotal-' + data.Item.ReplenishmentItem.id).html(data.Item.ReplenishmentItem.subtotal);
	} else {
	    alert('There was a problem. No subtotal was returned.');
	}
    }
    
    function refreshReplenishmentHeader(data){
	if (typeof(data.Replenishment.id) != 'undefined') {
	    $('#total-' + data.Replenishment.id).html(data.Replenishment.total);
	    $('#itemCount-' + data.Replenishment.id).html(data.Replenishment.order_item_count);
	} else {
	    alert('There was a problem. No Replenishment data was returned.');
	}
    }
    
    initQuantity();
    initRemove();
    initOrderStatus();
    initReplenPriceInputs();

    $("td.cartItem:first-of-type").each(function(){
		moveRowId($(this));
    });

});