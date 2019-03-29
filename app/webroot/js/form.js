//=========================
// form.js
//=========================

//============================================================
// POST TREE EDIT HANDLING
//============================================================

/**
 * Post the tree to the specified action
 * 
 * If the drag/drop didn't change anything, just leave.
 * Otherwise, post the data.
 * On return, display the new tree data and
 * reset the hidden form to its default state
 * 
 * @param {string} action
 * @returns {unresolved}
 */
function postTreeEdit(action) {
    //verify there was any change made before proceeding
    if (changeValidation()) {
		coverSidebar();
        writeCheckboxState('treeEdit', 'editTreeState');
        $.post(webroot + controller + action, $('#treeEditForm').serialize())
			.done(function(response) {
				resetTreeForm(response);
				$('#ajaxStart').css('display', 'none');
        })
    }
}

/**
 * Determine if any changes were made to the edit form
 * 
 * Check if the basic form fields are set to 'invalid'
 * Which we hand set before editing to indicate no change
 * 
 * @returns {Boolean}
 */
function changeValidation() {
    if ($('#TreeEditId').val() == 'invalid' &&
            $('#TreeEditSequence').val() == 'invalid' &&
            $('#TreeEditParent').val() == 'invalid') {
        return false;
    } else {
        return true;
    }
}

/**
 * Reset Tree edit form to beginning values
 * 
 * Set all standard tree edit form fields to 'invalid'
 * to indicate the no change state
 * 
 * @param {object} response
 * @returns {undefined}
 */
function resetTreeForm(response) {
    // reset all form elements to 'invalid'
    $('div.treeEdit').html(response);
    $('#TreeEditId').val('invalid');
    $('#TreeEditSequence').val('invalid');
    $('#TreeEditParent').val('invalid');
	$('#TreeEditParentKit').val('');
//    $('#ajaxStart').attr('class', 'hide');
    initTreeEdit();
    initTreeCheckboxes('treeEdit', 'editTreeState');
	bindHandlers();
	
	//cover sidebar
	coverSidebar();
}

/**
 * Position a previously hidden div to cover the sidebar
 * 
 * This ghosted div has a refresh message in it
 * and is visible when tree edits have occured.
 * Those edits are likely to make the sidebar invalid
 */
function coverSidebar() {
	var sidebarHeight = $('div.sidebar').outerHeight(true);
	var sidebarWidth = $('div.sidebar').outerWidth(true);
	$('div.overlay').removeClass('hide').css('width', sidebarWidth).css('height', sidebarHeight);
}

/**
 * When the covered sidebar is clicked, reload the page
 */
function initSidebarRefresh(){
	$('div.overlay').on('click',function(){
		location.reload();
	})
}

/**
 * Initialize the tools in a newly loaded tree edit form
 * 
 * @returns {undefined}
 */
function initTreeForm(action) {
    //init common elements for all editing forms
    initToggles();
    $('#treeEditForm button#cancel').unbind('click').bind('click', function(e) {
        removeForm();
    });

    $('#treeEditForm button#again').unbind('click').bind('click', function(e) {
        serialEntry();
    });

    // setup the new form under ajaxForm guidelines
    // using the options variable to control its process
     
    var options = {
        target: 'div.treeEdit',
        beforeSubmit: ajaxForm_beforeSubmit,
        success: resetTreeForm,
        url: webroot + controller + action
    }
    $('#treeEditForm').ajaxForm(options);
    
    //do special catalog or user form initialization, based upon loaded js file
    //if there's a problem here, see if formUser.js AND formCatalog.js are loaded together
    //because, the system only works if only one of them is loaded

    initTreeFormVariant();
}

/**
 * Bind basic cancel functionality to provided button
 * 
 * Bind both the 'reload page on cancel' action, and bind the
 * ESC key to the provided button
 */
function basicCancelButton(e) {
    // The Cancel button in the loaded form
//    $(button).off('click').on('click', function() {
        window.open(location.href, '_self');
//    });
}

function hrefCancelButton(e) {
    // The Cancel button in the loaded form
//    $(button).off('click').on('click', function() {
        window.open($(e.currentTarget).attr('href'), '_self');
//    });
}


//============================================================
// VALIDATE INPUTS
//============================================================

/*
 * Choose the validation to run
 * 
 * @returns boolean
 */
function validateFormInputs() {
    if (changeValidation()) {
        if (controller == 'users/') {
            return validateUserFormInputs();
        } else if (controller == 'catalogs/') {
            return validateCatalogFormInputs();
        }
        return true;
    } else {
        return false;
    }
}

function ajaxForm_beforeSubmit() {
    validateFormInputs();
    writeCheckboxState('treeEdit', 'editTreeState');
}

$(document).ready(function() {
	initSidebarRefresh();
})