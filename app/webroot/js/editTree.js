$(document).ajaxStart(function() {
    if (ajaxMessage == '') {
        ajaxMessage = "Processing your request. Please stand by...";
    }
    $('#container').append('<div class="start" id="ajaxStart">' + ajaxMessage + '</div>');
    $('#ajaxStart').center();
});

$(document).ajaxStop(function() {
    $('#ajaxStart').remove();
    ajaxMessage = '';
})

var ajaxMessage = '';

var liveNode = new Object();

/**
 * Reveal the tool pallet properly contextualized
 * 
 * Clear pallet form possible current display condition
 * Contextualize
 * Position and reveal
 * 
 * @param {type} e The event object
 * @param {type} img The element that triggered the reveal
 * @returns {unresolved}
 */
function revealToolPallet() {
	validateAccess($(this).parent('li').attr('id'));
    clearTreeEditToolPallet();
    $('#TreeEditId').val($(this).parent('li').attr('id'));
	var type = $(this).parent('li').attr('class').replace('li-', '');
	$('#treeEditTools span').html(type);
	var li = liveNode.li = $(this).parent(); //assumes the triggering img is a direct child of the LI

    // Tool filters go here
    //Top most item cannot be made a child, user cannot see the new parent
    if (li.prev().length == 0) {
        $('a.edit_toChild').addClass('noTool');
    }
    //User's root node, cannot be made a child, cannot have siblings, cannot be deleted
    if ($(li).find('img[class*="root folder"]').length > 0) {
        $('a.edit_toChild').addClass('noTool');
        $('a.edit_newSibling').addClass('noTool');
        $('a.edit_deleteItem').addClass('noTool');
    }
    //any node, if prev is item, cannot be made child
    if ($(li).prev().children('img[class*="item"]').length > 0) {
        $('a.edit_toChild').addClass('noTool');
    }
    //item node, cannot make a child node
    if ($(li).children('img[class*="item"]').length > 0) {
        $('a.edit_newChild').addClass('noTool');
    }
	//customer node cannot get a sibling
	if ($(li).attr('customer')) {
		$('a.edit_newSibling').addClass('noTool');
	}

    // bring the LI text up into the div
    $(this).next('span').clone().appendTo('#treeEditTools > p.label');
    var position = $(this).position();
    var top = position.top;
    var left = position.left;
    
    // reveal the tool pallet at the cursor location
    $('#treeEditTools').css('top', top - 25).css('left', left + 5).css('display', 'block');

}

/**
 * Restore edit-tree tool pallet to its default state
 * 
 * Pallet hidden
 * All tools potentially visible
 * No Pallet title
 * 
 * @returns {undefined}
 */
function clearTreeEditToolPallet() {
    $('#treeEditTools').css('display', 'none');
    // remove tool filters
    $('#treeEditTools > a').removeClass('noTool');
    // remove the tool pallets contextual label
    $('#treeEditTools > p.label').html('');
}

/**
 * Create the drag/drop sorting behavior for the edit tree
 * 
 * Write Id, Sequence, Parent data for each update
 * Post the data for processing on stop
 * 
 * @returns {undefined}
 */
function initSort() {
	if (location.href.match(/catalog/)) {
		$("ul.sort")
				.sortable({
			connectWith: "ul.sort",
			placeholder: "placehold",
			update: function(event, ui) {
				$('#TreeEditId').val(ui.item.attr("id"));
				$('#TreeEditSequence').val(ui.item.prev().attr("sequence"));
				$('#TreeEditParent').val(ui.item.parent().attr("id"));
				$('#TreeEditTypeContext').val(ui.item.parent().parent().children("img.kit").length);
			},
			stop: function(event, ui) {
				postTreeEdit('edit_tree');
			},
			items: 'li.li-folder, li.li-product, li.li-kit'
		})            
				.disableSelection();
		
		$("li.li-kit > ul")
				.sortable({
			connectWith: "li.li-kit > ul",
			placeholder: "placehold",
			update: function(event, ui) {
				$('#TreeEditId').val(ui.item.attr("id"));
				$('#TreeEditSequence').val(ui.item.prev().attr("sequence"));
				$('#TreeEditParent').val(ui.item.parent().attr("id"));
				$('#TreeEditTypeContext').val(ui.item.parent().parent().children("img.kit").length);
			},
			stop: function(event, ui) {
				postTreeEdit('edit_tree');
			},
			items: 'li.li-component'
		})            
				.disableSelection();
	} else {
		$("ul.sort")
				.sortable({connectWith: "ul.sort"})
				.sortable({placeholder: "placehold"})
				.sortable({update: function(event, ui) {
						$('#TreeEditId').val(ui.item.attr("id"));
						$('#TreeEditSequence').val(ui.item.prev().attr("sequence"));
						$('#TreeEditParent').val(ui.item.parent().attr("id"));
						$('#TreeEditParentKit').val(ui.item.parent().parent().children("img.kit").length);
					}
				})
				.sortable({stop: function(event, ui) {
						postTreeEdit('edit_tree');
					}})
				.disableSelection();
	}
}

