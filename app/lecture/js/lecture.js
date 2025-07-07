$(document).ready(function () {
    frm.msal.ready.then(function () {
        // Run routine
        frm.common.routine(frm.label.getStatic('lectures'), frm.label.getStatic('lectures-info'), true);

        // Share
        frm.share.setParams();

        // Init
        app.lecture.generalTimetable();
        app.lecture.generalNotice();

        // Course - Continue
        $('#lecture-select-courses').find('button[type="submit"]').once('click', function () {
            $('#lecture-select-period-heading button').click();
            $('html, body').animate({
                scrollTop: $("#lecture-select-courses-heading").offset().top - 10
            }, 400);
        });

        // Period - Continue
        $('#lecture-select-period').find('button[type="submit"]').once('click', function () {
            $('#lecture-select-modules-heading button').click();
        });
        // Period - Back
        $('#lecture-select-period').find('button[type="cancel"]').once('click', function () {
            $('#lecture-select-courses-heading button').click();
        });

        // Modules - Shown
        $('#lecture-select-modules').on('show.bs.collapse', app.lecture.readModules);
        // Modules - Continue
        $('#lecture-select-modules').find('button[type="submit"]').once('click', app.lecture.readTimetable);
        // Modules - Back
        $('#lecture-select-modules').find('button[type="cancel"]').once('click', function () {
            $('#lecture-select-period-heading button').click();
        });

        // Share
        $('#lecture-share').once('click', function () {
            if (frm.common.sync.isDraft || frm.common.sync.isPreview) {
                $('#module-share').popover('dispose');
                frm.modal.information(frm.label.getStatic('share-no-active-info'));
            } else
                frm.share.media(this, frm.label.getStatic('lectures'), frm.uri.addParam(C_PARAM_SHARE, frm.share.paramsBase64));
        });

        // Chance selection
        $('#lecture-change').once('click', function () {
            $('#lecture-selection').hide();
            $('#lecture-timetable-compact').hide();
            $('#lecture-steps').fadeIn(400, function () {
                $(this).find('#lecture-select-courses-heading button').click();
                $('html, body').animate({
                    scrollTop: $("#lecture-select-courses-heading").offset().top - 10
                }, 400);
            });
        });
    });
});