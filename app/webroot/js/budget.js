
function updateBudget(data){
    if (typeof(data.Budget) != 'undefined') {
        //call budget status updater
        if ($('.budgetstatus-' + data.Budget.id).length > 0) {
            updateBudgetStatus(data);
        }
        
        //call budget indicator updater
        updateBudgetIndicators(data);
    }
}

function updateBudgetStatus(data) {
    $('#budget > p > span.text').html(data.Budget.remaining_budget);
    if (data.Budget.remaining_budget != null && data.Budget.remaining_budget.match('-')) {
        $('#budget').addClass('negative');
        $('#budget > p > span.decoration').html('Over Budget');
    } else {
        $('#budget').removeClass('negative');
        $('#budget > p > span.decoration').html('Budget');
    }
    $('#item_budget > p > span.text').html(data.Budget.remaining_item_budget);
    if (data.Budget.remaining_item_budget != null && data.Budget.remaining_item_budget.match('-')) {
        $('#item_budget').addClass('negative');
        $('#item_budget > p > span.decoration').html('Over Item Budget');
    } else {
        $('#item_budget').removeClass('negative');
        $('#item_budget > p > span.decoration').html('Item Budget');
    }
}

function updateBudgetIndicators(data){
    if (data.Budget.remaining_budget != null && data.Budget.remaining_budget.match('-')) {
        var indicatorClass = 'negative';
    } else {
        var indicatorClass = 'positive';
    }
    $('span.budget-'+data.Budget.id).children('span').html(data.Budget.remaining_budget);
    $('span.budget-'+data.Budget.id).removeClass('negative positive').addClass(indicatorClass);
    if (data.Budget.remaining_item_budget != null && data.Budget.remaining_item_budget.match('-')) {
        var indicatorClass = 'negative';
    } else {
        var indicatorClass = 'positive';
    }
    $('span.itembudget-'+data.Budget.id).children('span').html(data.Budget.remaining_item_budget);
    $('span.itembudget-'+data.Budget.id).removeClass('negative positive').addClass(indicatorClass);
}

$(document).ready(function() {

    function initBudgetIndicators() {
        var indicators = $('span.indicator');
        if (indicators.length == 0) {
            return;
        }
        indicators.hover(function() {
            $(this).children('span').removeClass('hide')
        }, function() {
            $(this).children('span').addClass('hide')
        })
    }

    initBudgetIndicators();
})