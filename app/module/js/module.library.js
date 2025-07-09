// Init
var app = {};
// Set
app.module = {};

/**
 * Handle general timetable
 */
app.module.generalTimetable = function () {
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
            frm.common.sync.init(app.module.callbackSync);
        else
            $('#module-steps').hide();
    };
};

/**
 * Handle general notice
 */
app.module.generalNotice = function () {
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
            $('#module-notice').html(frm.bbcodeToHTML(responseText[0].result)).parent().show();
    });
};

/**
 * Callback on sync
 */
app.module.callbackSync = function () {
    // Init select2 period
    var select2periods = [];
    select2periods.push({
        id: 0,
        text: frm.label.getStatic('all')
    });

    $.when(
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Module.Read_Module',
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
    ).done(function (responseModule, responseWeek, responseSemester) {
        // Sanitise module, as it could be from external apps, like courses
        if (frm.share.params && frm.share.params.modules && responseModule[0].result.length)
            $.each(responseModule[0].result, function (indexRow, row) {
                $.each(frm.share.params.modules, function (indexModule, module) {
                    // Case insensitive comparison
                    if (row.tmt_module && frm.strcasecmp(row.tmt_module, module))
                        // Override
                        frm.share.params.modules[indexModule] = row.tmt_module;
                });
            });

        // Select2 modules
        frm.initSelect2('#module-select-modules-option', frm.common.select2.module(responseModule[0].result), frm.share.params ? frm.share.params.modules : null, true);

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
        frm.initSelect2('#module-select-period-option', select2periods, frm.share.params ? frm.share.params.period : 0);

        // Collapse accordian after select2 rendering is complete
        $('#module-select-period').collapse('hide');

        // Trigger next step
        if (frm.share.params)
            app.module.readTimetable();
    });

};

/**
 * Get timetable
 */
app.module.readTimetable = function () {
    // Set params
    const params = {
        modules: $('#module-select-modules-option').val(),
        period: $('#module-select-period-option').val(),
        syncId: frm.common.sync.id
    };

    // Get timetable
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Module.Read_Timetable',
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
        $('#module-timetable-compact').find('[name="timetables"').empty();
        $('#timetable-full-modal-body').empty();

        // Hide steps
        $('#module-steps').hide();

        // Show selected modules
        var selectedModules = [];
        if ($('#module-select-modules-option').find(':selected').length) {
            $('#module-select-modules-option').find(':selected').each(function () {
                selectedModules.push($(this).text());
            });
            $('#module-selection').find('[name="modules"]').text(selectedModules.join(', '));
        } else
            $('#module-selection').find('[name="modules"]').text(frm.label.getStatic('all'));
        // Show selected period
        $('#module-selection').find('[name="period"]').text($('#module-select-period-option').find(':selected').text());
        // Show selection
        $('#module-selection').fadeIn();

        // Check if a specific period or all periods (both semesters)
        ottKeys = Object.keys(otts);
        if (ottKeys.length == 1) {
            frm.ott.compact(otts[0], 0, $('#module-select-period-option').find(':selected').text(), '#module-timetable-compact');
            frm.ott.full(otts[0], 0, $('#module-select-period-option').find(':selected').text());
        } else
            ottKeys.forEach(indexKey => {
                frm.ott.compact(otts[indexKey], indexKey, $('#module-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text(), '#module-timetable-compact');
                frm.ott.full(otts[indexKey], indexKey, $('#module-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text());
            });

        // Auto load full modal
        if (!frm.config.ott.responsiveFirst && !isMobile.any && frm.breakpoint() > C_BREAKPOINT_LG)
            $('#timetable-full-modal').modal('show');

        // Matomo SPA traking
        frm.common.matomo.track(true);
    };
};