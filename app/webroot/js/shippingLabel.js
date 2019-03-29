$(document).ready(function() {
	initEditTable();
})

function newLabel(e) {
	var id = $(e.currentTarget).parents('li').attr('id').replace('li-', '');
	$.ajax({
		type: "GET",
		dataType: "HTML",
		url: webroot+'orders/newLabel/'+id,
		success: function (data) {
			$('#detailLabel').html('').append(data);
			bindHandlers($('#detailLabel'));
			initEditTable();
			$('#LabelSaveNewLabelForm').attr('action', webroot+'labels/saveNewLabel')
		},
		error: function (data) {
			alert('failure');
		}
	})
}

function editLabel(e) {
	var id = $(e.currentTarget).parents('li').attr('id').replace('li-', '');
	$.ajax({
		type: "GET",
		dataType: "HTML",
		url: webroot+'labels/editLabel/'+id,
		success: function (data) {
			$('#detailLabel').html('').append(data);
			bindHandlers($('#detailLabel'));
			initEditTable();
			$('#LabelSaveNewLabelForm').attr('action', webroot+'labels/editLabel')
		},
		error: function (data) {
			alert('failure');
		}
	})
}

function removeLabel(e) {
	var id = $(e.currentTarget).parents('li').attr('id').replace('li-', '');
	$.ajax({
		type: "DELETE",
		dataType: "HTML",
		url: webroot+'labels/removeLable/'+id,
		success: function (data) {
			$('#detailLabel').html('').append(data);
			$('#li-'+id).remove();
		},
		error: function (data) {
			$('#detailLabel').html('').append(data);
		}
	})

}

//function submitLabel(e) {
//	e.preventDefault();
//	if ($('#LabelId').val() == '') {
//		var action = webroot+'labels/saveNewLabel';
//	} else {
//		var action = webroot+'labels/editLabel';
//	}
//	$.ajax({
//		type: "POST",
//		dataType: "HTML",
//		data: $('#LabelSaveNewLabelForm').serialize(),
//		url: action,
//		success: function (data) {
//			location.assign(location.href)
//			$('#detailLabel').html('').append(data);
//		},
//		error: function (data) {
//			alert('failure');
//		}
//	})
//
//}

function include(e) {
	if ($(e.currentTarget).prop('checked')) {
		$(e.currentTarget).parents('tr').removeClass('omit').addClass('include');
	} else {
		$(e.currentTarget).parents('tr').removeClass('include').addClass('omit');
	}
}

function initEditTable() {
	var rows = $('table#editLabel').find('tr');
	
	// tweak the class for the first row which is not an item row
	if (rows.length > 0) {
		$(rows[0]).removeClass('itemRow').removeClass('omit').addClass('labelName');
	}
	
	var i = 1;
	var include;
	
	// set row classes to reflect included/omitted rows
	while (i<rows.length ) {
//		'LabelItems0Include'
		include = $(rows[i]).find('#LabelItems'+(i-1)+'Include').prop('checked');
		if (include) {
			$(rows[i]).removeClass('omit').addClass('include');
		}
		i++;
	}
	
}
function printLabel(e) {
	var id = $(e.currentTarget).parents('li').attr('id').replace('li-', '');
	window.open(webroot+'labels/printLabel/'+id+'.pdf');
}
