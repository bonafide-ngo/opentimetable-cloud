// Init
var app = {};
// Set
app.venue = {};

/**
 * Handle general timetable
 */
app.venue.generalTimetable = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_Setting',
        { code: frm.config.setting.flag.generalTimetable },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        // Check if general timetable is enabled or authenticated
        if (result || (frm.msal.isAuthenticated() && [C_MSAL_GROUP_ADMIN, C_MSAL_GROUP_STAFF, C_MSAL_GROUP_REVIEWER].includes(frm.msal.role)))
            frm.common.sync.init(app.venue.callbackSync);
        else
            $('#venue-steps').hide();
    };
};

/**
 * Handle general notice
 */
app.venue.generalNotice = function () {
    $.when(
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Admin.Read_Setting',
            { code: frm.config.setting.flag.generalNotice },
            null,
            null,
            null,
            null,
            { async: false }),
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Admin.Read_Setting',
            { code: frm.config.setting.text.generalNotice },
            null,
            null,
            null,
            null,
            { async: false })
    ).done(function (responseFlag, responseText) {
        if (responseFlag[0].result)
            $('#venue-notice').html(frm.bbcodeToHTML(responseText[0].result)).parent().show();
    });
};

/**
 * Callback on sync
 */
app.venue.callbackSync = function () {
    // Init select2 period
    var select2periods = [];
    select2periods.push({
        id: 0,
        text: frm.label.getStatic('all')
    });

    $.when(
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Venue.Read_Venue',
            { syncId: frm.common.sync.id },
            null,
            null,
            null,
            null,
            { async: false }),
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Timetable.Read_Period_Week',
            { syncId: frm.common.sync.id },
            null,
            null,
            null,
            null,
            { async: false }),
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Timetable.Read_Period_Semester',
            { syncId: frm.common.sync.id },
            null,
            null,
            null,
            null,
            { async: false })
    ).done(function (responseVenue, responseWeek, responseSemester) {
        // Select2 venues
        frm.initSelect2('#venue-select-venues-option', frm.common.select2.venue(responseVenue[0].result), frm.share.params ? frm.share.params.venues : null, true);

        // Select2 period for semester
        if (responseSemester[0].result.length) {
            // Append divider
            select2periods.push({
                id: 'divider',
                disabled: true
            });
            // Append semesters
            $.merge(select2periods, frm.common.select2.semester(responseSemester[0].result));
        }

        // Select2 period for week
        if (responseWeek[0].result.length) {
            // Append divider
            select2periods.push({
                id: 'divider',
                disabled: true
            });
            // Append weeks
            $.merge(select2periods, frm.common.select2.week(responseWeek[0].result));
        }

        // Select2 merge all, semester, week
        frm.initSelect2('#venue-select-period-option', select2periods, frm.share.params ? frm.share.params.period : 0);

        // Collapse accordian after select2 rendering is complete
        $('#venue-select-period').collapse('hide');

        // Trigger next step
        if (frm.share.params)
            app.venue.readTimetable();
    });

};

/**
 * Get timetable
 */
app.venue.readTimetable = function () {
    // Set params
    const params = {
        venues: $('#venue-select-venues-option').val(),
        period: $('#venue-select-period-option').val(),
        syncId: frm.common.sync.id
    };

    // Get timetable
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Venue.Read_Timetable',
        params,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(otts) {
        // Remove syncId from sharing 
        delete params.syncId;
        // Clear share params, if any
        frm.share.clearParams();
        // Encode share params
        frm.share.encodeParamsBase64(params);

        // Clear timetable(s)
        $('#venue-timetable-compact').find('[name="timetables"').empty();
        $('#timetable-full-modal-body').empty();

        // Hide steps
        $('#venue-steps').hide();

        // Show selected venues
        var selectedVenues = [];
        if ($('#venue-select-venues-option').find(':selected').length) {
            $('#venue-select-venues-option').find(':selected').each(function () {
                selectedVenues.push($(this).text());
            });
            $('#venue-selection').find('[name="venues"]').text(selectedVenues.join(', '));
        } else
            $('#venue-selection').find('[name="venues"]').text(frm.label.getStatic('all'));
        // Show selected period
        $('#venue-selection').find('[name="period"]').text($('#venue-select-period-option').find(':selected').text());
        // Show selection
        $('#venue-selection').fadeIn();

        // Check if a specific period or all periods (both semesters)
        ottKeys = Object.keys(otts);
        if (ottKeys.length == 1) {
            frm.ott.compact(otts[0], 0, $('#venue-select-period-option').find(':selected').text(), '#venue-timetable-compact');
            frm.ott.full(otts[0], 0, $('#venue-select-period-option').find(':selected').text());
        } else
            ottKeys.forEach(indexKey => {
                frm.ott.compact(otts[indexKey], indexKey, $('#venue-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text(), '#venue-timetable-compact');
                frm.ott.full(otts[indexKey], indexKey, $('#venue-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text());
            });

        // Auto load full modal
        if (!frm.config.ott.responsiveFirst && !isMobile.any && frm.breakpoint() > C_BREAKPOINT_LG)
            $('#timetable-full-modal').modal('show');

        // Matomo SPA traking
        frm.common.matomo.track(true);
    };
};