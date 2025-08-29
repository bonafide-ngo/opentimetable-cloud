// Init
var app = {};
// Set
app.department = {};

/**
 * Handle general timetable
 */
app.department.generalTimetable = function () {
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
            frm.common.sync.init(app.department.callbackSync);
        else
            $('#department-steps').hide();
    };
};

/**
 * Handle general notice
 */
app.department.generalNotice = function () {
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
            $('#department-notice').html(frm.bbcodeToHTML(responseText[0].result)).parent().show();
    });
};

/**
 * Callback on sync
 */
app.department.callbackSync = function () {
    // Init select2 period
    var select2periods = [];
    select2periods.push({
        id: 0,
        text: frm.label.getStatic('all')
    });

    $.when(
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Department.Read_Department',
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
    ).done(function (responseDepartment, responseWeek, responseSemester) {
        // Select2 departments
        frm.initSelect2('#department-select-departments-option', frm.common.select2.department(responseDepartment[0].result), frm.share.params ? frm.share.params.departments : null, true);

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
        frm.initSelect2('#department-select-period-option', select2periods, frm.share.params ? frm.share.params.period : 0);

        // Collapse accordian after select2 rendering is complete
        $('#department-select-courses').collapse('hide');
        $('#department-select-period').collapse('hide');
        $('#department-select-modules').collapse('hide');

        // Trigger next step
        if (frm.share.params)
            frm.share.handleTrigger(frm.share.params.departments, $('#department-select-departments-option').val(), app.department.readCourses);
    });

};

/**
 * Get courses
 */
app.department.readCourses = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Department.Read_Course',
        {
            departments: $('#department-select-departments-option').val(),
            syncId: frm.common.sync.id
        },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(results) {
        // Select2 courses
        frm.initSelect2('#department-select-courses-option', frm.common.select2.course(results), frm.share.params ? frm.share.params.courses : null, true);

        // Trigger next step
        if (frm.share.params)
            frm.share.handleTrigger(frm.share.params.courses, $('#department-select-courses-option').val(), app.department.readModules);
    };
};

/**
 * Get modules
 */
app.department.readModules = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Department.Read_Module',
        {
            departments: $('#department-select-departments-option').val(),
            courses: $('#department-select-courses-option').val(),
            period: $('#department-select-period-option').val(),
            syncId: frm.common.sync.id
        },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(results) {
        // Select2 modules
        frm.initSelect2('#department-select-modules-option', frm.common.select2.module(results), frm.share.params ? frm.share.params.modules : null, true);

        // Trigger next step
        if (frm.share.params)
            frm.share.handleTrigger(frm.share.params.modules, $('#department-select-modules-option').val(), app.department.readTimetable);
    };
};

/**
 * Get timetable
 */
app.department.readTimetable = function () {
    // Set params
    const params = {
        departments: $('#department-select-departments-option').val(),
        courses: $('#department-select-courses-option').val(),
        period: $('#department-select-period-option').val(),
        modules: $('#department-select-modules-option').val(),
        syncId: frm.common.sync.id
    };

    // Get timetable
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Department.Read_Timetable',
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
        $('#department-timetable-compact').find('[name="timetables"').empty();
        $('#timetable-full-modal-body').empty();

        // Hide steps
        $('#department-steps').hide();
        // Show selected departments
        var selectedDepartments = [];
        if ($('#department-select-departments-option').find(':selected').length) {
            $('#department-select-departments-option').find(':selected').each(function () {
                selectedDepartments.push($(this).text());
            });
            $('#department-selection').find('[name="departments"]').text(selectedDepartments.join(', '));
        } else
            $('#department-selection').find('[name="departments"]').text(frm.label.getStatic('all'));
        // Show selected courses
        var selectedCourses = [];
        if ($('#department-select-courses-option').find(':selected').length) {
            $('#department-select-courses-option').find(':selected').each(function () {
                selectedCourses.push($(this).text());
            });
            $('#department-selection').find('[name="courses"]').text(selectedCourses.join(', '));
        } else
            $('#department-selection').find('[name="courses"]').text(frm.label.getStatic('all'));
        // Show selected period
        $('#department-selection').find('[name="period"]').text($('#department-select-period-option').find(':selected').text());
        // Show selected modules
        var selectedModules = [];
        if ($('#department-select-modules-option').find(':selected').length) {
            $('#department-select-modules-option').find(':selected').each(function () {
                selectedModules.push($(this).text());
            });
            $('#department-selection').find('[name="modules"]').text(selectedModules.join(', '));
        } else
            $('#department-selection').find('[name="modules"]').text(frm.label.getStatic('all'));
        // Show selection
        $('#department-selection').fadeIn();

        // Check if a specific period or all periods (both semesters)
        ottKeys = Object.keys(otts);
        if (ottKeys.length == 1) {
            frm.ott.compact(otts[0], 0, $('#department-select-period-option').find(':selected').text(), '#department-timetable-compact');
            frm.ott.full(otts[0], 0, $('#department-select-period-option').find(':selected').text());
        } else
            ottKeys.forEach(indexKey => {
                frm.ott.compact(otts[indexKey], indexKey, $('#department-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text(), '#department-timetable-compact');
                frm.ott.full(otts[indexKey], indexKey, $('#department-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text());
            });

        // Auto load full modal
        if (!frm.config.ott.responsiveFirst && !isMobile.any && frm.breakpoint() > C_BREAKPOINT_LG)
            $('#timetable-full-modal').modal('show');

        // Matomo SPA traking
        frm.common.matomo.track(true);
    };
};