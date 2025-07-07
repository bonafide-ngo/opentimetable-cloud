// Init
var app = {};
// Set
app.admin = {};

app.admin.chartjs = {};
app.admin.chartjs.isReady = false;
app.admin.chartjs.totalCount = 0;
app.admin.chartjs.readyCount = 0;
app.admin.chartjs.plugin = {};

app.admin.datatables = {};
app.admin.datatables.isReady = false;
app.admin.datatables.totalCount = 0;
app.admin.datatables.readyCount = 0;

/**
 * Callback on sync
 */
app.admin.callbackSync = function () {
    app.admin.initSettings();
    app.admin.sync.read(true);

    // Check map is enabled
    if (frm.config.map.enable)
        app.admin.venue.read();

    // System tab is for admin only
    if ([C_MSAL_GROUP_ADMIN].includes(frm.msal.role)) {
        // Fetch raw exists
        app.admin.system.readRaw();
        // Fetch environment stats
        app.admin.system.readStats();
        // Fetch traffic stats
        app.admin.system.traffic.readStats();
        // Fetch cache stats
        app.admin.system.cache.readStats();
    }
};

/**
 * Init settings
 */
app.admin.initSettings = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_Setting_All',
        null,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(results) {
        results.forEach(result => {
            switch (result.code) {
                // Flag
                case frm.config.setting.flag.generalTimetable:
                    $('#admin-general').find('input[name="toggle-timetable"]').bootstrapToggle(result.value ? 'on' : 'off');
                    break;
                case frm.config.setting.flag.generalNotice:
                    $('#admin-general').find('input[name="toggle-notice"]').bootstrapToggle(result.value ? 'on' : 'off');
                    break;
                case frm.config.setting.flag.studentTimetable:
                    $('#admin-student').find('input[name="toggle-timetable"]').bootstrapToggle(result.value ? 'on' : 'off');
                    break;
                case frm.config.setting.flag.studentNotice:
                    $('#admin-student').find('input[name="toggle-notice"]').bootstrapToggle(result.value ? 'on' : 'off');
                    break;
                case frm.config.setting.flag.autosync:
                    $('#admin-sync').find('input[name="toggle-autosync"]').bootstrapToggle(result.value ? 'on' : 'off');
                    if (!result.value)
                        $('#admin-sync').find('[name="toggle-autopublish-row"]').fadeOut();
                    break;
                case frm.config.setting.flag.autopublish:
                    $('#admin-sync').find('input[name="toggle-autopublish"]').bootstrapToggle(result.value ? 'on' : 'off');
                    break;
                case frm.config.setting.flag.draft:
                    $('#admin-sync').find('input[name="toggle-draft"]').bootstrapToggle(result.value ? 'on' : 'off');
                    break;
                // Text
                case frm.config.setting.text.generalNotice:
                    $('#admin-general-notice').val(frm.bbcodeToHTML(result.value));
                    break;
                case frm.config.setting.text.studentNotice:
                    $('#admin-student-notice').val(frm.bbcodeToHTML(result.value));
                    break;
            }
        });

        // Bind settings
        $('#admin-general').find('input[name="toggle-timetable"]').once("change", function () {
            app.admin.updateSettingFlag(frm.config.setting.flag.generalTimetable, $(this).is(':checked'));
        });
        $('#admin-general').find('input[name="toggle-notice"]').once("change", function () {
            app.admin.updateSettingFlag(frm.config.setting.flag.generalNotice, $(this).is(':checked'));
        });
        $('#admin-student').find('input[name="toggle-timetable"]').once("change", function () {
            app.admin.updateSettingFlag(frm.config.setting.flag.studentTimetable, $(this).is(':checked'));
        });
        $('#admin-student').find('input[name="toggle-notice"]').once("change", function () {
            app.admin.updateSettingFlag(frm.config.setting.flag.studentNotice, $(this).is(':checked'));
        });
        $('#admin-sync').find('input[name="toggle-autosync"]').once("change", function () {
            app.admin.updateSettingFlag(frm.config.setting.flag.autosync, $(this).is(':checked'));
            if ($(this).is(':checked'))
                $('#admin-sync').find('[name="toggle-autopublish-row"]').fadeIn();
            else
                $('#admin-sync').find('[name="toggle-autopublish-row"]').fadeOut();
        });
        $('#admin-sync').find('input[name="toggle-autopublish"]').once("change", function () {
            app.admin.updateSettingFlag(frm.config.setting.flag.autopublish, $(this).is(':checked'));
        });
        $('#admin-sync').find('input[name="toggle-draft"]').once("change", function () {
            app.admin.updateSettingFlag(frm.config.setting.flag.draft, $(this).is(':checked'));
        });

        // Destroy all global instances of tinymce
        tinymce.remove();
        // Init tinymce
        tinymce.init({
            selector: 'textarea',
            license_key: 'gpl',
            promotion: false,
            height: 240,
            menubar: false,
            toolbar: 'save | undo | bold italic underline blockquote | link fontsize forecolor | copy cut paste',
            plugins: 'save link',
            save_onsavecallback: function (editor) {
                // Remove sabe button highlight
                var saveButton = $('#' + editor.id + ' ~ .tox').find('button[data-mce-name="save"]');
                saveButton.removeClass("save-button-enabled");
                // Handle the save
                switch (editor.id) {
                    case 'admin-general-notice':
                        app.admin.updateSettingText(frm.config.setting.text.generalNotice, frm.htmlToBBCode(editor.getContent()));
                        break;
                    case 'admin-student-notice':
                        app.admin.updateSettingText(frm.config.setting.text.studentNotice, frm.htmlToBBCode(editor.getContent()));
                        break;
                }
            },
            setup: function (editor) {
                // Highlight save button on content alter
                editor.on('input', async function () {
                    await frm.sleep(200);
                    var saveButton = $('#' + editor.id + ' ~ .tox').find('button[data-mce-name="save"]');
                    if (saveButton.attr('aria-disabled') == "false")
                        saveButton.addClass("save-button-enabled");
                    else
                        saveButton.removeClass("save-button-enabled");
                });
            },
            // N.B. Add necessary language packs in index.html
            // https://www.tiny.cloud/get-tiny/language-packages/
            language: frm.label.lang
        });
    }
}
/**
 * Update a flag setting 
 */
