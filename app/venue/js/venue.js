$(document).ready(function () {
    frm.msal.ready.then(function () {
        // Run routine
        frm.common.routine(frm.label.getStatic('venues'), frm.label.getStatic('venues-info'), true);

        // Share
        frm.share.setParams();

        // Init
        app.venue.generalTimetable();
        app.venue.generalNotice();

        // Venue - Continue
        $('#venue-select-venues').find('button[type="submit"]').once('click', function () {
            $('#venue-select-period-heading button').click();
            $('html, body').animate({
                scrollTop: $("#venue-select-venues-heading").offset().top - 10
            }, 400);
        });

        // Period - Continue
        $('#venue-select-period').find('button[type="submit"]').once('click', app.venue.readTimetable);
        // Period - Back
        $('#venue-select-period').find('button[type="cancel"]').once('click', function () {
            $('#venue-select-venues-heading button').click();
        });

        // Share
        $('#venue-share').once('click', function () {
            if (frm.common.sync.isDraft || frm.common.sync.isPreview) {
                $('#module-share').popover('dispose');
                frm.modal.information(frm.label.getStatic('share-no-active-info'));
            } else
                frm.share.media(this, frm.label.getStatic('venues'), frm.uri.addParam(C_PARAM_SHARE, frm.share.paramsBase64));
        });

        // Chance selection
        $('#venue-change').once('click', function () {
            $('#venue-selection').hide();
            $('#venue-timetable-compact').hide();
            $('#venue-steps').fadeIn(400, function () {
                $(this).find('#venue-select-venues-heading button').click();
                $('html, body').animate({
                    scrollTop: $("#venue-select-venues-heading").offset().top - 10
                }, 400);
            });
        });
    });
});