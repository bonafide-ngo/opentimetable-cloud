<?
// Listen for webhooks
if (isset($_REQUEST[REQUEST_WEHBOOK])) {
    switch ($_REQUEST[REQUEST_WEHBOOK]) {
        case APP_WEBHOOK_UPGRADE:
            \App\Webhook::Upgrade(
                isset($_REQUEST['version']) ? $_REQUEST['version'] : null,
                isset($_REQUEST['signedSalsa_base64']) ? $_REQUEST['signedSalsa_base64'] : null
            );
            break;
        case APP_WEBHOOK_CLEANUP:
            \App\Webhook::Cleanup(isset($_REQUEST['signedSalsa_base64']) ? $_REQUEST['signedSalsa_base64'] : null);
            break;
        case APP_WEBHOOK_BATCH:
            \App\Webhook::Batch(isset($_REQUEST['signedUser_base64']) ? $_REQUEST['signedUser_base64'] : null);
            break;
        case APP_WEBHOOK_IPBLOCKLIST:
            \App\Webhook::IpBlocklist(isset($_REQUEST['signedSalsa_base64']) ? $_REQUEST['signedSalsa_base64'] : null);
            break;
        case APP_WEBHOOK_TRAFFIC:
            \App\Webhook::Traffic(isset($_REQUEST['signedSalsa_base64']) ? $_REQUEST['signedSalsa_base64'] : null);
            break;
        case APP_WEBHOOK_SYNC:
            \App\Webhook::Sync(isset($_REQUEST['signedSalsa_base64']) ? $_REQUEST['signedSalsa_base64'] : null);
            break;
        case APP_WEBHOOK_CUSTOM:
            // Implement on demand (i.e. see env.php)
            break;
        default:
            // Display and return http error code
            echo 'Invalid webhook request';
            http_response_code(403);
            exit();
            break;
    }
} else
    // Listen to API
    JsonRpc::Listen();
