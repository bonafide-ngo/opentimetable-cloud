// Init
var frm = frm || {};

/*******************************************************************************
Framework - MSAL
*******************************************************************************/

frm.msal = {};
frm.msal.override = {};
frm.msal.graphAPI = {};
frm.msal.graphAPI.promise = {};
frm.msal.graphAPI.await = {};
frm.msal.decodedIdToken = {};
frm.msal.instance = null;
frm.msal.ready = null;
frm.msal.role = null;
frm.msal.isAccessToken = false;

/**
 * Overridable methods
 * 
 * @param {*} isLogin 
 */
frm.msal.override.init = async function (isLogin) {
    isLogin = isLogin || false
}
frm.msal.override.logout = function () {
}

/**
 * Handle msal exception
 * 
 * @param {*} origin 
 * @param {*} error 
 */
frm.msal.exception = function (origin, error) {
    console.error(origin, error);
    frm.modal.exception(frm.label.parseDynamic('exception-msal', [frm.config.email.timetable[0], JSON.stringify([origin, error], true, 4)]));
}

/**
 * Start the MSAL public client app
 */
frm.msal.setPublicClientApplication = async function () {
    try {
        // Set the instance, sync
        frm.msal.instance = new msal.PublicClientApplication(frm.config.msal.instance);
        // Initialise the instance, async
        frm.msal.ready = frm.msal.instance.initialize()
            .then(async () => {
                // Handle promise and response
                return await frm.msal.instance.handleRedirectPromise();
            })
            .then(async response => {
                return await frm.msal.handleResponse(response);
            })
            .catch(e => {
                frm.msal.exception('frm.msal.setPublicClientApplication', e);
            });
    } catch (e) {
        frm.msal.exception('frm.msal.setPublicClientApplication', e);
    }
}

/**
 * Handle MSAL response
 * 
 * @param {*} response 
 */
frm.msal.handleResponse = async function (response, isLogin) {
    isLogin = isLogin || false;

    // Simulate spinner buying time
    await frm.spinner.start(true);

    if (response?.account) {
        // Decode the id token
        const decodedIdToken = frm.msal.decodeIdToken(response.idToken);
        // Store for later
        frm.msal.decodedIdToken = decodedIdToken;
        // Set cookie for APIs
        frm.crypto.setCookie(frm.config.cookie.property.msal.id, encodeURIComponent(response.idToken));
        frm.crypto.setCookie(frm.config.cookie.property.msal.access, encodeURIComponent(response.accessToken));
        // Set active MSAL account
        frm.msal.instance.setActiveAccount(response.account);

        // Call override init method
        await frm.msal.override.init(isLogin);
    } else {
        // Attempt to get active account
        const activeAccount = frm.msal.instance.getActiveAccount();
        if (!activeAccount) {
            // Get all accounts
            const accounts = frm.msal.instance.getAllAccounts();
            if (accounts && accounts.length) {
                // Set active account
                frm.msal.instance.setActiveAccount(accounts[0]);
                // Refresh id/access tokens
                await frm.msal.getAccessToken();
                // Call override init method
                await frm.msal.override.init(isLogin);
            }
        }

        // Call override init method
        await frm.msal.override.init(isLogin);
    }
    // Simulate spinner buying time
    frm.spinner.stop();
}

/**
 * Login
 */
frm.msal.login = function () {
    if (!frm.msal.instance)
        return false;

    // Handle login via Microsoft popup and implicit redirect
    frm.msal.instance.loginPopup({ scopes: frm.config.msal.scopes })
        .then(async response => {
            await frm.msal.handleResponse(response, true);
        })
        .catch(e => {
            switch (e.errorCode) {
                case 'interaction_in_progress':
                    frm.modal.information(frm.label.getStatic('information-masal-interaction-in-progress'));
                    break;
                case 'popup_window_error':
                    frm.modal.information(frm.label.getStatic('information-masal-interaction-in-progress'));
                    break;
                case 'hash_empty_error':
                    // Nothing happened, just reload the redirect uri
                    window.location.href = frm.config.msal.instance.auth.redirectUri;
                    break;
                case 'user_cancelled':
                    frm.ss.engine.load(frm.config.url.home);
                    break;
                case 'no_account_error': // Microsoft had a glitch finding the account
                case 'block_nested_popups': // Request blocked form within a popup or iframe
                    // Reload with no history
                    window.location.reload();
                    break;
                default:
                    frm.msal.exception('frm.msal.login', e);
                    break;
            }
        });
}

