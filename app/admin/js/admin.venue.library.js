// Init
var app = app || {};
app.admin = app.admin || {};
// Set
app.admin.venue = {};

/**
 * Read venue all
 */
app.admin.venue.read = function () {
    // Add datatable
    app.admin.datatables.totalCount++;

    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_Location_All',
        { syncId: frm.common.sync.id },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(results) {
        if ($.fn.dataTable.isDataTable('#admin-venue-table')) {
            $('#admin-venue-table').DataTable().clear().rows.add(results).draw();
        } else
            $('#admin-venue-table').DataTable({
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
                order: [[0, 'asc']],
                buttons: [{
                    extend: 'csv',
                    title: 'opentimetable_cloud_venue_' + moment().unix()
                }],
                data: results,
                columns: [
                    { data: 'lct_code' },
                    { data: 'lct_latitude' },
                    { data: 'lct_longitude' },
                    { data: 'lct_update_timestamp' },
                    { data: 'lct_update_by' }
                ],
                order: [[0, 'asc']],
                columnDefs: [
                    {
                        className: 'dt-body-left',
                        render: function (data, type, row) {
                            if (row.lct_latitude && row.lct_longitude)
                                return $('<a>', {
                                    name: 'map',
                                    href: '#',
                                    text: data
                                })[0].outerHTML;
                            else
                                return data;
                        },
                        targets: 0
                    },
                    {
                        className: 'dt-body-right',
                        render: function (data, type, row) {
                            return $('<div>', { class: 'd-flex align-items-center w-100' }).append(
                                $('<div>', {
                                    text: data,
                                    class: 'me-2 flex-grow-1',
                                    contenteditable: true
                                }), $('<i>', {
                                    name: 'edit',
                                    class: 'fa-solid fa-pen-to-square fs-5 text-primary',
                                    title: frm.label.getStatic('edit')
                                })
                            )[0].outerHTML;
                        },
                        targets: [1, 2]
                    },
                    {
                        render: function (data, type, row) {
                            if (type === 'sort')
                                return data;
                            else
                                return frm.common.formatTimestamp(data);
                        },
                        className: 'dt-body-center',
                        targets: 3
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
                        targets: 4
                    }
                ],
                language: frm.label.dictionary.plugin.datatable,
                drawCallback: drawCallback
            }).on('responsive-display', function (e, datatable, row, showHide, update) {
                drawCallback();
            }).buttons().container().appendTo('#admin-venue [name="csv"]');


        function drawCallback() {
            $('#admin-venue-table').find('[name="map"]').once('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                // Get tr
                var tr = $(this).closest('tr');

                // Query datatable
                var datatable = $('#admin-venue-table').DataTable();
                var data = datatable.row(tr).data();

                // Open map
                frm.map.listen(data.lct_latitude, data.lct_longitude, data.vnx_code, data.vnx_name);
            });

            $('#admin-venue-table').find('[contenteditable="true"]').once('keydown', function (e) {
                if (e.key === 'Enter') {
                    // Prevent line break in contenteditable
                    e.preventDefault();
                    // Trigger blur to re-use same handler
                    $(this).blur();
                }
            }).once('blur', function (e) {
                // Get tr, td, value 
                var tr = $(this).closest('tr');
                var td = $(this).closest('td');
                var value = $(this).text().trim();

                // Query datatable
                var datatable = $('#admin-venue-table').DataTable();
                var data = datatable.row(tr).data();
                var cell = datatable.cell(td);
                var columnIndex = cell.index().column;
                var rowIndex = cell.index().row;

                // Init params
                var params = {
                    locationId: data.lct_id,
                    latitude: data.lct_latitude,
                    longitude: data.lct_longitude
                };

                // Set param
                switch (columnIndex) {
                    case 1: // Latitude, see datatable
                        if (value != (data.lct_latitude || '')) {
                            params.latitude = value;
                            onUpdate();
                        }
                        break;
                    case 2: // Longitude, see datatable
                        if (value != (data.lct_longitude || '')) {
                            params.longitude = value;
                            onUpdate();
                        }
                        break;
                }
                function onUpdate() {
                    frm.ajax.jsonrpc.request(
                        frm.config.url.api,
                        'App.Admin.Update_Location',
                        params,
                        onSuccess);
                }

                function onSuccess() {
                    // Update timestamp and user on the fly without refreshing the datatable
                    datatable.cell({ row: rowIndex, column: 1 }).data(params.latitude);
                    datatable.cell({ row: rowIndex, column: 2 }).data(params.longitude);
                    datatable.cell({ row: rowIndex, column: 3 }).data(moment().unix());
                    datatable.cell({ row: rowIndex, column: 4 }).data(frm.msal.decodedIdToken.email);
                    // Redraw datatable row only
                    datatable.row(rowIndex).invalidate().draw(false);
                }
            });

            $('#admin-venue-table').find('[name="edit"]').once('click', function (e) {
                $(this).siblings('div').trigger('focus');
            });
        }
    }
};