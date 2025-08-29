// Init
var app = {};
// Set
app.lecture = {};

/**
 * Handle general timetable
 */
app.lecture.generalTimetable = function () {
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
            frm.common.sync.init(app.lecture.callbackSync);
        else
            $('#lecture-steps').hide();
    };
};

/**
 * Handle general notice
 */
app.lecture.generalNotice = function () {
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
            $('#lecture-notice').html(frm.bbcodeToHTML(responseText[0].result)).parent().show();
    });
};

/**
 * Callback on sync
 */
app.lecture.callbackSync = function () {
    // Init select2 period
    var select2periods = [];
    select2periods.push({
        id: 0,
        text: frm.label.getStatic('all')
    });

    $.when(
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Lecture.Read_Course',
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
    ).done(function (responseCourse, responseWeek, responseSemester) {
        // Select2 courses
        const courses = responseCourse[0].result;
        frm.initSelect2('#lecture-select-courses-option', frm.common.select2.course(courses), frm.share.params ? frm.share.params.courses : null);

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
        frm.initSelect2('#lecture-select-period-option', select2periods, frm.share.params ? frm.share.params.period : 0);

        // Collapse accordian after select2 rendering is complete
        $('#lecture-select-period').collapse('hide');
        $('#lecture-select-modules').collapse('hide');

        // Trigger next step
        if (frm.share.params)
            frm.share.handleTrigger(frm.share.params.courses, $('#lecture-select-courses-option').val(), app.lecture.readModules);
    });

};

/**
 * Get modules
 */
app.lecture.readModules = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Lecture.Read_Module',
        {
            courses: $('#lecture-select-courses-option').val(),
            period: $('#lecture-select-period-option').val(),
            syncId: frm.common.sync.id
        },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(results) {
        // Select2 module
        frm.initSelect2('#lecture-select-modules-option', frm.common.select2.module(results), frm.share.params ? frm.share.params.modules : null, true);

        // Trigger next step
        if (frm.share.params)
            frm.share.handleTrigger(frm.share.params.modules, $('#lecture-select-modules-option').val(), app.lecture.readTimetable);
    };
};

/**
 * Get timetable
 */
app.lecture.readTimetable = function () {
    // Set params
    const params = {
        courses: $('#lecture-select-courses-option').val(),
        period: $('#lecture-select-period-option').val(),
        modules: $('#lecture-select-modules-option').val(),
        syncId: frm.common.sync.id
    };

    // Get timetable
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Lecture.Read_Timetable',
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
        $('#lecture-timetable-compact').find('[name="timetables"').empty();
        $('#timetable-full-modal-body').empty();

        // Hide steps
        $('#lecture-steps').hide();
        // Show selected courses
        var selectedCourses = [];
        if ($('#lecture-select-courses-option').find(':selected').length) {
            $('#lecture-select-courses-option').find(':selected').each(function () {
                selectedCourses.push($(this).text());
            });
            $('#lecture-selection').find('[name="courses"]').text(selectedCourses.join(', '));
        } else
            $('#lecture-selection').find('[name="courses"]').text(frm.label.getStatic('all'));
        // Show selected period
        $('#lecture-selection').find('[name="period"]').text($('#lecture-select-period-option').find(':selected').text());
        // Show selected modules
        var selectedModules = [];
        if ($('#lecture-select-modules-option').find(':selected').length) {
            $('#lecture-select-modules-option').find(':selected').each(function () {
                selectedModules.push($(this).text());
            });
            $('#lecture-selection').find('[name="modules"]').text(selectedModules.join(', '));
        } else
            $('#lecture-selection').find('[name="modules"]').text(frm.label.getStatic('all'));
        // Show selection
        $('#lecture-selection').fadeIn();

        // Check if a specific period or all periods (both semesters)
        ottKeys = Object.keys(otts);
        if (ottKeys.length == 1) {
            frm.ott.compact(otts[0], 0, $('#lecture-select-period-option').find(':selected').text(), '#lecture-timetable-compact');
            frm.ott.full(otts[0], 0, $('#lecture-select-period-option').find(':selected').text());
        } else
            ottKeys.forEach(indexKey => {
                frm.ott.compact(otts[indexKey], indexKey, $('#lecture-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text(), '#lecture-timetable-compact');
                frm.ott.full(otts[indexKey], indexKey, $('#lecture-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text());
            });

        // Auto load full modal
        if (!frm.config.ott.responsiveFirst && !isMobile.any && frm.breakpoint() > C_BREAKPOINT_LG)
            $('#timetable-full-modal').modal('show');

        // Matomo SPA traking
        frm.common.matomo.track(true);
    };
};