/**
 * Logout
 * 
 * @param {*} intentional 
 */
frm.msal.logout = function (intentional) {
    intentional = intentional || false;

    if (!frm.msal.instance)
        return;

    // Handle logout via Microsoft popup
    frm.msal.instance.logoutPopup({
        account: frm.msal.instance.getActiveAccount()
    }).then(() => {
        // Check if the user dismissed the logout instead
        if (!frm.msal.isAuthenticated()) {
            // Remove cookie for APIs
            frm.crypto.removeCookie(frm.config.cookie.property.msal.id);
            frm.crypto.removeCookie(frm.config.cookie.property.msal.access);
            // Call override logout method
            frm.msal.override.logout(intentional);
        }
    }).catch(e => {
        frm.msal.exception('frm.msal.logout', e);
    });
}

/**
 * Verify user is authenticated
 * 
 * @returns 
 */
frm.msal.isAuthenticated = function () {
    if (!frm.msal.instance)
        return false;

    var msalAccounts = frm.msal.instance.getAllAccounts();
    return msalAccounts.length ? true : false;
}

/**
 * Get access token
 * 
 * @returns 
 */
frm.msal.getAccessToken = async function () {
    if (!frm.msal.instance)
        return null;

    // Wait for the pending access token flag
    await new Promise(resolve => {
        const check = () => {
            if (!frm.msal.isAccessToken) {
                resolve();
            } else {
                setTimeout(check, 100);
            }
        };
        check();
    });

    // Set pending access token flag
    frm.msal.isAccessToken = true;

    // Attempt to retrieve silently first
    return await frm.msal.instance.acquireTokenSilent({
        account: frm.msal.instance.getActiveAccount(),
        scopes: frm.config.msal.scopes
    })
        .then((response) => {
            // Set active account in case it changed
            frm.msal.instance.setActiveAccount(response.account);
            // Decode the id token
            const decodedIdToken = frm.msal.decodeIdToken(response.idToken);
            // Store for later
            frm.msal.decodedIdToken = decodedIdToken;
            // Set cookie for APIs
            frm.crypto.setCookie(frm.config.cookie.property.msal.id, encodeURIComponent(response.idToken));
            frm.crypto.setCookie(frm.config.cookie.property.msal.access, encodeURIComponent(response.accessToken));
            return response.accessToken;
        })
        .catch(async (e) => {
            if (e instanceof msal.InteractionRequiredAuthError) {
                // Fallback to interactive method if silent acquisition fails
                return await frm.msal.instance.acquireTokenPopup({
                    account: frm.msal.instance.getActiveAccount(),
                    scopes: frm.config.msal.scopes
                })
                    .then((response) => {
                        // Set active account in case it changed
                        frm.msal.instance.setActiveAccount(response.account);
                        // Decode the id token
                        const decodedIdToken = frm.msal.decodeIdToken(response.idToken);
                        // Store for later
                        frm.msal.decodedIdToken = decodedIdToken;
                        // Set cookie for APIs
                        frm.crypto.setCookie(frm.config.cookie.property.msal.id, encodeURIComponent(response.idToken));
                        frm.crypto.setCookie(frm.config.cookie.property.msal.access, encodeURIComponent(response.accessToken));
                        return response.accessToken;
                    })
                    .catch(e => {
                        switch (e.errorCode) {
                            case 'interaction_in_progress':
                            case 'popup_window_error':
                            case 'hash_empty_error':
                                // Ignore silently as login may be running too
                                break;
                            case 'user_cancelled':
                                frm.ss.engine.load(frm.config.url.home);
                                break;
                            case 'no_account_error': // Microsoft had a glitch finding the account
                            case 'block_nested_popups': // Request blocked form within a popup or iframe
                                // Reload with no history
                                window.location.reload();
                                break;
                            default:
                                frm.msal.exception('frm.msal.getAccessToken', e);
                                break;
                        }
                        return null;
                    });
            } else {
                frm.msal.exception('frm.msal.getAccessToken', e);
                return null;
            }
        })
        .finally(() => {
            // Reset pending access token flag
            frm.msal.isAccessToken = false;
        });
}

