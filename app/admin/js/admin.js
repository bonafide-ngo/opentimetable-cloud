$(document).ready(function () {
    frm.msal.ready.then(function () {
        // Security
        if (!frm.msal.isAuthenticated()) {
            if (frm.isSafari())
                // Request user interaction to trigger MSAL login in Safari
                frm.modal.confirm(frm.label.getStatic('confirm-msal-interaction'), frm.msal.login);
            else
                // Force login
                frm.msal.login();

            // Preclude admin page in background
            frm.ss.engine.load(frm.config.url.home);
            return;
        } else if (![C_MSAL_GROUP_ADMIN, C_MSAL_GROUP_STAFF].includes(frm.msal.role)) {
            frm.modal.error(frm.label.getStatic('error-admin'));
            // Preclude admin page in background
            frm.ss.engine.load(frm.config.url.home);
            return;
        }

        // Run routine
        frm.common.routine(frm.label.getStatic('admin'), null, true);

        // Dynamic labels
        $('#admin').find('[name="title-sub"]').html(frm.label.parseDynamic('admin-panel-sub', [frm.config.url.ticketingSystem]));
        // Set version
        $('#admin-version').text(frm.config.version);

        // Init toggles
        $('input[data-toggle="toggle"]').bootstrapToggle({
            onlabel: frm.label.getStatic('on'),
            offlabel: frm.label.getStatic('off')
        });

        // https://datatables.net/extensions/responsive/examples/column-control/classes.html
        $('#admin-sync-table, #admin-venue-table').on('init.dt', function () {
            app.admin.datatables.readyCount++;
            if (app.admin.datatables.readyCount === app.admin.datatables.totalCount) {
                // All datatables are rendered
                app.admin.datatables.isReady = true;
                $(document).trigger('eventReady');
            }
        });

        // Ready event
        $(document).on('eventReady', function () {
            if (app.admin.datatables.isReady && app.admin.chartjs.isReady)
                $('.accordion-collapse.show').collapse('hide');
        });

        // Init
        frm.common.sync.init(app.admin.callbackSync, true);

        /* Sync
         * ************************************************************************
         */

        // Autosync
        $('#admin-sync').find('[name="time"]').text(frm.config.autosync);
        // Sync events
        $("#admin-sync").find('button[name="syncnow"]').once('click', function () {
            frm.modal.confirm(frm.label.getStatic('confirm-sync'), app.admin.sync.create);
        });

        /* Venues
         * ************************************************************************
         */

        if (!frm.config.map.enable)
            // Hide venue
            $('#admin-venue').parent().hide();

        /* System
         * ************************************************************************
         */

        // Init Chart.js plugins
        Chart.register(app.admin.chartjs.plugin.line);
        Chart.register(app.admin.chartjs.plugin.tracker);

        // Environment events
        $("#admin-system-environment").find('button[name="refresh"]').once('click', function () {
            app.admin.system.readRaw();
            app.admin.system.readStats();
            app.admin.system.readLogHistory(true);
        });
        $("#admin-system-environment").find('button[name="log"]').once('click', function () {
            app.admin.system.readLog();
        });
        $("#admin-system-environment").find('a[name="loghistory"]').once('click', function (e) {
            e.preventDefault();
            app.admin.system.readLogHistory();
        });
        $("#admin-system-environment").find('button[name="sysinfo"]').once('click', function () {
            $("#modal-sysinfo").find('[name="body"]').html(app.admin.system.phpinfo);
            $("#modal-sysinfo").modal('show').once('hide.bs.modal', function () {
                // Clear inline CSS coming from PHP Info output
                $("#modal-sysinfo").find('[name="body"]').empty();
            });
        });
        $("#admin-system-cleanup").once('click', function () {
            frm.modal.confirm(frm.label.getStatic('confirm-cleanup'), app.admin.system.cleanup);
        });

        // Traffic events
        $("#admin-system-traffic").find('button[name="refresh"]').once('click', function () {
            app.admin.system.traffic.readStats();
        });
        $("#admin-system-traffic").find('button[name="query"]').once('click', function () {
            var ip = $("#admin-system-traffic").find('input[name="ip"]').val();
            if (!((new RegExp(C_REGEX_IP, "ig")).test(ip))) {
                frm.modal.error(frm.label.getStatic('error-ip'));
                return;
            }

            // Set title and show modal
            $("#modal-query").find('[name="title"]').text(ip);
            $("#modal-query").modal('show');

            // Query IP
            app.admin.system.traffic.readQueryIp(ip);
        });

        // Cache events
        $("#admin-system-cache").find('button[name="flush"]').once('click', function () {
            frm.modal.confirm(frm.label.getStatic('confirm-cache-flush'), app.admin.system.cache.flush);
        });
        $("#admin-system-cache").find('button[name="refresh"]').once('click', function () {
            app.admin.system.cache.readStats();
        });

        // System tab is for admin only
        if ([C_MSAL_GROUP_ADMIN].includes(frm.msal.role)) {
            // Set URLs
            $('#admin-system').find('[name="analytics"]').attr('href', frm.config.url.analytics);
            $('#admin-system').find('[name="uptime"]').attr('href', frm.config.url.uptime);
        } else {
            // Update ready event
            app.admin.chartjs.isReady = true;
            // Hide system
            $('#admin-system').parent().hide();
        }
    });
});