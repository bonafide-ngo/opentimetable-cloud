$(document).ready(function () {
    frm.msal.ready.then(function () {
        // Run routine
        frm.common.routine(frm.label.getStatic('modules'), frm.label.getStatic('modules-info'), true);

        // Share
        frm.share.setParams();

        // Init
        app.module.generalTimetable();
        app.module.generalNotice();

        // Module - Continue
        $('#module-select-modules').find('button[type="submit"]').once('click', function () {
            $('#module-select-period-heading button').click();
            $('html, body').animate({
                scrollTop: $("#module-select-modules-heading").offset().top - 10
            }, 400);
        });

        // Period - Continue
        $('#module-select-period').find('button[type="submit"]').once('click', app.module.readTimetable);
        // Period - Back
        $('#module-select-period').find('button[type="cancel"]').once('click', function () {
            $('#module-select-module-heading button').click();
        });

        // Share
        $('#module-share').once('click', function () {
            if (frm.common.sync.isDraft || frm.common.sync.isPreview) {
                $('#module-share').popover('dispose');
                frm.modal.information(frm.label.getStatic('share-no-active-info'));
            } else
                frm.share.media(this, frm.label.getStatic('modules'), frm.uri.addParam(C_PARAM_SHARE, frm.share.paramsBase64));
        });

        // Chance selection
        $('#module-change').once('click', function () {
            $('#module-selection').hide();
            $('#module-timetable-compact').hide();
            $('#module-steps').fadeIn(400, function () {
                $(this).find('#module-select-modules-heading button').click();
                $('html, body').animate({
                    scrollTop: $("#module-select-modules-heading").offset().top - 10
                }, 400);
            });
        });
    });
});