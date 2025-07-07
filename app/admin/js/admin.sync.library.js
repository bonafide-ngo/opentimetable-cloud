// Init
var app = app || {};
app.admin = app.admin || {};
// Set
app.admin.sync = {};

/**
 * Read sync all
 */
app.admin.sync.read = function (checkPending) {
    checkPending = checkPending || false;

    // Add datatable
    app.admin.datatables.totalCount++;

    if (checkPending)
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Admin.Update_Sync_Pending',
            null,
            function (result) {
                readSyncAll();

                // Run pending interval if any
                if (result && !window.pendingInterval)
                    // Monitor the change in the pending status
                    window.pendingInterval = window.setInterval(app.admin.sync.intervalCallback, frm.config.validity.intervalPending);
            });
    else
        readSyncAll();

    function readSyncAll() {
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Admin.Read_Sync_All',
            null,
            onSuccess,
            null,
            null,
            null,
            { async: false });

        function onSuccess(results) {
            if ($.fn.dataTable.isDataTable('#admin-sync-table')) {
                $('#admin-sync-table').DataTable().clear().rows.add(results).draw();
            } else
                $('#admin-sync-table').DataTable({
                    responsive: {
                        breakpoints: [
                            { name: 'xxl', width: Infinity },
                            { name: 'xl', width: 1200 },
                            { name: 'lg', width: 992 },
                            { name: 'md', width: 768 },
                            { name: 'sm', width: 576 },
                            { name: 'xs', width: 480 }
                        ]
                    },
                    order: [[0, 'desc']],
                    buttons: [{
                        extend: 'csv',
                        title: 'opentimetable_cloud_sync_' + moment().unix()
                    }],
                    data: results,
                    columns: [
                        { data: 'snc_id' },
                        { data: 'snc_status' },
                        { data: 'snc_draft' },
                        { data: 'snc_status' },
                        { data: 'snc_create_timestamp' },
                        { data: 'snc_create_by' },
                        { data: 'snc_live_from_timestamp' },
                        { data: 'snc_live_to_timestamp' },
                        { data: 'snc_live_by' },
                        { data: 'btc_id' },
                        { data: 'btc_end_timestamp' }
                    ],
                    order: [[0, 'desc']],
                    columnDefs: [
                        {
                            className: 'dt-body-left',
                            targets: 0
                        },
                        {
                            render: function (data, type, row) {
                                switch (data) {
                                    case C_DB_SYNC_STATUS_SUCCESS:
                                        if (row.snc_active)
                                            return $('<span>', {
                                                class: "badge bg-danger",
                                                text: frm.label.getStatic('active')
                                            })[0].outerHTML;
                                        else if (row.snc_live_to_timestamp)
                                            return $('<span>', {
                                                class: "badge bg-dark",
                                                text: frm.label.getStatic('archive')
                                            })[0].outerHTML;
                                        else
                                            return $('<span>', {
                                                class: "badge bg-success",
                                                text: frm.label.getStatic('sync')
                                            })[0].outerHTML;
                                        break;
                                    case C_DB_SYNC_STATUS_ERROR:
                                        return $('<span>', {
                                            class: "badge bg-warning",
                                            text: frm.label.getStatic('error')
                                        })[0].outerHTML;
                                        break;
                                    case C_DB_SYNC_STATUS_PENDING:
                                        return $('<span>', {
                                            class: "badge bg-info",
                                            text: frm.label.getStatic('pending')
                                        })[0].outerHTML;
                                        break;
                                }
                            },
                            className: 'dt-body-center',
                            targets: 1
                        },
                        {
                            render: function (data, type, row) {
                                if (row.snc_status == C_DB_SYNC_STATUS_SUCCESS)
                                    return $('<input>', {
                                        name: 'draft',
                                        class: 'form-check-input',
                                        type: 'radio',
                                        checked: data ? true : false,
                                        sync: row.snc_id
                                    })[0].outerHTML;
                                else
                                    return '';
                            },
                            className: 'dt-body-center',
                            targets: 2
                        },
                        {
                            render: function (data, type, row) {
                                switch (data) {
                                    case C_DB_SYNC_STATUS_SUCCESS:
                                        if (row.snc_active)
                                            return $('<button>', {
                                                class: "btn btn-sm btn-outline-danger",
                                                name: 'preview',
                                                text: frm.label.getStatic('preview'),
                                                sync: ''
                                            })[0].outerHTML;
                                        else if (row.snc_live_to_timestamp)
                                            return $('<button>', {
                                                class: "btn btn-sm btn-outline-dark",
                                                name: 'rollback',
                                                text: frm.label.getStatic('rollback'),
                                                sync: row.snc_id
                                            })[0].outerHTML + ' ' + $('<button>', {
                                                class: "btn btn-sm btn-outline-secondary",
                                                name: 'preview',
                                                text: frm.label.getStatic('preview'),
                                                sync: row.snc_id
                                            })[0].outerHTML;
                                        else
                                            return $('<button>', {
                                                class: "btn btn-sm btn-outline-success",
                                                name: 'publish',
                                                text: frm.label.getStatic('publish'),
                                                sync: row.snc_id
                                            })[0].outerHTML + ' ' + $('<button>', {
                                                class: "btn btn-sm btn-outline-secondary",
                                                name: 'preview',
                                                text: frm.label.getStatic('preview'),
                                                sync: row.snc_id
                                            })[0].outerHTML;
                                        break;
                                    default:
                                        return '';
                                        break;
                                }
                            },
                            className: 'dt-body-right',
                            targets: 3
                        },
                        {
                            render: function (data, type, row) {
                                if (type === 'sort')
                                    return data;
                                else
                                    return frm.common.formatTimestamp(data);
                            },
                            className: 'dt-body-center',
                            targets: 4
                        },
                        {
                            render: function (data, type, row) {
                                if ((new RegExp(C_REGEX_EMAIL, "ig")).test(data))
                                    return $('<a>', {
                                        href: 'mailto:' + data,
                                        title: data,
                                        text: data
                                    })[0].outerHTML;
                                else
                                    return data;
                            },
                            className: 'dt-body-left',
                            targets: 5
                        },
                        {
                            render: function (data, type, row) {
                                if (type === 'sort')
                                    return data;
                                else
                                    return data ? frm.common.formatTimestamp(data) : '';
                            },
                            className: 'dt-body-center',
                            targets: 6
                        },
                        {
                            render: function (data, type, row) {
                                if (type === 'sort')
                                    return data;
                                else
                                    return data ? frm.common.formatTimestamp(data) : '';
                            },
                            className: 'dt-body-center',
                            targets: 7
                        },
                        {
                            render: function (data, type, row) {
                                if ((new RegExp(C_REGEX_EMAIL, "ig")).test(data))
                                    return $('<a>', {
                                        href: 'mailto:' + data,
                                        title: data,
                                        text: data
                                    })[0].outerHTML;
                                else
                                    return data;
                            },
                            className: 'dt-body-left',
                            targets: 8
                        },
                        {
                            className: 'dt-body-left',
                            targets: 9,
                            visible: [C_MSAL_GROUP_ADMIN].includes(frm.msal.role)
                        },
                        {
                            render: function (data, type, row) {
                                if (!data) {
                                    if (type === 'sort')
                                        return data;
                                    else
                                        return frm.label.getStatic('n-a');
                                } else {
                                    // Calculate duration in seconds
                                    var durationS = data - row.btc_start_timestamp;

                                    if (type === 'sort')
                                        return durationS;
                                    else {
                                        var durationObj = moment.duration(durationS, 'seconds');
                                        return moment.utc(durationObj.asMilliseconds()).format('mm:ss');
                                    }
                                }
                            },
                            className: 'dt-body-center',
                            targets: 10,
                            visible: [C_MSAL_GROUP_ADMIN].includes(frm.msal.role)
                        }
                    ],
                    language: frm.label.dictionary.plugin.datatable,
                    drawCallback: drawCallback
                }).on('responsive-display', function (e, datatable, row, showHide, update) {
                    drawCallback();
                }).buttons().container().appendTo('#admin-sync [name="csv"]');

            function drawCallback() {
                // Bind events
                $('#admin-sync-table').find('button[name="preview"]').once('click', function () {
                    if ($(this).attr('sync'))
                        // Set sync cookie
                        frm.crypto.setCookie(frm.config.cookie.property.session.sync, parseInt($(this).attr('sync')));
                    else
                        // Remove cookie
                        frm.crypto.removeCookie(frm.config.cookie.property.session.sync);

                    frm.ss.engine.load(frm.config.url.home);
                });
                $('#admin-sync-table').find('button[name="publish"]').once('click', function () {
                    // Publish sync
                    app.admin.sync.publish(parseInt($(this).attr('sync')));
                });
                $('#admin-sync-table').find('button[name="rollback"]').once('click', function () {
                    // Rollback sync
                    app.admin.sync.rollback(parseInt($(this).attr('sync')));
                });
                $('#admin-sync-table').find('input[name="draft"]').once('change', function () {
                    // Set draft
                    app.admin.sync.draft(parseInt($(this).attr('sync')));
                });
            }
        }
    }
}