/**
 * Decode the JWT id token
 * @param {*} idToken 
 * @returns 
 */
frm.msal.decodeIdToken = function (idToken) {
    try {
        // Get the payload part[1] of the token
        const base64Url = idToken.split(".")[1];
        const base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
        const jsonPayload = decodeURIComponent(
            atob(base64)
                .split("")
                .map((c) => "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2))
                .join("")
        );
        return JSON.parse(jsonPayload);
    } catch (e) {
        frm.msal.exception('frm.msal.decodeIdToken', e);
        return null;
    }
}

/**
 * Get the user role
 * 
 * @returns 
 */
frm.msal.initRole = async function () {
    if (frm.msal.role)
        return frm.msal.role;

    // Re-Pre-set
    frm.msal.role = null;

    // Get user groups Ids
    const userGroupsIds = await frm.msal.getUserGroupsIds();
    if (userGroupsIds.length)
        // Get the highest role of a user, since they could be in multiple msal groups
        frm.config.msal.groupsPriority.some(role => {
            if (userGroupsIds.some(group => frm.config.msal.groups[role].includes(group))) {
                frm.msal.role = role;
                // stops .some()
                return true;
            } else
                // continues .some()
                return false;
        });

    return frm.msal.role;
}

/**
 * Get the user groups ids by id token
 * @returns 
 */
frm.msal.getUserGroupsIds = async function () {
    var groups = [];

    if (!frm.msal.instance)
        return groups;

    // Get active account
    const activeAccount = frm.msal.instance.getActiveAccount();
    // Get groups via id token claims if present, otherwise query graph API
    groups = activeAccount?.idTokenClaims?.groups || [];
    if (groups.length)
        return groups;
    else
        // Query graph api to read groups
        return await frm.msal.graphAPI.await.memberOf();
}

/**
 * Query Graph API to get the user groups details
 */
frm.msal.graphAPI.await.memberOf = async function () {
    if (!frm.msal.instance)
        return [];

    try {
        const accessToken = await frm.msal.getAccessToken();
        const stream = await fetch(frm.config.msal.url.memberOf, {
            headers: {
                Authorization: `Bearer ${accessToken}`,
            },
        });

        // Parse json stream
        const response = await stream.json();
        // Map group ids
        return response.value.map(group => group.id);
    } catch (e) {
        frm.msal.exception('frm.msal.graphAPI.await.memberOf', e);
        return [];
    }
}

/**
 * Query Graph API to get the user groups details
 * N.B. For debugging purpose only, as (essential) groups are retrieved via id/access token
 */
frm.msal.graphAPI.promise.memberOf = function () {
    if (!frm.msal.instance)
        return null;

    frm.msal.getAccessToken()
        .then((accessToken) => {
            // Call Microsoft Graph API to get user groups
            fetch(frm.config.msal.url.memberOf, {
                headers: {
                    Authorization: `Bearer ${accessToken}`,
                },
            })
                .then((stream) => stream.json())
                .then((response) => {
                    console.log(response);
                })
                .catch(e => {
                    frm.msal.exception('frm.msal.graphAPI.memberOf', e);
                });
        })
        .catch(e => {
            frm.msal.exception('frm.msal.graphAPI.memberOf', e);
        });
}