app.admin.updateSettingFlag = function (code, flag) {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Update_SettingFlag',
        {
            code: code,
            flag: flag
        });
}
/**
 * Update a text setting
 */
app.admin.updateSettingText = function (code, text) {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Update_SettingText',
        {
            code: code,
            text: text
        });
}

/**
 * Generate evenly spaced colours
 * https://stackoverflow.com/questions/1484506/random-color-generator
 * 
 * @param {*} alpha 
 * @returns 
 */
app.admin.rainbow = function (alpha) {
    alpha = alpha || 0.2;

    // 25 random hues with step of 10 degrees
    var r = Math.floor(Math.random() * 25) * 10;
    var g = Math.floor(Math.random() * 25) * 10;
    var b = Math.floor(Math.random() * 25) * 10;

    return [
        'rgba(' + r + ',' + g + ',' + b + ',' + 1 + ')',
        'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')'];
}

/**
 * Chart JS plugin to draw an horizontal line
 * http://www.java2s.com/example/javascript/chart.js/draw-horizontal-lines-in-chartjs.html
 */
app.admin.chartjs.plugin.line = {
    id: "linePlugin",
    afterDraw: function (instance) {
        var canvas = instance.canvas;
        var ctx = instance.ctx;
        var index;
        var line;
        var style;
        if (instance.options.linePlugin) {
            for (index = 0; index < instance.options.linePlugin.length; index++) {
                line = instance.options.linePlugin[index];
                style = line.style;
                yValue = instance.scales.y.getPixelForValue(line.y);
                ctx.lineWidth = 2;
                if (yValue) {
                    ctx.beginPath();
                    ctx.moveTo(50, yValue);
                    ctx.lineTo(canvas.width, yValue);
                    ctx.strokeStyle = style;
                    ctx.stroke();
                }
                if (line.text) {
                    ctx.fillStyle = style;
                    ctx.fillText(line.text, canvas.width - 10 - line.text.length * 5, yValue - 10);
                }
            }
            return;
        };
    }
};

/**
 * Chart JS tracker to monitor rendering
 */
app.admin.chartjs.plugin.tracker = {
    id: "trakerPlugin",
    afterRender(chart, args, options) {
        app.admin.chartjs.readyCount++;
        if (app.admin.chartjs.readyCount === app.admin.chartjs.totalCount) {
            // All charts are rendered
            app.admin.chartjs.isReady = true;
            $(document).trigger('eventReady');
        }
    }
};