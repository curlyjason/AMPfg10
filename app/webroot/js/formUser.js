//=========================
// formUser.js
//=========================

//============================================================
// VALIDATE INPUTS BASED UPON FOLDER CHOICE
//============================================================

/*
 * Do appropriate user form input validation
 * 
 * Validate the username and role based on folder-value
 * 
 * @returns boolean
 */
function validateUserFormInputs() {
    var messageA = enforceUsernameContent();
    var messageB = enforceRoleContent();
    var divider = (messageA != '' && messageB != '') ? '\r\n' : '';
    if (messageA != '' || messageB != '') {
        alert(messageA + divider + messageB);
        return false;
    } else {
        return true;
    }
}

/*
 * Enforce username given folder input setting
 * 
 * @returns {undefined}
 */
function enforceUsernameContent() {
    if ($('#UserFolder').prop('checked')) {
        return '';
    } else {
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        if (!emailReg.test($('#UserUsername').val())) {
            return 'A username must be a valid email address.\n\reg: you@domain.com';
        } else {
            return '';
        }
    }
}

/*
 * Enforce user role given folder input setting
 * 
 * @returns {undefined}
 */
function enforceRoleContent() {
    if ($('#UserFolder').prop('checked')) {
        if ($('#UserRole').val() == '') {
            return 'When making a User Folder, you must also set a Role.';
        } else {
            return '';
        }
    }
    return '';
}

//============================================================
// VALIDATE INPUTS BASED UPON FOLDER CHOICE
//============================================================

/*
 * Insure both selections are made for Observer assignments
 * 
 * Valid records require a user and a type.
 * Users may be observed or observer
 * 
 * @returns boolean
 */
function validateObserverFormInputs() {
    var observer = $('select[id*="ObserverUser"]'); // get the proper id input
    var messageA = $(observer).val() == '' ? 'You must select an User' : '';
    var messageB = $('#ObserverType').val() == '' ? 'You must select an Type' : '';
    var divider = (messageA != '' && messageB != '') ? '\r\n\r\n' : '';
    if (messageA != '' || messageB != '') {
        alert(messageA + divider + messageB);
        return false;
    } else {
        return true;
    }
}

//============================================================
// INIT ROUTINES SPECIFIC TO USER TREE FORM EDITING
//============================================================

/**
 * Bind actions specific to user tree form editing
 * 
 * Bind collapsing sections, buttons, and keyboard shortcuts
 * This is the primary user edit tree form init-er
 * this is called by the primary common edit tree from init-er
 * 
 * @returns {undefined}
 */
function initTreeFormVariant() {
    // set up the User/Catalog toggling to display selection checkboxes
    $('div.permissions > label').bind('click', function() {
        toggleThis($(this).parent().find('div.checkbox'));
    });

    // setup the Advanced field toggling
    $('p.advanced').bind('click', function() {
        toggleThis('div.advanced');
    });

    initUserFolderInput();
    initTreeFormKeydown();
    initUserFormDisplay();
}

/**
 * Set up User form 'folder' switch for username/role input
 * 
 * When a User record is set as a Folder
 *	    non-email usernames are allowed
 *	    role is required
 * When not a folder
 *	    usernames must be email address
 *	    role is optional
 * 
 * @returns {undefined}
 */
function initUserFolderInput() {
    $('#UserFolder').on('change', function() {
        if ($(this).prop('checked')) {
            $('label[ for="UserUsername"]').html('Name');
            $('label[ for="UserRole"]').parent().addClass('required');
            $('#UserFirstName').parent('div').css('display','none');
            $('#UserLastName').parent('div').css('display','none');
			$('div.nonFolder').css('display', 'none');
        } else {
            $('label[ for="UserUsername"]').html('Email');
            $('label[ for="UserRole"]').parent().removeClass('required');
            $('#UserFirstName').parent('div').css('display','block');
            $('#UserLastName').parent('div').css('display','block');
			$('div.nonFolder').css('display', 'block');
        }
    });
}

/**
 * Bind keydown functions separately for clean code
 * 
 * This could easily be in the initTreeFormVariant, above
 * but it makes the code look crappy.
 * 
 * @returns {undefined}
 */
