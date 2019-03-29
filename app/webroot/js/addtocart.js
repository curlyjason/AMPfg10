$(document).ready(function() {
	
	$('a.reveal').on('click', function(e) {
		e.preventDefault();
	});

	
	/* ****************************************************** 
	 * These are shopping page things
	 ****************************************************** */
	
	$('#jumpTo').on('change', function(){
		var page = parseInt($(this).val())+1;
		var baseUrl = location.href.replace(/\/page:(\d)+/,'');
		location.assign(baseUrl+'/page:'+page);
	});
	
	$('#paginationLimit').on('change', function(){
		$.get(webroot + controller + 'paginationLimitPreference/' + $(this).val() + getNow(),'d', function(data, textStatus, xh){
			var loc = location.href.replace(/\/(.+page)[:0-9]+(.*)/,/$1:1$2/);
			location.assign(loc);
		});
	})

});

    function addToCart(e) {
		var target = $(e.currentTarget);
		$(e.currentTarget).css('font-weight', 'bold').css('color', 'green').html('Adding...');

        $.ajax({
            type: "POST",
            url: webroot + "shop/itemupdate",
            data: {
                id: $(e.currentTarget).attr("catalogId"),
                quantity: $(e.currentTarget).parent('form').find('input.cartQuantityInput').val()
            },
            dataType: "json",
            success: function(data) {
                if (typeof(data.error) != 'undefined') {
                    alert(data.error);
                    return;
                }

                $('#cartCount').html(data.Order.order_item_count);
                updateBudget(data);

                $('#msg').html('<div class="alert alert-success flash-msg cart-msg">Product Added to Shopping Cart</div>');
                $('#msg').center();
                $('#cartbutton').css('display', 'inline-block');
                $('.flash-msg').delay(2000).fadeOut('slow');

                refreshAvailable(data.Available, '.avail');
				$(target).css('font-weight', 'normal').css('color', '#333333').html('Add to Cart');

            },
            error: function() {
                alert('No specific error, but something went wrong adding that item.')
				$(target).css('font-weight', 'normal').css('color', '#333333').html('Add to Cart');
            }
        });

	return false;
	};