// Init
var frm = frm || {};

/*******************************************************************************
Framework - OpenTimetable
*******************************************************************************/

// Set
frm.ott = {};

/**
 * Handle compact (responsive) timetable
 * @param {*} ott 
 * @param {*} index 
 * @param {*} period 
 * @param {*} selector 
 */
frm.ott.compact = function (ott, index, period, selector) {
    // Extract references and retain original order
    ottPeriods = Object.keys(ott.cues.periods);
    ottDays = Object.keys(ott.days);

    // Template container
    var templateContainer = $("#timetable-compact-container-template").clone();
    templateContainer.removeAttr('id');
    templateContainer.find('[name="period"]').text(period);
    templateContainer.find('button[name="full-week"]').attr('data-bs-target', '#timetable-full-modal');

    // Template tab
    ottDays.forEach((valueDay, indexDay) => {
        var templateTab = $("#timetable-compact-tab-template").clone();
        templateTab.removeAttr('id');
        templateTab.find('.nav-link').attr('href', '#timetable-compact-tab-' + index + '-' + indexDay);
        templateTab.find('.nav-link').text(frm.ott.day2tab(valueDay));
        if (!indexDay)
            templateTab.find('.nav-link').addClass('active');

        // Append
        templateContainer.find('[name="tabs"]').append(templateTab);
    });

    // Template day
    ottDays.forEach((valueDay, indexDay) => {
        var templateDay = $("#timetable-compact-day-template").clone();
        templateDay.attr('id', 'timetable-compact-tab-' + index + '-' + indexDay);
        if (!indexDay)
            templateDay.addClass('active');

        // Template period
        var countPeriods = 0;
        ottPeriods.forEach((valuePeriod, indexPeriod) => {
            if (ott.days[valueDay]
                && ott.days[valueDay].classes[valuePeriod]
                && ott.days[valueDay].classes[valuePeriod].length) {
                var templatePeriod = $("#timetable-compact-period-template").clone();
                templatePeriod.removeAttr('id');
                templatePeriod.find('[name="time"]').text(frm.config.ott.showTimeRange ? ott.cues.periods[valuePeriod].from + ' - ' + ott.cues.periods[valuePeriod].to : ott.cues.periods[valuePeriod].from);

                // Template divider
                var templateDivider = $("#timetable-compact-divider-template").clone();
                templateDivider.removeAttr('id');

                // Classes
                ott.days[valueDay].classes[valuePeriod].forEach((valueClass, indexClass) => {
                    var templateClass = $("#timetable-compact-class-template").clone();
                    templateClass.removeAttr('id');

                    var module = null;
                    // Check for classgroup
                    if (valueClass.extension.group) {
                        var classGroup = $('<span>', {
                            class: 'text-info',
                            text: '/' + valueClass.extension.group
                        })[0].outerHTML;

                        // Parse name
                        module = valueClass.name.replace(new RegExp('\/' + valueClass.extension.group + '$', 'i'), classGroup);
                    } else
                        // Set name
                        module = valueClass.name;

                    // Check link
                    if (valueClass.extension.link) {
                        var link = $('<a>', {
                            text: valueClass.abbreviation,
                            href: frm.config.url.coursesModule.sprintf([valueClass.extension.link]),
                            target: '_blank',
                            class: 'fw-bold text-light'
                        })[0].outerHTML;
                        // Parse module
                        module = valueClass.name.replace(new RegExp(valueClass.abbreviation, 'i'), link);
                        // Set link
                        templateClass.find('[name="module"]').addClass('bg-secondary').html(module);
                    } else
                        templateClass.find('[name="module"]').addClass('bg-dark').html(module);

                    // Set venue
                    templateClass.find('[name="venue"]').html('[ ' + valueClass.location + ' ]');
                    templateClass.find('[name="venue"]').attr('code', valueClass.location);
                    templateClass.find('[name="venue"]').attr('href', frm.config.url.map ?? '');

                    // Append
                    templatePeriod.find('[name="classes"]').append(templateClass);;
                });

                // Append
                if (countPeriods++)
                    templateDay.find('[name="periods"]').append($().add(templateDivider).add(templatePeriod));
                else
                    templateDay.find('[name="periods"]').append(templatePeriod);
            }
        });

        // Show no classes
        if (!countPeriods) {
            var templateEmpty = $("#timetable-compact-empty-template").clone();
            templateEmpty.removeAttr('id');

            // Set label
            templateEmpty.find('[name="no-classes-day"]').text(frm.label.parseDynamic('no-classes-day', [valueDay]));

            // Append
            templateDay.find('[name="periods"]').append(templateEmpty);
        }

        // Append
        templateContainer.find('[name="days"]').append(templateDay);
    });

    // Render
    $(selector).find('[name="timetables"]').append(templateContainer);
    $(selector).find('[name="timestamp"]').text(moment().format('LLL'));
    $(selector).fadeIn();

    // Init popover
    $('[data-bs-toggle="popover"]').popover();

    // Bind map
    frm.ott.bindMap(selector);

    // Print full timetable
    $(selector).find('button[type="print"]').once('click', function () {
        $('#timetable-full-modal').once('shown.bs.modal', function () {
            // Disable print for next clicks
            $('#timetable-full-modal').off('shown.bs.modal');
            window.print();
        }).modal('show');
    });
}

/**
 * Handle full (scrollable) timetable
 * @param {*} ott 
 * @param {*} index 
 * @param {*} period 
 */
