// Init
var app = app || {};
app.admin = app.admin || {};
// Set
app.admin.system = {};
app.admin.system.phpinfo = '';

/**
 * Read if raw data exists
 */
app.admin.system.readRaw = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_Raw',
        null,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        if (result)
            $('#admin-system-cleanup').fadeIn();
    }
}
/**
 * Cleanup raw data
 */
app.admin.system.cleanup = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Delete_Raw',
        null,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        $('#admin-system-cleanup').fadeOut();
        frm.modal.success(frm.label.getStatic('success-cleanup'));
    }
}

/**
 * Read environment stats
 */
app.admin.system.readStats = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_EnvironmentStats',
        null,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        // Set and disable toggles
        $('#admin-system').find('input[name="toggle-debug"]').bootstrapToggle(result.debug ? 'on' : 'off').prop('disabled', true);
        // Set benchmark, fade because of refresh
        $('#admin-system-environment').find('[name="benchmark"]').fadeOut().text(result.benchmark).fadeIn();
        // Store phpinfo for later
        app.admin.system.phpinfo = result.phpinfo;
    }
}

/**
 * Read a Log
 */
app.admin.system.readLog = function (log) {
    log = log || null;
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_Log',
        { log: log },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        $("#modal-log").find('[name="log"]').html(result);
        // Refresh Prism highlight
        Prism.highlightAll();
        // Scroll to bottom
        $("#modal-log").modal('show').once('shown.bs.modal', function () {
            $(this).find('[name="body"]').animate({ scrollTop: $("#modal-log").find('[name="log"]').height() }, 600);
        });
    }
}

/**
 * Read Log history
 */
app.admin.system.readLogHistory = function (refresh) {
    refresh = refresh || false;
    isVisible = $("#admin-system-environment").find('[name="logs"]').is(':visible');

    if (!refresh && isVisible) {
        $("#admin-system-environment").find('[name="logs"]').slideUp();
        return;
    }

    if ((!refresh && !isVisible) || (refresh && isVisible))
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Admin.Read_LogHistory',
            null,
            onSuccess,
            null,
            null,
            null,
            { async: false });

    function onSuccess(results) {
        $("#admin-system-environment").find('[name="logs"]').empty();

        // Loop through 
        if (results.filenames.length) {
            $.each(results.filenames, function (index, filename) {
                var filesize = results.filesizes[index];
                var template = $("#admin-system-environment-template").clone();

                template.attr('filename', filename);
                template.find('[name="filename"]').text(filename);
                template.find('[name="filesize"]').text(frm.formatSize(filesize, 'B'));
                $("#admin-system-environment").find('[name="logs"]').append(template);
            });

            // Append delete
            var template = $("#admin-system-environment-template").clone();

            template.attr('filename', 'delete').addClass('bg-danger text-light');
            template.find('[name="filename"]').text(frm.label.getStatic('delete-log-history'));
            template.find('[name="filesize"]').text('');
            $("#admin-system-environment").find('[name="logs"]').append(template);

        } else
            $("#admin-system-environment").find('[name="logs"]').append($('<pre>', {
                class: 'card-text text-warning ps-2',
                text: frm.label.getStatic('no-logs')
            }));

        //Bind action items
        $("#admin-system-environment").find('[name="logs"]').slideDown();
        $("#admin-system-environment").find('[name="logs"]').find('li.list-group-item-action').once('click', function () {
            if ($(this).attr('filename') == 'delete')
                frm.modal.confirm(frm.label.getStatic('confirm-delete-logs'), app.admin.system.deleteLogHistory);
            else
                app.admin.system.readLog($(this).attr('filename'));
        });
    }
}

/**
 * Delete log history
 */
app.admin.system.deleteLogHistory = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Delete_LogHistory',
        null,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        app.admin.system.readLogHistory();
    }
}