$(document).ready(function () {
    frm.msal.ready.then(function () {
        // Run routine
        frm.common.routine(frm.label.getStatic('home'));

        // Init
        app.home.generalTimetable();
        app.home.generalNotice();
        frm.common.sync.init();

        // Bind links
        $('#home-lecture').once('click', function () {
            frm.ss.engine.load(frm.config.url.lecture);
        });
        $('#home-venue').once('click', function () {
            frm.ss.engine.load(frm.config.url.venue);
        });
        $('#home-department').once('click', function () {
            frm.ss.engine.load(frm.config.url.department);
        });
        $('#home-student').once('click', function () {
            if (frm.msal.isAuthenticated())
                frm.ss.engine.load(frm.config.url.student);
            else
                frm.msal.login();
        });

        // Build links
        $.each(frm.config.links, function (label, url) {
            $('#home-links').append(
                $('<a>', {
                    class: 'list-group-item list-group-item-action text-primary',
                    href: url,
                    target: '_blank',
                    text: frm.label.getStatic(label)
                })
            );
        });
    });
});