frm.ott.full = function (ott, index, period) {
    // Extract references and retain original order
    ottPeriods = Object.keys(ott.cues.periods);
    ottDays = Object.keys(ott.days);

    // Set overall timestamp
    $("#timetable-full-modal").find('[name="timestamp"]').text(moment().format('LLL'));

    // Template body 
    var templateBody = $("#timetable-full-body-template").clone();
    templateBody.removeAttr('id');
    // Set period
    templateBody.find('[name="period"]').text(period);

    // Template corner
    var templateCorner = $("#timetable-full-corner-template").clone();
    templateBody.find('[name="days"]').append(templateCorner);

    // Template days
    var width = Math.floor((100 - 5) / ottDays.length);
    ottDays.forEach((valueDay, indexDay) => {
        var templateDay = $("#timetable-full-day-template").clone();
        templateDay.removeAttr('id');
        //templateDay.css('width', width + '%');
        templateDay.text(valueDay);

        // Append
        templateBody.find('[name="days"]').append(templateDay);
    });

    // Grid
    var gridCount = 0;
    ottPeriods.forEach((valuePeriod, indexPeriod) => {
        // Init row
        var row = $('<tr>');

        // Template time
        var templateTime = $("#timetable-full-time-template").clone();
        templateTime.text(frm.config.ott.showTimeRange ? ott.cues.periods[valuePeriod].from + ' - ' + ott.cues.periods[valuePeriod].to : ott.cues.periods[valuePeriod].from);

        // Append
        row.append(templateTime);

        ottDays.forEach((valueDay, indexDay) => {
            var templateWrapper = $("#timetable-full-wrapper-template").clone();
            templateWrapper.removeAttr('id');

            // Classes
            if (ott.days[valueDay]
                && ott.days[valueDay].classes[valuePeriod]
                && ott.days[valueDay].classes[valuePeriod].length) {
                ott.days[valueDay].classes[valuePeriod].forEach((valueClass, indexClass) => {
                    ++gridCount;

                    var templateClass = $("#timetable-full-class-template").clone();
                    templateClass.removeAttr('id');

                    var module = null;
                    // Check for classgroup
                    if (valueClass.extension.group) {
                        var classGroup = $('<span>', {
                            class: 'text-info',
                            text: '/' + valueClass.extension.group
                        })[0].outerHTML;

                        // Parse name
                        module = valueClass.name.replace(new RegExp('\/' + valueClass.extension.group + '$', 'i'), classGroup);
                    } else
                        // Set name
                        module = valueClass.name;

                    // Check link
                    if (valueClass.extension.link) {
                        var link = $('<a>', {
                            text: valueClass.abbreviation,
                            href: frm.config.url.coursesModule.sprintf([valueClass.extension.link]),
                            target: '_blank',
                            class: 'fw-bold text-light'
                        })[0].outerHTML;
                        // Parse module
                        module = valueClass.name.replace(new RegExp(valueClass.abbreviation, 'i'), link);
                        // Set link
                        templateClass.find('[name="module"]').addClass('bg-secondary').html(module);
                    } else
                        templateClass.find('[name="module"]').addClass('bg-dark').html(module);

                    // Set venue
                    templateClass.find('[name="venue"]').html('[ ' + valueClass.location + ' ]');
                    templateClass.find('[name="venue"]').attr('code', valueClass.location);
                    templateClass.find('[name="venue"]').attr('href', frm.config.url.map ?? '');

                    // Append
                    templateWrapper.find('tbody').append(templateClass);
                });
                // Append
                row.append(templateWrapper);
            } else
                // Append
                row.append($('<td>'));
        });

        // Append
        templateBody.find('[name="grid"]').append(row);
    });

    // Show no classes
    if (!gridCount) {
        var templateEmpty = $("#timetable-full-empty-template").clone();
        templateEmpty.removeAttr('id');

        // Append
        templateBody.find('[name="table"]').empty().append(templateEmpty);
        // Remove info for clear view
        templateBody.find('[name="info"]').remove();
    }

    // Render
    $("#timetable-full-modal-body").append(templateBody);

    // Init popover
    $('[data-bs-toggle="popover"]').popover();

    // Bind map
    frm.ott.bindMap("#timetable-full-modal-body");

    // Print full timetable
    $('#timetable-full-modal').find('button[type="print"]').once('click', function () {
        window.print();
    });
}

/**
 * Short a day to fit a tab in the compact view
 * N.B. Check if it can be shortened if not a valid date
 * 
 * @param {*} day 
 * @returns 
 */
frm.ott.day2tab = function (day) {
    return moment(day, moment.ISO_8601, true).isValid() ? day : day.substring(0, 3);
}

/**
 * Bind map event
 * 
 * @param {*} selector 
 * @returns 
 */
frm.ott.bindMap = function (selector) {
    if (!frm.config.map.enable)
        return;

    // Bind map
    $(selector).find('[name="venue"]').once('click', function (e) {
        e.preventDefault();
        var that = this;

        // Get venue
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Timetable.Read_Location',
            {
                syncId: frm.common.sync.id,
                venueCode: $(this).attr('code')
            },
            onSuccess,
            null,
            null,
            null,
            { async: false });

        function onSuccess(result) {
            // Check coordinates exist
            if (result['lct_latitude'] && result['lct_longitude'])
                // Open map
                frm.map.listen(result['lct_latitude'], result['lct_longitude'], result['vnx_code'], result['vnx_name']);
            else if ($(that).attr('href'))
                // Fallback on map if any
                window.open($(that).attr('href'), '_blank');
        }
    });
}