/*******************************************************************************
Framework - Constant
*******************************************************************************/

// Ajax
const C_AJAX_SUCCESS = "success";

// Regex
// https://stackoverflow.com/questions/6038061/regular-expression-to-find-urls-within-a-string?page=1&tab=scoredesc#tab-top
const C_REGEX_URL = "^https?:\\/\\/(www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b([-a-zA-Z0-9()@:%_\\+.~#?&//=]*)$";
// https://stackoverflow.com/questions/4098415/use-regex-to-get-image-url-in-html-js
const C_REGEX_URL_IMG = "^https?:\\/\\/.*\.(?:png|jpg|jpeg|gif|png|svg|webp)(\\??.*)$";
// https://stackoverflow.com/questions/22172604/convert-image-url-to-base64
const C_REGEX_FILE_EXTENSION = "\\.[^.]*$";
//https://stackoverflow.com/questions/4460586/javascript-regular-expression-to-check-for-ip-addresses
const C_REGEX_IP = "^(?!0)(?!.*\\.$)((1?\\d?\\d|25[0-5]|2[0-4]\\d)(\\.|$)){4}$";
// https://stackoverflow.com/questions/5601647/html5-email-input-pattern-attribute#answer-36379040
const C_REGEX_EMAIL = "^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$";

// Intention
const C_INTENT_FILE_NAME = 'IntentFileName';
const C_INTENT_FILE_SIZE = 'IntentFileSize';
const C_INTENT_FILE_TYPE = 'IntentFileType';
const C_INTENT_FILE_BASE64 = 'IntentFileBase64';

// Breakpoint
const C_BREAKPOINT_XS = 1;
const C_BREAKPOINT_SM = 2;
const C_BREAKPOINT_MD = 3;
const C_BREAKPOINT_LG = 4;
const C_BREAKPOINT_XL = 5;
const C_BREAKPOINT_XXL = 6;

// Params
const C_PARAM_DRAFT = 'draft';
const C_PARAM_LOGIN = 'login';
const C_PARAM_MODULE = 'module';
const C_PARAM_SHARE = 'share';
const C_PARAM_PAYLOAD = 'payload';

// MSAL groups, must match config.json
const C_MSAL_GROUP_STUDENT = 'student';
const C_MSAL_GROUP_REVIEWER = 'reviewer';
const C_MSAL_GROUP_STAFF = 'staff';
const C_MSAL_GROUP_ADMIN = 'admin';

// Sync
const C_DB_SYNC_STATUS_SUCCESS = 'success';
const C_DB_SYNC_STATUS_ERROR = 'error';
const C_DB_SYNC_STATUS_PENDING = 'pending';

// Map
const C_MAP_ESRI = 'esri';