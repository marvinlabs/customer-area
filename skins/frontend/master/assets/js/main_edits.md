Edits done from original file
=============================
This file is just a reminder for development purposes.

Trays height
------------
Javascript need to include WP admin bar in height calculation
Line 429, changed code to
```
// Loop each tray and set height to match body
trayMatch.each(function() {
    var bodyH = $('body').innerHeight();
    var TopbarH = $('#topbar').outerHeight();
    var NavbarH = $('#main > .main-header').outerHeight();
    var AdminbarH = $('#wpadminbar').outerHeight();
    var Height = bodyH - (TopbarH + NavbarH + AdminbarH);
    $(this).height(Height - 45);
});
```