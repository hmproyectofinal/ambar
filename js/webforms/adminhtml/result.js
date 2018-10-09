function Admin_JsWebFormsResultModal(title, url) {
    var win = new Window({
        className: 'magento',
        title: title,
        url: url,
        width: 820,
        height: 473,
        zIndex: 10000
    });
    win.showCenter(true);
}