function initTreeFormKeydown() {
    $(document).keydown(function(e) {
        if ($('#treeEditForm fieldset').html() != '') {
            e.stopImmediatePropagation();
            if (e.ctrlKey) {

                // ctrl-a toggle Advanced
                if (e.which == '65') {
                    toggleThis('div.advanced');
                }

                // ctrl-c toggle Catalog permissions
                if (e.which == '67') {
                    toggleThis('div.catalog div.checkbox');
                }

                // ctrl-u toggle User permissions
                if (e.which == '85') {
                    toggleThis('div.user div.checkbox');
                }

                // ctrl-return Submit and make another
                if (e.which == '13') {
                    $('#treeEditForm button#again').trigger('click');
                }
            }

            // escape Cancel the form
            if (e.which == '27') {
                $('#treeEditForm button#cancel').trigger('click');
            }
        }
    })
}

function initUserFormDisplay() {
    $('#UserFolder').trigger('change');
}

//============================================================
// INIT ROUTINES SPECIFIC TO USER GRAIN FORMS
//============================================================

/**
 * Init the newly loaded User grain edit form
 * 
 * wrapper is a div containing the newly loaded form
 * it has an id of user_id/hash
 * 
 * @param object wrapper
 * @returns {undefined}
 */
function initUserGrainForm(wrapper) {
    // make the submit button validate first
    bindHandlers(); //bindBasicCancelButton\(\$(wrapper).find('.cancelButton'));
    initUserFolderInput();
    $('button[type="submit"]').on('click', function() {
        if (!validateUserFormInputs()) {
            return false;
        }
    });
    $('#UserFirstName').select();
}

/**
 * Setup Observer Grain Form for edits
 * 
 * Create binding for Cancel button and setup client-side validation
 * for required fields.
 * 
 * @param {object} wrapper
 * @returns {undefined}
 */
function initObserverGrainForm(wrapper) {
    // The Cancel button in the loaded form
    bindHandlers(); //bindBasicCancelButton\(\$(wrapper).find('.cancelButton'));
    $('button[type="submit"]').on('click', function() {
        if (!validateObserverFormInputs()) {
            return false;
        }
    });
}

/**
 * Setup Address Grain Form for edits
 * 
 * Create binding for Cancel button and set focus to AddressName
 * 
 * @param {object} wrapper
 * @returns {undefined}
 */
function initAddressGrainForm(wrapper) {
    // The Cancel button in the loaded form
    bindHandlers(); //bindBasicCancelButton\(\$(wrapper).find('.cancelButton'));
    $('#AddressName').select();
}

/**
 * Reset selected user's password
 */
function reset_password() {
    var userName = $('#UserUsername').val();
    var postData = {'User':{'username':userName}};
    $.ajax({
        type: "POST",
        dataType: "JSON",
        data: postData,
        url: webroot + 'users/forgotPassword',
        success: function (data) {
            $('.view').prepend(data.flash);
            basicCancelButton();
        },
        error: function (data) {

        }
    })
}

/**
 * Reset selected customer's token
 */
function updateToken() {
    var CustomerId = $('#CustomerId').val();
    var postData = {'Customer':{'id':CustomerId}};
    $.ajax({
        type: "POST",
        // dataType: "JSON",
        data: postData,
        url: webroot + 'customers/resetCustomerToken',
        success: function (data) {
            $('.view').prepend(data.flash);
            basicCancelButton();
        },
        error: function (data) {
            basicCancelButton();
        }
    })
}

/**
 * Copy Address info from Customer to Branding Data
 *
 * In the customer edit form, copy the Customer's primary
 * address information into the Branding company and address fields
 */
function fillBrandingAddressValues(e) {
    e.preventDefault();
    // alert('This button is fill Branding Address Values');

    var AddressLastLine = $('#AddressCity').val() + ', ' + $('#AddressState').val() + ' ' + $('#AddressZip').val();

    $('#PreferenceBrandingCompany').val($('#UserUsername').val());
    $('#PreferenceBrandingAddress1').val($('#AddressAddress').val());
    $('#PreferenceBrandingAddress2').val($('#AddressAddress2').val());
    $('#PreferenceBrandingAddress3').val(AddressLastLine);
}

function updateAddressName() {
    var AddressName = $('#UserUsername').val();
    $('#AddressName').val(AddressName);
    $('#AddressCompany').val(AddressName);
}


$(document).ready(function() {
})
