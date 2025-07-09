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
frm.msal.accountId = null;

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
 * @param {*} origin 
 * @param {*} code 
 * @param {*} message 
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
            .then(() => {
                // Handle promise and response
                return frm.msal.instance.handleRedirectPromise();
            })
            .then(response => {
                return frm.msal.handleResponse(response);
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

    if (response !== null) {
        // Decode the id token
        const decodedIdToken = frm.msal.decodeIdToken(response.idToken);
        // Store for later
        frm.msal.accountId = response.account.homeAccountId;
        frm.msal.decodedIdToken = decodedIdToken;
        // Set cookie
        frm.crypto.setCookie(frm.config.cookie.property.msal.id, encodeURIComponent(response.idToken));
        frm.crypto.setCookie(frm.config.cookie.property.msal.access, encodeURIComponent(response.accessToken));
        // Set active MSAL account
        frm.msal.instance.setActiveAccount(response.account);
        // Call override init method
        await frm.msal.override.init(isLogin);
    } else {
        // Read all authenticated accounts
        const currentAccounts = frm.msal.instance.getAllAccounts();
        if (currentAccounts && currentAccounts.length) {
            // Store for later
            frm.msal.accountId = currentAccounts[0].homeAccountId;
            // Set active MSAL account
            frm.msal.instance.setActiveAccount(currentAccounts[0]);
            // Refresh id/access tokens
            await frm.msal.getAccessToken();
            // Call override init method
            await frm.msal.override.init(isLogin);
        } else
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
        .then(response => frm.msal.handleResponse(response, true))
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
        account: frm.msal.instance.getAccountByHomeId(frm.msal.accountId)
    }).then(() => {
        // Check if the user dismissed the logout instead
        if (!frm.msal.isAuthenticated()) {
            // Remove cookie
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
    if (!frm.msal.instance || !frm.msal.accountId)
        return null;

    // Extend scopes with authenticated account
    const scopes = $.extend(true, { account: frm.msal.instance.getAccountByHomeId(frm.msal.accountId) }, { scopes: frm.config.msal.scopes });

    // Attempt to retrieve silently first
    return await frm.msal.instance.acquireTokenSilent(scopes)
        .then((response) => {
            // Decode the id token
            const decodedIdToken = frm.msal.decodeIdToken(response.idToken);
            // Store for later
            frm.msal.decodedIdToken = decodedIdToken;
            // Set cookie
            frm.crypto.setCookie(frm.config.cookie.property.msal.id, encodeURIComponent(response.idToken));
            frm.crypto.setCookie(frm.config.cookie.property.msal.access, encodeURIComponent(response.accessToken));
            return response.accessToken;
        })
        .catch(async (e) => {
            if (e instanceof msal.InteractionRequiredAuthError) {
                // Fallback to interactive method if silent acquisition fails
                return await frm.msal.instance.acquireTokenPopup(scopes)
                    .then((response) => {
                        // Decode the id token
                        const decodedIdToken = frm.msal.decodeIdToken(response.idToken);
                        // Store for later
                        frm.msal.decodedIdToken = decodedIdToken;
                        // Set cookie
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
        frm.config.msal.groupsPriority.forEach(role => {
            if (userGroupsIds.includes(frm.config.msal.groups[role]))
                frm.msal.role = role;
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

    const accounts = frm.msal.instance.getAllAccounts();
    if (!accounts || !accounts.length)
        return groups;

    // Get the id token
    const idToken = accounts[0].idToken;
    if (!idToken)
        return groups;

    // Decode the id token
    const decodedIdToken = frm.msal.decodeIdToken(idToken);
    if (!decodedIdToken)
        return groups;

    // Attempt extracting groups from the token
    groups = decodedIdToken.groups || [];
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
 * N.B. For debugging as (essential) groups are retrieved via id/access token
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