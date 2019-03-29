function setCatalogRoot(e) {
    e.preventDefault();
    var parent_id = $(e.currentTarget).attr('href').split("/").pop();
    var catalog_name = $(e.currentTarget).html();
    $('input#catalogParentId').val(parent_id);
    $('span.company_name').html("Destination Catalog: " + catalog_name);
}

function validateSubmit(e){
    if($('input#catalogParentId').val() == 'unset'){
        e.preventDefault();
        alert('You need to pick a valid catalog from the list on the left first.')
    }
}

$(document).ready(function(){
    $('div.treeSelect a').off('click').on('click', setCatalogRoot);
    $('form#ItemImportsSaveItemsForm div.submit input').on('click', validateSubmit);
});
