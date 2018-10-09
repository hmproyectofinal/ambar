
function openPopup(url){
    var newWindow = window.open(url, 'cron', 'height=700, width=1000');
    if(window.focus) newWindow.focus();
}