/**
 * Create sync
 */
app.admin.sync.create = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Create_Sync',
        null,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess() {
        frm.modal.information(frm.label.getStatic('success-sync-start'));
        $('#modal-information').once('hidden.bs.modal', function (e) {
            // Refresh sync table
            app.admin.sync.read();
        });

        // Monitor the change in the pending status
        window.pendingInterval = window.setInterval(app.admin.sync.intervalCallback, frm.config.validity.intervalPending);
    }
}

/**
 * Run interval callback 
 */
app.admin.sync.intervalCallback = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_Sync_Pending',
        null,
        function (result) {
            // No longer pending?
            if (!result) {
                window.clearInterval(window.pendingInterval);
                frm.modal.information(frm.label.getStatic('information-sync'));

                // Refresh stats
                $('#modal-information').once('hidden.bs.modal', function (e) {
                    // Refresh sync table if user is still in the admin entity
                    if (app.admin)
                        app.admin.sync.read();
                });
            }
        },
        null,
        function (error) {
            window.clearInterval(window.pendingInterval);
            frm.modal.error(error.data ? error.data : error.message);

            // Refresh stats
            $('#modal-error').once('hidden.bs.modal', function (e) {
                // Refresh sync table if user is still in the admin entity
                if (app.admin)
                    app.admin.sync.read();
            });
        });
}

/**
 * Update sync publish
 * 
 * @param {*} syncId 
 */
app.admin.sync.publish = function (syncId) {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Update_Sync_Publish',
        { syncId: syncId },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess() {
        frm.modal.success(frm.label.getStatic('success-publish'));

        // Refresh stats
        $('#modal-success').once('hidden.bs.modal', function (e) {
            // Refresh sync table
            app.admin.sync.read();
        });
    }
}

/**
 * Update sync rollback
 * 
 * @param {*} syncId 
 */
app.admin.sync.rollback = function (syncId) {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Create_Sync_Rollback',
        { syncId: syncId },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess() {
        frm.modal.success(frm.label.getStatic('success-rollback'));

        // Refresh stats
        $('#modal-success').once('hidden.bs.modal', function (e) {
            // Refresh sync table
            app.admin.sync.read();
        });
    }
}

/**
 * Update sync draft
 * 
 * @param {*} syncId 
 */
app.admin.sync.draft = function (syncId) {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Update_Sync_Draft',
        { syncId: syncId });
}