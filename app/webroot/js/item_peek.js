$('a[href*="item_peek"]').on('click', function(e){
    e.preventDefault();
    $(this).after().load($(this).attr('href'));
})