/**
 * Initialize the Tool pallet links
 * 
 * The class of each link, is the key to their operation.
 * In executeTreeEdit class serves as a switch statement key and as the 
 * name of the action that will eventually process data on the server side.
 * 
 * @todo move the class values to the action attribute. makes more sense there
 * @returns {undefined}
 */
function initTreeEditToolLinks() {
    $('div#treeEditTools a').bind('click', function(e) {
        e.preventDefault();
        executeTreeEdit(this);
    })
}

/*
 * Given a node id (hashed version expected) make its checkbox checked
 * 
 * This is the call that will insure 'demote-to-child' will 
 * go into an open node, and that create-new-child will do the same
 * If the new parent is an empty folder it won't have a checkbox
 * so we'll detect that, make one (hidden) and check it
 * 
 * <input type="checkbox" id="edit_check_22"> // expected element
 * 
 * @param string parentId xxliUUIDkdjfkdjfjd
 * @returns {undefined}
 */
function insureOpenParent(parentId) {
	var bareId = parentId.match(/^\d+/);
	var exists = $('#edit_check_'+bareId).length;
	if (exists < 1) {
		$('#'+parentId).append('<input type="checkbox" id="edit_check_'+bareId+'" class="hide">');
	}
	$('#edit_check_'+bareId).prop('checked', true);
}

/**
 * Handle a tree edit tool pallet choice
 * 
 * Clicked <a> tag class is the controller action that will be called.
 * Each variant sets the three critical tree fields in hidden inputs:
 *	    id of the record to operate on
 *	    parent id of the record
 *	    sequence of the record
 * Next, the data is posted for saving
 * Or, more form inputs are loaded for user input.
 * The form will have a submit button to post for saving
 * 
 * @param <a> element The tool link that was clicked
 * @returns {undefined}
 */
function executeTreeEdit(element) {
	var cookiePath = webroot + controller + action;
    switch ($(element).attr('class')) {

        // demote this sibling to be a child of its previous sibling
        case 'edit_toChild':
			//set variables
            var target = $('#TreeEditId').val();
			var parentId = $('#' + target).prev().attr("id");
			
			//set dom
            $('#TreeEditSequence').val('');
            $('#TreeEditParent').val(parentId);
			
			//insure the parent is open
			insureOpenParent(parentId); //we want the new location to be visible
			
			//execute the move
            postTreeEdit('edit_toChild');
            break;

            // make a new sibling following this element
        case 'edit_newSibling':
			//set variables
            var render = chooseRenderForm('sibling/');
            var target = $('#TreeEditId').val();
            var legend = 'New Sibling for: ' + $('#' + target + ' > span').html();
			var treeEditParentId = $('#' + target).parent().attr("id");
            var sequence = parseInt($('#' + target).attr("sequence"));
            var treeEditTypeContext = setTypeContext(target, treeEditParentId);
//			var parent = $('#TreeEditParent').val().match(/\d*/);

			//set dom
            $('#TreeEditId').val('');
            $('#TreeEditParent').val(treeEditParentId);
			$('#TreeEditTypeContext').val(treeEditTypeContext);
            $('#TreeEditSequence').val(sequence + .5);
			
			//insert the form
            insertAddForm(target, render + treeEditTypeContext, 'edit_newSibling', legend);
            break;

            // make a new child of this element (make it the last child)
        case 'edit_newChild':
			//set variables
            var render = chooseRenderForm('child/');
            var target = $('#TreeEditId').val();
            var legend = 'New Child for: ' + $('#' + target + ' > span').html();
			var parentId = $('#' + target).attr("id");
            var treeEditTypeContext = setTypeContext(target, parentId);
			
			//set dom
			$('#TreeEditTypeContext').val(treeEditTypeContext);
			$('#TreeEditId').val('');
            $('#TreeEditSequence').val('');
            $('#TreeEditParent').val(parentId);
			
			//ensure the parent of this new child is open
			insureOpenParent(parentId); //we want the new location to be visible
			
			//insert the form
            insertAddForm(target, render + treeEditTypeContext, 'edit_newChild', legend);
            break;

            // edit this item
        case 'edit_saveEditForm':
            var target = $('#TreeEditId').val();
            var legend = 'Edit ' + $('#' + target + ' > span').html();
            $('#TreeEditId').val(target);
            $('#TreeEditSequence').val($('#' + target).attr('sequence'));
            $('#TreeEditParent').val($('#' + target).parent().attr("id"));
						
            insertAddForm(target, 'edit_renderEditForm/' + target + getNow()  + ' .ajaxEditPull', 'edit_saveEditForm', legend);
            break;

            // delete this item
        case 'edit_deleteItem':
            var target = $('#TreeEditId').val();
			var r = confirm('Do you want to inactivate this element and all its descendents?');
			if(r != true){
				return;
			}
			document.location.replace(webroot + controller + 'edit_deactivate/' + target);
            break;
			
		case 'list_itemHistory':
			document.location.assign(webroot + 'items/itemHistory/' + $('#TreeEditId').val());
			break;
    }
}

