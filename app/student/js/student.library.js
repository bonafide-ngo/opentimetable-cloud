// Init
var app = {};
// Set
app.student = {};

/**
 * Handle student timetable
 */
app.student.studentTimetable = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_Setting',
        { code: frm.config.setting.flag.studentTimetable },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        // Check if student timetable is enabled
        if (result)
            frm.common.sync.init(app.student.callbackSync);
        else
            $('#student-steps').hide();
    };
};

/**
 * Handle student notice
 */
app.student.studentNotice = function () {
    $.when(
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Admin.Read_Setting',
            { code: frm.config.setting.flag.studentNotice },
            null,
            null,
            null,
            null,
            { async: false }),
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Admin.Read_Setting',
            { code: frm.config.setting.text.studentNotice },
            null,
            null,
            null,
            null,
            { async: false })
    ).done(function (responseFlag, responseText) {
        if (responseFlag[0].result)
            $('#student-notice').html(frm.bbcodeToHTML(responseText[0].result)).parent().show();
    });
};

/**
 * Callback on sync
 */
app.student.callbackSync = function () {
    // Init select2 period
    var select2periods = [];
    select2periods.push({
        id: 0,
        text: frm.label.getStatic('all')
    });

    $.when(
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
    ).done(function (responseWeek, responseSemester) {
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
        frm.initSelect2('#student-select-period-option', select2periods, 0);

        if (frm.uri.isParam(C_PARAM_PAYLOAD))
            // Autoload student verifiable via payload 
            app.student.readTimetable();
        else
            // Autoload student timetable matching todays week
            app.student.autoLoad(responseWeek[0].result);
    });

};

/**
 * Auto load today's week student timetable
 *
 * @param {*} weeks 
 * @returns 
 */
app.student.autoLoad = function (weeks) {
    if (!weeks || !weeks.length)
        return;

    // Get today @ midnight
    const todayTimestamp = moment().utc().startOf('day').unix();

    // Seek today among available weeks
    // N.B. It works wiht any starting date (Sun Mon, Tue...), as the first match is returned
    weeks.forEach(week => {
        for (let index = 0; index <= 7; index++) {
            if (todayTimestamp == week.prd_week_start_timestamp + index * 3600 * 24) {
                // Preset todays week
                const weekTimestamp = frm.config.prefix.week + week.prd_week;
                $('#student-select-period-option').val(weekTimestamp).trigger('change');
                // Auto load timetable
                app.student.readTimetable();
                return;
            }
        }
    });
}

/**
 * Get timetable
 */
app.student.readTimetable = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Student.Read_Timetable',
        {
            period: $('#student-select-period-option').val(),
            syncId: frm.common.sync.id,
            payloadBase64: frm.uri.getParam(C_PARAM_PAYLOAD)
        },
        onSuccess,
        null,
        onError,
        null,
        { async: false });

    function onSuccess(otts) {
        // Clear timetable(s)
        $('#student-timetable-compact').find('[name="timetables"').empty();
        $('#timetable-full-modal-body').empty();

        // Hide steps
        $('#student-steps').hide();
        // Show selected period
        $('#student-selection').find('[name="period"]').text($('#student-select-period-option').find(':selected').text());
        // Show selection
        $('#student-selection').fadeIn();

        // Check if a specific period or all periods (both semesters)
        ottKeys = Object.keys(otts);
        if (ottKeys.length == 1) {
            frm.ott.compact(otts[0], 0, $('#student-select-period-option').find(':selected').text(), '#student-timetable-compact');
            frm.ott.full(otts[0], 0, $('#student-select-period-option').find(':selected').text());
        } else
            ottKeys.forEach(indexKey => {
                frm.ott.compact(otts[indexKey], indexKey, $('#student-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text(), '#student-timetable-compact');
                frm.ott.full(otts[indexKey], indexKey, $('#student-select-period-option').find('option[value="' + frm.config.prefix.semester + indexKey + '"]').text());
            });

        // Auto load full modal
        if (!frm.config.ott.responsiveFirst && !isMobile.any && frm.breakpoint() > C_BREAKPOINT_LG)
            $('#timetable-full-modal').modal('show');
    };

    function onError(error) {
        frm.modal.error(frm.ajax.formatError(error));
        $('#modal-error').once('hidden.bs.modal', function () {
            // Load home page seamlessly
            frm.ss.engine.load(frm.config.url.home);
        });
    };
};