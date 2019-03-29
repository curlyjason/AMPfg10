//establish variables to name seconds, minutes and hours from the millisecond math
var seconds = 1000;
var minutes = 60 * seconds;
var hours = 60 * minutes;

//$(document).idleTimer(2 * seconds);

function setIdleTimer() {
//    $('#timeWarningNo').unbind('click');
//    $('#timeWarningNo').bind('click', timeout);
//    
//    $('#timeWarningYes').idleTimer('destroy');
//    $('#timeWarningYes').unbind('idle.idleTimer');
//    
//    $('.timeWarning').css('display', 'none');
//    $('#defeat').attr('class', 'hide');
    
    $(document).idleTimer('destroy');
    $(document).idleTimer(idleLimit * seconds);
    $(document).bind('idle.idleTimer', timeout);
}

function countdown() {
    $(document).idleTimer('destroy');
    $(document).unbind('idle.idleTimer');
//    var t = setTimeout(timeout, warningLimit);
    $('#timeWarningYes').idleTimer(warningLimit * seconds);
//    $('#timeWarningYes').bind('idle.idleTimer', timeout).bind('click',setIdleTimer);
//    $('#defeat').attr('class', 'show');
//    $('.timeWarning').css('display', 'block').center();
}

function timeout() {
    $(document).idleTimer('destroy');
    $(document).unbind('idle.idleTimer');
    window.location.assign(webroot + 'users/timeout');
}
$(document).ready(function() {
    setIdleTimer();
})
