$(document).ready(function(){

	$('.numeric1').on('keypress', function(event) {
		if (event.keyCode == 13) { //enter key
			return true;
		}
		return (/\d/.test(String.fromCharCode(event.keyCode)));
	});

	$('.numeric').on('keyup change', function(event) {

		var quantity = Math.round($(this).val());

        //if you delete or backspace your value out, do not update cart
        if ((event.keyCode == 46 || event.keyCode == 8) && quantity > 0) { //delete or backspace key
		} else {
			if(/\d/.test(String.fromCharCode(event.keyCode)) === false) {
				return false;
			}
		}

		var id = $(this).attr("data-id");

		ajaxcart(id, quantity);

	});

	$(".remove").each(function() {
		$(this).replaceWith('<a class="remove" id="' + $(this).attr('id') + '" href="' + webroot + 'shop/remove/' + $(this).attr('id') + '" title="Remove item"><img src="' + webroot + 'img/icon-remove.gif" alt="Remove" /></a>');
	});

	$(".remove").click(function() {
		ajaxcart($(this).attr("id"), 0);
		return false;
	});
    
    $("td.cartItem:first-of-type").each(function(){
		moveRowId($(this));
    });
    
    $(".clearCart").off('click').on('click', function(){
					window.location.assign(webroot + "shop/clear");
    });
    
    $('.recalculate').off('click').on('click', function(){
					window.location.replace(webroot + "shop/cartupdate");
    });

    $('.checkout').off('click').on('click', function(){
		window.location.replace(webroot + "shop/address");
    });

	function ajaxcart(id, quantity) {

		if(quantity === 0) {
			$('#row-' + id).fadeOut(1000, function(){ $('#row-' + id).remove(); });
		}

		$.ajax({
			type: "POST",
			url: webroot + "shop/itemupdate",
			data: {
				id: id,
				quantity: quantity
			},
			dataType: "json",
			success: function(data) {
				$.each(data.OrderItem, function(key, value) {
					if($('#subtotal-' + key).html() != value.subtotal) {
						$('#ProductQuantity-' + key).val(value.quantity);
						$('#subtotal-' + key).html(value.subtotal).animate({ backgroundColor: "#ff8" }, 100).animate({ backgroundColor: "#fff" }, 500);
					}
				});
				
                refreshAvailable(data.Available, '.avail');
				updateBudget(data);
				
				$('#subtotal').html('$' + data.Order.total).animate({ backgroundColor: "#ff8" }, 100).animate({ backgroundColor: "#fff" }, 500);
				$('#total').html('$' + data.Order.total).animate({ backgroundColor: "#ff8" }, 100).animate({ backgroundColor: "#fff" }, 500);
				$('span#cartCount').html(data.Order.order_item_count);
				if(data.Order.total === 0) {
					window.location.replace(webroot + "shop/clear");
				}
			},
			error: function() {
//        alert('this is where we used to delete the entire cart. You should be happy.');
//				window.location.replace(webroot + "shop/clear");
			}
		});
	}
});
