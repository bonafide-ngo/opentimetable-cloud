$(document).ready(function () {
    frm.msal.ready.then(function () {
        // Run routine
        frm.common.routine(frm.label.getStatic('departments'), frm.label.getStatic('departments-info'), true);

        // Share
        frm.share.setParams();

        // Init
        app.department.generalTimetable();
        app.department.generalNotice();

        // Department - Continue
        $('#department-select-departments').find('button[type="submit"]').once('click', function () {
            $('#department-select-courses-heading button').click();
            $('html, body').animate({
                scrollTop: $("#department-select-departments-heading").offset().top - 10
            }, 400);
        });

        // Courses - Shown
        $('#department-select-courses').on('show.bs.collapse', app.department.readCourses);
        // Courses - Continue
        $('#department-select-courses').find('button[type="submit"]').once('click', function () {
            $('#department-select-period-heading button').click();
            $('html, body').animate({
                scrollTop: $("#department-select-courses-heading").offset().top - 10
            }, 400);
        });
        // Courses - Back
        $('#department-select-courses').find('button[type="cancel"]').once('click', function () {
            $('#department-select-departments-heading button').click();
        });

        // Period - Continue
        $('#department-select-period').find('button[type="submit"]').once('click', function () {
            $('#department-select-modules-heading button').click();
        });
        // Period - Back
        $('#department-select-period').find('button[type="cancel"]').once('click', function () {
            $('#department-select-courses-heading button').click();
        });

        // Modules - Shown
        $('#department-select-modules').on('show.bs.collapse', app.department.readModules);
        // Modules - Continue
        $('#department-select-modules').find('button[type="submit"]').once('click', app.department.readTimetable);
        // Modules - Back
        $('#department-select-modules').find('button[type="cancel"]').once('click', function () {
            $('#department-select-period-heading button').click();
        });

        // Share
        $('#department-share').once('click', function () {
            if (frm.common.sync.isDraft || frm.common.sync.isPreview) {
                $('#module-share').popover('dispose');
                frm.modal.information(frm.label.getStatic('share-no-active-info'));
            } else
                frm.share.media(this, frm.label.getStatic('departments'), frm.uri.addParam(C_PARAM_SHARE, frm.share.paramsBase64));
        });

        // Chance selection
        $('#department-change').once('click', function () {
            $('#department-selection').hide();
            $('#department-timetable-compact').hide();
            $('#department-steps').fadeIn(400, function () {
                $(this).find('#department-select-departments-heading button').click();
                $('html, body').animate({
                    scrollTop: $("#department-select-departments-heading").offset().top - 10
                }, 400);
            });
        });
    });
});