function chooseRenderForm(context) {
    switch (controller) {
        case 'users/':
            return 'add .ajaxPull';
            break;
        case 'catalogs/':
            return 'add_renderEditForm/' + context;
            break;
    }
}

function setTypeContext(target, parentId){
	if (controller == 'catalogs/') {
		var treeEditTypeContext = $('#'+target).attr('type') + '/' + parentId.match(/\d*/);
	} else {
		var treeEditTypeContext = '';
//		var treeEditTypeContext = '/' + parentId.match(/\d*/);
	}
	return treeEditTypeContext;
}

/**
 * Bring a form in for user input
 * 
 * There is always a form with hidden iputs (key tree fields) on the page.
 * When additional inputs are needed, this fetches them 
 * and inserts them to the existing form with a new contextualized legend.
 * The Submit button is then set post data to the proper processing action
 * 
 * @param string target Id/hash of the record that held the clicked tool
 * @param string render The action to render the form inputs [+ selector to cherry-pick them]
 * @param string action The controller action that will process the form data
 * @param string legend The legend for the form (expects a legend in the form)
 * @returns string The form to insert into the DOM below target
 */
function insertAddForm(target, render, action, legend) {
    ajaxMessage = 'Inserting a form for your request. Please stand by...';
    $('#treeEditForm fieldset').load(webroot + controller + render, function(data) {
		if(data.match(/^http:/)){
			document.location.replace(data);
		}
        placeForm($('#treeEditForm'), target);

        // initialize the new form and hide the 'loading' message
        $('#treeFormLegend').html(legend);
		$('#ajaxStart').css('display', 'none');
        initTreeForm(action);
    });
}

/**
 * Expand the bottom padding target, position element over the new space
 * 
 * Since forms can be inside LIs, this is a way to bring the 
 * 'new child/sibling' form into position as though its pushing
 * the list open to show itself in order-context
 * 
 * This could be abstracted for general use and left/right/top/bottom placement
 * 
 * @param obj element The DOM object to position
 * @param obj target The DOM object to fluff up
 * @returns {undefined}
 */
function placeForm(element, target) {
    clearTreeEditToolPallet();
    // before moving, clear any expanded location that may currently hold the form
    $('span[style*="padding-bottom"]').attr('style', '');

    // now get the dimensions and locations we need
    target = $('#' + target + ' > span');
    var elementHeight = element.outerHeight();
    var elementOffset = elementHeight - element.height(); // how much padding an border adds to the size
    var targetPosition = target.position();
    var targetHeight = target.height();

    // make space for the element
    target.css('padding-bottom', elementHeight + 40);
    // move the element and make it visible
    element
            .css('position', 'absolute')
            .css('top', targetPosition.top + elementOffset)
            .css('left', targetPosition.left - elementOffset)
            .css('display', 'block');
}

/**
 * Hide the tree edit form and collapse the space it was in
 * 
 * @returns {undefined}
 */
function removeForm() {
    $('span[style*="padding-bottom"]').attr('style', '');
    $('#treeEditForm fieldset').html('');
    $('#treeEditForm').css('display', 'none');
}

/**
 * Stub for future serial entry function
 * 
 * @returns {undefined}
 */
function serialEntry() {
    alert('Save this record and open another form for the next');
}

/**
 * Setup clicks on control features of edit tree
 * 
 * Assign revealToolPallet to the gear image
 * Assign clearTreeEditToolPallet to the 'X'
 * 
 * @returns {undefined}
 */
function initToolPallets() {
    $('.treeEdit img.gear').on('click', revealToolPallet);
    $('p.close').on('click', clearTreeEditToolPallet);
}

/**
 * Initialize a newly loaded, editable tree
 * 
 * Set up the tool pallet reveal/hide
 * Set up links to the tools in the pallet
 * Make the tree drag/drop sortable
 * Initialize data in the hidden form that supports the tools
 * 
 * @returns {undefined}
 */
function initTreeEdit() {
	$('div > ul > li > input[type="checkbox"]').prop('checked', true);
    initToolPallets();
    initTreeEditToolLinks();
    initSort();
	bindHandlers();
	initToggles();
//    initAddForm();
}

$(document).ready(function() {
    initTreeEdit();
})