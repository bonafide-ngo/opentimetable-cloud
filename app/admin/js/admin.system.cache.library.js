// Init
var app = app || {};
app.admin = app.admin || {};
// Set
app.admin.system.cache = {};
app.admin.system.cache.chart = {};

/**
 * Read cache stats
 */
app.admin.system.cache.readStats = function () {
    // Add 2 chartjs
    app.admin.chartjs.totalCount++;
    app.admin.chartjs.totalCount++;

    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_CacheStats',
        null,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess(result) {
        // Set uptime (days)
        $('#admin-system-cache').find('[name="uptime"]').html(frm.label.parseDynamic('uptime-x-days', [Math.floor(result.uptime / 24 / 3600)]));

        // Hits
        if (app.admin.system.cache.chart.hits)
            app.admin.system.cache.chart.hits.destroy();
        app.admin.system.cache.chart.hits = new Chart($("#admin-system-cache-hits")[0].getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: [frm.label.getStatic('in') + ' (#)', frm.label.getStatic('out') + ' (#)'],
                datasets: [
                    {
                        data: [result.hits.in, result.hits.out],
                        backgroundColor: [
                            '#00CBEC',
                            '#103671'
                        ],
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true
            },
        });

        // Size
        if (app.admin.system.cache.chart.size)
            app.admin.system.cache.chart.size.destroy();
        app.admin.system.cache.chart.size = new Chart($("#admin-system-cache-size")[0].getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: [frm.label.getStatic('in') + ' (MB)', frm.label.getStatic('out') + ' (MB)'],
                datasets: [
                    {
                        data: [Math.round(result.size.in / 1024 / 1024), Math.round(result.size.out / 1024 / 1024)],
                        backgroundColor: [
                            '#00CBEC',
                            '#103671'
                        ],
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true
            },
        });
    }
}

/**
 * Flush cache
 */
app.admin.system.cache.flush = function () {
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Delete_Cache',
        null,
        onSuccess,
        null,
        null,
        null,
        { async: false });

    function onSuccess() {
        frm.modal.success(frm.label.getStatic('success-cache-flush'));

        // Refresh stats
        $('#modal-success').once('hidden.bs.modal', function (e) {
            app.admin.system.cache.readStats();
        });
    }
}
