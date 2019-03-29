$(document).ready(function() {
//    initPullToggles();
    $("td.cartItem:first-of-type").each(function(){
		moveRowId($(this));
	});

});

function pullCheckboxClick(){ // also update the label on the onHand input
//    $('input[id*="pull-"]').off('click').on('click', function(e){
        
        if($(this).attr('class').match(/Replenishment/) == 'Replenishment'){
            mode = 'replenishment';
            if($(this).prop("checked")){
                var pullMultiplier = 1;
            } else {
                var pullMultiplier = -1;
            }
        } else {
            mode = 'pull'
            if($(this).prop("checked")){
                var pullMultiplier = -1;
            } else {
                var pullMultiplier = 1;
            }
        }
        pullData = new Object();
		pullData.pullMultiplier = pullMultiplier;
        pullData.pullQty = ($(this).attr('pullquantity'))*pullData.pullMultiplier;
        pullData.itemId = $(this).attr('item-id');
        pullData.rowId = $(this).attr('id').replace('pull-', '');
        pullData.onHand = $('input[id*="onHand-'+pullData.rowId+'"]').val();
        pullData.mode = mode;
        updateInventory(pullData);
//    });
}

function updateInventory(pullData) {

    $.ajax({
        type: "POST",
        url: webroot + "items/updateInventory/",
        data: pullData,
        dataType: "json",
        success: function(data) {
			if (!data.item) {
				// untested method of returning check to original val if save failed
				var el = $('#pull-'+pullData.rowId);
				$(el).prop('checked', !$(el).prop('checked'));
				alert('That change was not recorded. Save failed.')
			} else {
				var id = data.item.Item.id;
				var qty = data.item.Item.quantity;

				//update quantity on page
				var changed = $('input[itemId="'+id+'"], span[itemId="'+id+'"]');
				changed.val(qty).attr('origValue', qty);
				$('span[qtyItemId="'+id+'"]').html(qty);

				//update trailing label on Qty
				var label = $(data.rowId).find('input[id*="pull-"]').prop('checked') ? 'after' : 'before';
				$(data.rowId).siblings().find('.onHandLabel').html(label);

				//flash color
				changed.parents('tr').each(function(){
					var color = $(this).css('backgroundColor');
					$(this)
						.animate({ backgroundColor: '#ff0' }, 200)
						.animate({ backgroundColor: color }, 1000);
				});
			}
        },
        error: function(data) {
            alert("updateInventory FAILURE!!");
        }
    });
}

function adjustOnHand(e){ // needs ajax call then update of all of same itemID
	var rowId = $(this).attr('id').replace('onHand-', '#row-')
	var pullData = {
		rowId : rowId,
		Item : {
			id : $(this).attr('itemId'),
			quantity : $(this).val(),
			orig_value : $(this).attr('origValue'),
			name : $(rowId).find('span.itemName').html()
		}
	}
	//	
	$.ajax({
		type: "POST",
		url: webroot + "items/adjustOnHand/",
		data: pullData,
		dataType: "json",
		success: function(data) {
			// check the json error message for actual success
			if (typeof(data.Item.error) === 'undefined') {
				
				// All is well, change the necessary values on the page
				var changed = $('input[itemId="'+data.Item.id+'"], span[itemId="'+data.Item.id+'"]');
				changed.val(data.Item.quantity).attr('origValue', data.Item.quantity);
				$('span[itemId="'+data.Item.id+'"]').html(data.Item.quantity);
				
				// and highlight all the rows that changed
				changed.parents('tr').each(function(){
					var color = $(this).css('backgroundColor');
					$(this)
						.animate({ backgroundColor: '#ff0' }, 200)
						.animate({ backgroundColor: color }, 1000);
				})
				refreshAvailable(data.Item.Pending, '.pend');
				refreshAvailable(data.Item.Available, '.avail');
			// Well, there was an error saving. Let the user know and restore	
			} else {
				alert(data.Item.error);
				$('input[itemId="'+data.Item.id+'"]').val(data.Item.orig_value);
			}
		},
		error: function(data) {
			alert('There was a problem with that call to the server. Your change was not made.')
			$('input[itemId="'+data.Item.id+'"]').val(data.Item.orig_value);
		}
	});

}

/**
 * Comment
 */
function kitTool(e) {
	e.preventDefault();
	$('.' + $(e.currentTarget).attr('id')).toggle(50, function() {
		// animation complete.
	});

}

/**
 * Save the inventory change for kit actions and update the DOM
 * 
 */
function kitInventoryUpdate(e) {
	e.preventDefault();
	var orderItemId = $(e.currentTarget).parents('tr').attr('id').replace('row-', '');
	var changeQty = $('#kit_quantity-' + orderItemId).val();
	var catalogId = $('#kit_cat_id-' + orderItemId).val();
	var catalogType = $('#kit_cat_type-' + orderItemId).val();
    $.ajax({
        type: "GET",
        url: webroot + "catalogs/kitAdjustment/" + catalogId + '/' + changeQty + '/' + catalogType,
        dataType: "json",
		async: false,
        success: function(data) {
			$('div.debug').html(data.message);
			bindHandlers();
			initToggles();
			if(data.available){
				refreshAvailable(data.Available, '.avail');
			}
        },
        error: function(data) {
            alert("There was a problem with the kitInventoryUpdate java call");
        }
    });
	
}