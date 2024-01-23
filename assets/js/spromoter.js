document.addEventListener('DOMContentLoaded', function () {
    // Create & load widget js
    let widget = document.createElement('script');
    widget.type = 'text/javascript';
    widget.async = true;
    widget.src = `//staticwp.spromoter.test/${spromoterSettings.app_id}/widget.js`;
    document.body.appendChild(widget);

    // Review Lightbox
    lightbox.option({
        'showImageNumberLabel': false,
        'positionFromTop': 70
    })
});