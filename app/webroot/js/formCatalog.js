//=========================
// formCatalog.js
//=========================

//============================================================
// INIT ROUTINES SPECIFIC TO CATALOG TREE FORM EDITING
//============================================================

/**
 * Bind actions specific to catalog tree form editing
 * 
 * Bind collapsing sections, buttons, and keyboard shortcuts
 * This is the primary catalog edit tree form init-er
 * this is called by the primary common edit tree from init-er
 * 
 * @returns {undefined}
 */
function initTreeFormVariant() {
	
    initItemSourceRadioButton();
    initCatalogFormDisplay();
//	initKitPrefs();
//    initCatalogNameUpdate();
	bindHandlers();
	initHelp($('#treeEditForm'));
	$('input[id*="CatalogType"]').filter('input[checked*="checked"]').trigger('change');
}

/**
 * Initialize the Item Source Radio Button
 * 
 * Initializes the item source radio button to manage section
 * and input displays
 * 
 * @returns {undefined}
 */
function initItemSourceRadioButton() {
    $('.input.radio > input[id*="ItemSource"]').on('change', function() {
        if ($(this).val() == 0) {
            //new item chosen
            $('#CatalogItemId').parent('div').css('display', 'none');
			$('#CatalogItemId').val('');
			$('#ItemId').val('');
			$('#ajaxEditImage').attr('src', webroot + 'img/image/img_file/no/x160y120_image.jpg');
        } else {
            $('#CatalogItemId').parent().css('display', 'block');
			pullItemSourceList();
        }
    })
}

function pullItemSourceList(){
	var secureCatalogId = $('#TreeEditParent').val();
	$.ajax({
		type: "GET",
		url: webroot + "catalogs/fetchCutomerItemList/" + secureCatalogId,
		dataType: "html",
		success: function(data) {
			if (data=='FALSE'){
				alert('No list was returned, please try again.');
			} else {
				$('#CatalogItemId').replaceWith(data);
				bindHandlers();
			}
		},
		error: function() {
			alert('There was a javascript error fetching that list, please try again.')
		}
	});

}


/**
 * Control visiblility of secondary input based on kit inventory strategy
 * 
 * If both kits and components are inventoried, it is assumed both can be ordered
 * If only one or the other are inventoried, components may or may not be ordereable
 * This shows/hides the checkbox that allows the 'orderable' choice
 * 
 * @returns {undefined}
 */
function initKitPrefs() {
	$('input[id*="CatalogKitPrefs"]').on('change', function(e){
		if ($(this).val() === "128") {
			$('#CanOrderComponents').addClass('hide');
		} else {
			$('#CanOrderComponents').removeClass('hide');
		}
	});
}

/**
 * Initialize the Item Source Radio Button
 * 
 * Initializes the item source radio button to manage section
 * and input displays
 * 
 * @returns {undefined}
 */
function catalogTypeRadio(e) {
	var choice = $(e.currentTarget).val();
	if (choice == 2) {
		//it's a folder
		$('div.nonFolder').css('display', 'none');
	} else if(choice == 1) {
		//it's a kit
		$('div.kitBlock').css('display', 'block');
		$('div.nonFolder').css('display', 'block');
		$('#CatalogSellUnit').val('ea');
		$('#CatalogSellQuantity').val('1');
	} else if(choice == 4) {
		//it's a product
		$('div.kitBlock').css('display', 'none');
		$('div.nonFolder').css('display', 'block');
	}
}

/**
 * If this is a kit, keep sell units as 'each'
 * 
 * @param {type} e
 */
function sellUnitControl(e) {
	var type = $('input[id*="CatalogType"]:checked').val();
	if (type == 1) {
		$('#CatalogSellUnit').val('ea');
		alert('Kits can only be ordered as 1 each');
	}
}

/**
 * If this is a kit, keep sell quantity as 1
 * 
 * @param {type} e
 */
function sellQtyControl(e) {
	var type = $('input[id*="CatalogType"]:checked').val();
	if (type == 1) {
		$('#CatalogSellQuantity').val('1');
		alert('Kits can only be ordered as 1 each');
	}
}
/*
 * On setup, js fires a change event to ensure the form is displaying properly
 * 
 * When the form is first created, it contains all the fields and label for both
 * add a new item and connect to an exisiting item. This triggers the
 * initItemSourceRadioButton method to update the display appropriately
 * @returns {undefined}
 */
function initCatalogFormDisplay() {
    $('#ItemSource0').trigger('change');
}

//============================================================
// VALIDATE INPUTS
//============================================================

/*
 * Do appropriate catalog form input validation
 * 
 * Catalogs have no validation at this time 
 * 
 * @returns boolean
 */
function validateCatalogFormInputs() {
    return true;
}

/**
 * Update the form with new data when a different item is chosen
 * 
 * @param {object} e
 */
function existingItemChange(e){
	var itemId = $('#CatalogItemId').val();
	$.ajax({
		type: "GET",
		url: webroot + "items/fetchJsonItem/" + itemId,
		dataType: "json",
		success: function(data) {
			if (typeof(data.error) != 'undefined') {
				alert(data.error);
				return;
			}

			for(var index in data){
				if (index == 'ajaxEditImage') {
					$('#'+index).attr('src', webroot + 'img/image/img_file/' + data[index]['dir'] + '/x160y120_' + data[index]['name']);
				} else {
					$('#'+index).val(data[index]);
				}
				
			}

		},
		error: function() {
			alert('No specific error, but something went wrong connecting to that item.')
		}
	});


}

$(document).ready(function() {
})