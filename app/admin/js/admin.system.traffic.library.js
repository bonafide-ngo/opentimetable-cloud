// Init
var app = app || {};
app.admin = app.admin || {};
// Set
app.admin.system.traffic = {};
app.admin.system.traffic.chart = {};

/**
 * Read traffic stats
 */
app.admin.system.traffic.readStats = function () {
    // Add 2 chartjs
    app.admin.chartjs.totalCount++;
    app.admin.chartjs.totalCount++;

    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_TrafficStats',
        null,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        // Set average time (s)
        $('#admin-system-traffic').find('[name="avgtime"]').html(frm.label.parseDynamic('average-time-x-s', [Math.round(result.avgTime / 1000 * 10) / 10]));

        // Handle top
        var labelTop = [];
        var data = [];

        $.each(result.top, function (index, row) {
            labelTop.push(row.trf_ip);
            data.push(row.trf_count);
        });

        if (app.admin.system.traffic.chart.top)
            app.admin.system.traffic.chart.top.destroy();
        app.admin.system.traffic.chart.top = new Chart($("#admin-system-traffic-top")[0].getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelTop,
                datasets: [{
                    label: frm.label.getStatic('ip') + ' (#)',
                    data: data,
                    backgroundColor: '#25788b',
                    fill: true,
                    borderWidth: 0
                }]
            },
            options: {
                linePlugin: [{
                    y: result.avgHits,
                    style: "#25788b",
                    text: frm.label.getStatic('average')
                }],
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            color: '#abb6c2'
                        },
                        grid: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false,
                            drawTicks: true,
                            borderColor: '#abb6c2',
                            color: '#abb6c2'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#abb6c2',
                            stepSize: 1
                        },
                        grid: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false,
                            drawTicks: true,
                            borderColor: '#abb6c2',
                            color: '#abb6c2'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // Handle size
        var labelSize = [];
        var dataIn = [];
        var dataOut = [];
        $.each(result.top, function (index, row) {
            labelSize.push(row.trf_ip);
            dataIn.push(Math.round(row.trf_size_in / 1024)); // KB
            dataOut.push(Math.round(row.trf_size_out / 1024)); // KB
        });

        if (app.admin.system.traffic.chart.size)
            app.admin.system.traffic.chart.size.destroy();
        app.admin.system.traffic.chart.size = new Chart($("#admin-system-traffic-size")[0].getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelSize,
                datasets: [{
                    label: frm.label.getStatic('ip-in') + ' (KB)',
                    data: dataIn,
                    backgroundColor: '#00CBEC',
                    fill: true,
                    borderWidth: 0
                }, {
                    label: frm.label.getStatic('ip-out') + ' (KB)',
                    data: dataOut,
                    backgroundColor: '#103671',
                    fill: true,
                    borderWidth: 0
                }]
            },
            options: {
                linePlugin: [{
                    y: result.avgSizeIn,
                    style: "#00CBEC",
                    text: frm.label.getStatic('average-in')
                }, {
                    y: result.avgSizeOut,
                    style: "#103671",
                    text: frm.label.getStatic('average-out')
                }],
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            color: '#abb6c2'
                        },
                        grid: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false,
                            drawTicks: true,
                            borderColor: '#abb6c2',
                            color: '#abb6c2'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#abb6c2',
                            stepSize: 1
                        },
                        grid: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false,
                            drawTicks: true,
                            borderColor: '#abb6c2',
                            color: '#abb6c2'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

/**
 * Query IP
 * 
 * @param {*} ip 
 */
app.admin.system.traffic.readQueryIp = function (ip) {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_QueryIp',
        { ip: ip },
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        // Set country and city if available
        $('#modal-query').find('[name="isocode"]').text(result.geoIsoCode);
        $('#modal-query').find('[name="name"]').text(result.geoName);
        // Set information
        $('#modal-query').find('[name="information"]').html(result.information);
        // Reset blocklists
        $('#modal-query').find('[name="blocklists"]').empty();
        // Append failed
        $.each(result.blocklists.Failed, function (index, row) {
            $('#modal-query').find('[name="blocklists"]').append($('<span>', {
                class: "badge bg-danger fs-6 rounded-8 me-1 mb-1",
                text: row.Name
            }));
        });
        // Append warnings
        $.each(result.blocklists.Warning, function (index, row) {
            $('#modal-query').find('[name="blocklists"]').append($('<span>', {
                class: "badge bg-warning text-light fs-6 rounded-8 me-1 mb-1",
                text: row.Name
            }));
        });
        // Append passed
        $.each(result.blocklists.Passed, function (index, row) {
            $('#modal-query').find('[name="blocklists"]').append($('<span>', {
                class: "badge bg-success fs-6 rounded-8 me-1 mb-1",
                text: row.Name
            }));
        });
        // Set block toggle
        $('#modal-query').find('input[name="block"]').prop('disabled', false).off('change').bootstrapToggle('destroy').bootstrapToggle(result.isBlocked ? 'on' : 'off').once("change", function () {
            // Update IP block
            app.admin.system.traffic.updateBlock($("#admin-system-traffic").find('input[name="ip"]').val(), $(this).is(':checked'));
        });

    }
}

/**
 * Update IP block
 * 
 * @param {*} ip 
 * @param {*} block 
 */
app.admin.system.traffic.updateBlock = function (ip, block) {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Update_IpBlock',
        {
            ip: ip,
            block: block
        },
        onSuccess,
        null,
        onError,
        null,
        { async: false });

    function onSuccess() {
        frm.modal.success(frm.label.getStatic('success-update'));
    }

    function onError(error) {
        $('#modal-query').find('input[name="block"]').off('change').bootstrapToggle('on').prop('disabled', true);
        frm.modal.error(frm.ajax.formatError(error));
    }
}
