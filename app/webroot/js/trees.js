$(document).ready(function() {

    //============================================================
    // SIDEBAR/SELECT TREE BEHAVIOR
    //============================================================

    function initTree() {
		var firstLevelNodes = $('div > ul > li > input[type="checkbox"]');
		if(firstLevelNodes.length == 1){
		$('div > ul > li > input[type="checkbox"]').prop('checked', true);
	}
        initTreeCheckboxes('sidebar', 'sidebarState');
        initTreeCheckboxes('treeEdit', 'editTreeState');
    }

    function initSidebar() {

        $('div.sidebar li > a').on('click', function(e) {
            if (e.metaKey) {
                e.preventDefault();
                window.open($(this).attr('altLink'), '_self');
            }
        });
		
		// if we're a folder page but the folder doesn't have items
		// the sidebar element won't have a checkbox. It will have 
		// a folder. So we'll have to detect this condition
		// and swap the image out for the folder with a check
		var target = location.pathname.match(/\d+\/.*$/);
		if (target != null) {
			// this is expected to be:
			// <li> <img /> <a> </a>
			// with a as target
			var folder = $('a[href*="'+target+'"]').prev();
			if (typeof($(folder).attr('src')) !== 'undefined') {
				$(folder).attr('src', $(folder).attr('src').replace('folder', 'foldercheck'));
			}
		}
    }

    /**
     * Insuere sidebar exposure of logged in use node
     * 
     * In the case of prefs-tool click of Edit account info,
     * The record's ancestor list becomes a url param, used to
     * guarantee the sidebar will expose the 
     * location of this user's record
     */
    function accountShortcutCheck() {
	if (location.search.match(/side_check/)) {
	    //?ancestors=side_check_1.side_check_5
	    //alert(location.search);
	    var ck = location.search.replace('?ancestors=', '').replace('.', ',');
	    openTreeForm(ck);
	}
    }

    /**
     * Initialize the sidebar tools
     * 
     * @returns {undefined}
     */
    initTree();
    initSidebar();
    accountShortcutCheck();
})

function writeTreeStates() {
    writeCheckboxState('treeSelect', 'sidebarState');
    writeCheckboxState('treeEdit', 'editTreeState');
}

function writeCheckboxState(divClass, cookieName) {
    var IDs = [];
    $('.' + divClass + ' input:checked').each(function() {
        IDs.push(this.id);
    })
    if (location.pathname.search(/edit_user/) != -1) {
        var controller = 'users/';
        var cookieSet = ['edit_user/', 'edit_userGrain/'];
    } else if (location.pathname.search(/edit_catalog/) != -1) {
        var controller = 'users/';
        var cookieSet = ['edit_catalog/', 'edit_catalogGrain/'];
    } else if (location.pathname.search('/shopping') != -1) {
        var controller = 'catalogs/';
        var cookieSet = ['shopping/'];
    }

    if (cookieSet) {

        for (i = 0; i < cookieSet.length; i++) {
            var action = cookieSet[i];
            var treeState = cookieName + " =" + IDs + ";path=" + webroot + controller + action;
            document.cookie = treeState;

        }
    }
}

$(window).on('unload', function() {
    writeTreeStates();
})

function initTreeCheckboxes(divClass, cookieName) {
    $('div.' + divClass + ' li > input[type="checkbox"]').bind('click', function(e) {
        if ($(this).is('input:checked') && e.metaKey) {
            cascadeOpen($(this));
        } else if (e.metaKey) {
            cascadeClosed($(this));
        }
    });

    var ck = ReadCookie(cookieName);
    if (ck != null) {
        openTreeForm(ck);
    }
}

/**
 * Given a list 'side_check_xx,side_check_yy' check the boxes
 * 
 * This will open the checked items in the sidebar
 */
function openTreeForm(ck) {
    var checkList = ck.split(/,\s*/);
    for (var i = 0; i < checkList.length; i++) {
	$('#' + checkList[i]).prop('checked', true);
    }
}

/**
 * Check true (open) all decendent checkboxes
 * 
 * @param {type} box
 * @returns {undefined}
 */
function cascadeOpen(box) {
    $(box).parent().find('input[type="checkbox"]').prop('checked', true);
}

/**
 * Check false (close) all decendent checkboxes
 * 
 * @param {type} box
 * @returns {undefined}
 */
function cascadeClosed(box) {
    $(box).parent().find('input[type="checkbox"]').prop('checked', false);
}

/**
 * 
 * @returns {undefined}
 */
function newCustomer(){
	$('div.view').load(webroot+'customers/add', function(){
		bindHandlers();
		initToggles();
	});
}
