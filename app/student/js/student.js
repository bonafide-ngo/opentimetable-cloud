$(document).ready(function () {
    frm.msal.ready.then(function () {
        // Security
        if (!frm.uri.isParam(C_PARAM_PAYLOAD))
            if (!frm.msal.isAuthenticated()) {
                if (frm.isSafari())
                    // Request user interaction to trigger MSAL login in Safari
                    frm.modal.confirm(frm.label.getStatic('confirm-msal-interaction'), frm.msal.login);
                else
                    // Force login
                    frm.msal.login();

                // Preclude student page in background
                frm.ss.engine.load(frm.config.url.home);
                return;
            } else if (![C_MSAL_GROUP_STUDENT].includes(frm.msal.role)) {
                frm.modal.information(frm.label.parseDynamic('information-student', [frm.config.email.timetable[0]]));
                // Preclude student page in background
                frm.ss.engine.load(frm.config.url.home);
                return;
            }

        // Run routine
        frm.common.routine(frm.label.getStatic('student'), frm.label.getStatic('student-info'), true);

        // Init
        app.student.studentTimetable();
        app.student.studentNotice();

        // Period - Continue
        $('#student-select-period').find('button[type="submit"]').once('click', app.student.readTimetable);

        // Chance selection
        $('#student-change').once('click', function () {
            $('#student-selection').hide();
            $('#student-timetable-compact').hide();
            $('#student-steps').fadeIn(400, function () {
                $('html, body').animate({
                    scrollTop: $("#student-select-period-heading").offset().top - 10
                }, 400);
            });
        });
    });
});