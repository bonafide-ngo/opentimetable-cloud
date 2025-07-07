// Init
var app = {};
// Set
app.home = {};

/**
 * Handle general timetable
 */
app.home.generalTimetable = function () {
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
        // Check if general timetable is not enabled and not authenticated
        if (!result && (!frm.msal.isAuthenticated() || ![C_MSAL_GROUP_ADMIN, C_MSAL_GROUP_STAFF, C_MSAL_GROUP_REVIEWER].includes(frm.msal.role)))
            $('#home-menu').hide();
    };
};

/**
 * Handle general notice
 */
app.home.generalNotice = function () {
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
            $('#home-notice').html(frm.bbcodeToHTML(responseText[0].result)).parent().show();
    });
};