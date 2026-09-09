[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
![Badge](https://img.shields.io/badge/Accessibility-WCAG_2.1_AA-green)

# Open Timetable Cloud
**Open Timetable Cloud** is an open-source application designed for schools, colleges, and universities, facilitating integration with various timetabling data sources to generate dynamic, accessible, and user-friendly timetables. The application provides current timetable access via a cross-browser responsive web interface and across Android and iOS mobile devices.

The system streamlines the scheduling process, enhances user experience, and supports efficient timetable management and distribution for educational institutions.

## Key Features
- **Flexible Data Sources:** Pull, normalise and denormalise timetable data directly from bespoke databases, directly from the [Scientia (T1 / Technology1)](https://www.technology1.com/products/timetabling-and-scheduling) timetabling database or from APIs or files compatible with the [OTF - Open Timetable Format](https://github.com/bonafide-ngo/opentimetable-standard).
- **Dynamic Content Generation:** Supports scheduled timetable synchronization and publication.
- **Accessibility:** Ensures all users can easily access and navigate timetables [WCAG 2.1](https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1).
- **Cross-Platform:** Compatibility across current web browsers and mobile devices.
- **Single Sign-On:** Configurable Single Sign-On (SSO) via Microsoft Entra ID for timetable management and student/department access.
## User Access
- **Public Users:** View general timetable information by Lectures, Venues, Departments and Modules.
- **Students:** Access personalised timetables via **SSO** for a secure and seamless experience.
- **Staff and Administrators:** Manage and update timetables, ensuring accurate scheduling and resource allocation via **SSO**.

## Benefits
- **Responsive Design:** Optimized for various devices, providing a consistent user experience across desktops, tablets, and smartphones.
- **Scheduled Updates:** Publishes synchronized timetable data so users can access the latest available information, reducing confusion and scheduling conflicts.
- **Open-Source Flexibility:** Allows for customization and integration with existing institutional systems, making it adaptable to specific needs.
- **Cross Compatibility:** Supports integration with [Scientia (T1 / Technology1)](https://www.technology1.com/products/timetabling-and-scheduling) timetabling databases and other configured data sources.
- **Open Timetable Standard:** Ensures interoperability, making it easier to integrate with other systems, share data seamlessly, and enhance user accessibility across platforms. See [OTF - Open Timetable Format](https://github.com/bonafide-ngo/opentimetable-standard).

## [Wiki - Project](https://github.com/bonafide-ngo/opentimetable-cloud/wiki)
- [Requirements](https://github.com/bonafide-ngo/opentimetable-cloud/wiki/Requirements)
- [Personas](https://github.com/bonafide-ngo/opentimetable-cloud/wiki/Personas)
- [Functional Specifications](https://github.com/bonafide-ngo/opentimetable-cloud/wiki/Functional-Specifications)
- [Non Functional Specifications](https://github.com/bonafide-ngo/opentimetable-cloud/wiki/Non-Functional-Specifications)
- [Accessibility WCAG 2.1](https://github.com/bonafide-ngo/opentimetable-cloud/wiki/Accessibility-WCAG-2.1)
- [Data Architecture](https://github.com/bonafide-ngo/opentimetable-cloud/wiki/Data-Architecture)
- [Data Model](https://github.com/bonafide-ngo/opentimetable-cloud/wiki/Data-Model)
- [TODO](https://github.com/bonafide-ngo/opentimetable-cloud/wiki/TODO)

## Wiki - Application
- [Project Structure](#project-structure)
- [Data Model](#data-model)
- [Prerequisites](#prerequisites)
- [Optional Integrations](#optional-integrations)
- [Configuration](#configuration)
- [Public Browser Configuration](#public-browser-configuration)
- [Server Environment File](#server-environment-file)
- [API Bootstrap File](#api-bootstrap-file)
- [API Logging and Debugging](#api-logging-and-debugging)
- [Request and Data Flow](#request-and-data-flow)
- [API Automation](#api-automation)

## Stewardship
[Bona Fide NGO](https://bonafide.ngo) is the original custodian of the **Open Timetable Cloud** application, ensuring it meets the needs of users, educational institutions, and stakeholders. It provides vital support, expertise, and guidance, helping the project thrive and deliver optimal results. Its commitment improves efficiency and accessibility in the educational sector.

## Sponsorship
This project is proudly supported by the following organizations, whose contributions are vital to the success of the **Open Timetable Cloud** application. They provides essential resources across business analysis, research, funding and infrastructure, enabling continuous design, development, and long-term maintenance:
The project is supported by the following organizations, offering essential business requirements, research capability and financial support for the design, development, and maintenance of the **Open Timetable Cloud** application:
- [Maynooth University](https://mu.ie): Providing domain expertise, real-world use cases, and continuous feedback to help shape the functionality and usability of the system. The production SaaS environment is hosted at https://timetable.maynoothuniversity.ie by **Bona Fide NGO**.
- [AWS](https://aws.amazon.com/): Offering credits for cloud infrastructure and services to support the initial research and proof-of-concept of the project.

## Project Structure
The project is deployed as a PHP-backed single-page web application. The browser loads the client-side application from `/app` and communicates with the server through JSON-RPC-style AJAX requests to `/api`.

| Directory | Purpose |
| --- | --- |
| `/app` | Client-side pages and JavaScript for the home, timetable, student, and administration areas. |
| `/api` | PHP entry points, application services, framework classes, libraries, sessions, logs, and server-side cache. |
| `/api/application` | Domain-specific API, business service, DTO, and override code for each application area. |
| `/api/constant` | Server-side constants, database definitions, environment templates, and signing configuration. |
| `/api/framework` | Shared framework classes, templates, and bundled PHP dependencies. |
| `/sql` | Database schema, upgrade scripts, queries, seed data, and operational SQL grouped by application area. |
| `/config` | Public client configuration consumed by the browser, including URLs, language settings, feature flags, and UI options. Do not place secrets here. |
| `/lang` | JSON translation dictionaries for the supported languages. |
| `/css` and `/js` | Shared client-side stylesheets and framework/application JavaScript used by the pages in `/app`. |
| `/img`, `/audio` | Static images, icons, and audio assets. |

The top-level HTML files and service-worker scripts provide the public shell and Progressive Web App support. The web server must expose the static files while routing PHP requests under `/api` to PHP.

## Prerequisites
The production runtime requires the following services and components:

- **PHP**, with a web-server integration such as PHP-FPM or an equivalent supported SAPI. The bundled Composer dependencies include PHP platform checks, so the selected PHP runtime must satisfy all dependency requirements.
- **MariaDB** or another compatible MySQL database server. The application uses a MySQL-compatible database connection and the default database port is `3306`. The schema and supporting scripts are in `/sql/schema` and `/sql`. Depending on the third-party timetable data source, additional infrastructure may also be required, such as an ODBC driver/connector or a secure VPN/IPsec connection to the source system.
- **Memcached**, when distributed caching, Memcached locking, or Memcached-backed PHP sessions are enabled. The default environment template defines Memcached servers and allows sessions to fall back to the filesystem when `MEMCACHED_SESSION` is disabled.
- A web server capable of serving HTML, CSS, JavaScript, JSON, images, and service-worker files, and forwarding PHP requests to the API runtime.
- PHP extensions required by the bundled libraries and application, including database access, JSON, cURL, OpenSSL, mbstring, file information, and the `memcached` extension when Memcached is enabled.
- An installed GeoIP country database when IP geolocation features are enabled. The default path is `/usr/share/GeoIP/GeoLite2-Country.mmdb`.

Microsoft Entra ID (Azure AD) application registration is required for SSO features. SMTP, Firebase, SMS, map, analytics, and other integrations are optional and must be configured only when the corresponding features are enabled.

For SSO, register the browser application with the exact public redirect URI,
authority/tenant, Graph scopes, and group claims used in `/config/config.json`.
Grant the required Graph consent, populate the `student`, `reviewer`, `staff`,
and `admin` role groups, and ensure the configured student identifier property
is present in the directory user profile.

### Deployment hardening

- Serve the site exclusively over HTTPS and verify that HTTP and `www` redirects
	preserve HTTPS. The checked-in Apache rewrite rule is only a starting point;
	apply the equivalent policy in the production web server or reverse proxy.
- Keep `/api/cache/`, `/api/log/`, `/api/session/`, `/api/stat/` completely outside web
	access. These are runtime storage directories, not public application assets.
- Add suitable security headers, backups, log retention, and operating-system
	permissions according to the hosting environment. Test the deployed paths
	from an unauthenticated browser before opening the service publicly.

## Optional Integrations
- **Memcached:** Available for application caching, distributed locking, and PHP session storage. It is strongly recommended for production deployments with high traffic or load because it reduces repeated database and filesystem work and improves coordination across multiple application workers. File-based caching and sessions remain available when Memcached is disabled.
- **Firebase:** Firebase is currently not in active use. The existing Firebase configuration and service integration are retained as a foundation for a potential future push-notification feature.
- **Matomo:** The application supports Matomo analytics for monitoring usage and timetable interactions. It can be enabled and configured with the Matomo settings in `/config/config.json`, including the instance URL, site ID, and cookie preferences.
- **GoAccess:** GoAccess is an optional deployment-level web-access-log analyzer.

## Configuration
Configuration is split between browser-visible settings and server-only environment settings:

1. Update `/config/config.json` with deployment-specific public values such as the API URL, application URLs, supported languages, timezone, and client integration settings. This file is intentionally publicly accessible, so it must not contain passwords, private keys, or service credentials.
2. Configure database, Memcached, SMTP, signing keys, Firebase, and SMS credentials in the server-side environment file under `/api/constant/env`. Keep this file outside public source control or protect it with the deployment process.
3. Apply the SQL schema and required seed or upgrade scripts from `/sql` to the configured MariaDB database.
4. Ensure the PHP process can write to the runtime directories used for logs, cache, and sessions, unless those facilities are redirected to external services.
5. Configure the web server so the application root serves the static SPA files and `/api` remains executable by PHP.

### Public Browser Configuration

The browser loads `/config/config.json` at runtime. Treat this file as public:
it can be downloaded by any visitor and its values are copied into the client
application. It is appropriate for URLs, labels, feature switches, OAuth
client metadata, map providers, cookie names, and display settings. It must
never contain database passwords, SMTP passwords, SMS credentials, Firebase
service-account JSON, private signing keys, or other confidential material.

The file is JSON, so use JSON syntax rather than PHP constants or comments.
After changing it, reload the application and clear any CDN or browser cache
that may still serve the previous version. The following sections describe the
supported top-level properties.

#### Application identity and language

| Property | Description |
| --- | --- |
| `version` | Public application version displayed or used by client code. Keep it aligned with the deployed release. |
| `license` | Public license or copyright label used by the application. |
| `lang` | Default language code, such as `en` or `ga`. |
| `language` | Map of supported language codes to their display labels. Each language must have a matching dictionary under `/lang`. |
| `logo` | Public logo URLs. `invariant` is the default image, `variant` is formatted with a language or variant value, and `og` is used for social/Open Graph presentation. |
| `autosync` | Human-readable automatic synchronization schedule shown by the application. The scheduler itself must also be configured to invoke the relevant API routine. |

#### Settings and URLs

The `setting.flag` and `setting.text` values are application setting codes,
not the setting values themselves. They must match the codes seeded in the
database, such as timetable visibility flags and notice text values.

The `url` object contains the public route map and external links:

- `api` is the fully qualified API endpoint used by browser AJAX calls.
- `root`, `home`, `lecture`, `venue`, `department`, `student`, `module`,
	`admin`, and `maintenance` are application routes and must match the web
	server's public paths.
- `studentPayload` is the student timetable route template and must retain the
	`{0}` placeholder for the generated payload.
- `analytics`, `uptime`, `ticketingSystem`, `www`, `privacy`, `coursesModule`,
	`map`, `googlePlay`, `appleUrlScheme`, and `appStore` are optional links.
- `directionsGPS` and `directionsStatic` are map direction URL templates and
	must retain their coordinate placeholders.
- `firebaseBatchFCM` and `firebaseFCM` are public endpoint templates; the
	Firebase project identifier is inserted by the client/server integration as
	appropriate.
- `ieInfo` is the compatibility-information link shown to affected browsers.

The `links` object holds additional named links used by the application. The
`social` array contains public social links with a `title`, `href`, and icon
class. Empty URLs disable the corresponding link.

#### Timeouts, email labels, and PHP settings

| Section | Properties | Description |
| --- | --- | --- |
| `validity` | `intervalPending`, `payload`, `syncTimeout`, `toast`, `trafficLog` | Client/API validity windows. `intervalPending`, `payload`, `syncTimeout`, and `toast` are expressed in seconds or milliseconds according to the consuming feature; preserve the existing units when changing them. `trafficLog` is the traffic retention interval in seconds. |
| `email` | `support`, `noreply`, `timetable` | Public sender/recipient address and display-name pairs used in email templates and error notifications. SMTP credentials remain in `env.php`. |
| `php.session` | `gc_maxlifetime`, `gc_probability`, `gc_divisor` | PHP session lifetime and garbage-collection settings applied by the API. Keep cookie expiry and `gc_maxlifetime` consistent. |
| `php.date` | `timezone` | PHP/application timezone used for dates and log timestamps. Use a valid PHP timezone identifier. |
| `php.log` | `maxsize` | Maximum active API log size in bytes before rotation. This is public operational configuration; the log contents themselves must remain protected. |

When changing a validity value, check both the unit expected by the consuming
JavaScript/API code and any matching cron or database retention process. Do not
use this file to configure server-side PHP credentials or filesystem paths.

#### VTI and Firebase web settings

`vti.enable` controls the optional Virtual Tunnel Interface behavior and
`vti.param` is the public request parameter name. The `vti.storage` values are
public storage-key labels used by the browser; they are not private keys and
must not be mistaken for the signing keys in `env.php`.

The `firebase` object contains browser-side Firebase settings:

- `firebase.fcm.oAuth` contains the public web OAuth configuration expected by
	the Firebase client integration.
- `firebase.fcm.webpushKeyPair` contains the public web push key material, when
	browser notifications are enabled.
- `firebase.generic` is the public generic notification/category value.

Firebase service-account credentials and OAuth secrets belong in
`APP_FIREBASE_OAUTH2` in `env.php`, not in this file. Firebase can remain
empty/disabled when push notifications are not deployed.

#### Matomo analytics

| Property | Description |
| --- | --- |
| `matomo.enable` | Enables client-side Matomo tracking. Keep `false` if analytics are not approved or deployed. |
| `matomo.siteId` | Numeric Matomo site identifier. Required when tracking is enabled. |
| `matomo.baseUrl` | Matomo instance URL, normally ending in `/`. |
| `matomo.disableCookies` | Disables Matomo cookies when `true`, subject to the deployment's privacy requirements. |
| `matomo.timetableTitle` | Public title/category used for timetable analytics events. |

#### Microsoft Entra ID / MSAL

The `msal` object configures the browser's Microsoft Authentication Library
public client. A client ID is intentionally public; client secrets must never
be placed here.

| Property | Description |
| --- | --- |
| `msal.instance.auth.clientId` | Entra application/client ID. |
| `msal.instance.auth.authority` | Entra authority/tenant URL used for authentication. |
| `msal.instance.auth.redirectUri` | URL registered for the browser redirect response. It must exactly match the Entra app registration. |
| `msal.instance.auth.navigateToLoginRequestUrl` | Controls whether MSAL returns to the original login URL. |
| `msal.instance.cache` | Browser token-cache behavior. `cacheLocation` is normally `localStorage`; `storeAuthStateInCookie` supports browser compatibility. |
| `msal.instance.system` | Browser-specific MSAL behavior, including iframe redirects and platform broker use. |
| `msal.url` | Public Microsoft Graph and signing-key endpoints. Preserve the `{0}` placeholder in `select`. |
| `msal.property.user` | Graph user property used as the application username/email. |
| `msal.property.studentId` | Graph user property containing the student identifier. |
| `msal.scopes` | Graph permission scopes requested by the client. These must be granted by the Entra app registration. |
| `msal.claims` | Required group claims in ID and access tokens. The Entra app must emit these claims for role resolution to work. |
| `msal.groups` | Entra group IDs or values assigned to the public role names `student`, `reviewer`, `staff`, and `admin`. |
| `msal.groupsPriority` | Role precedence when a user belongs to more than one configured group. |

The group names must remain aligned with the server constants in
`api/constant/constant.application.php`. Restrict the `admin` group to trusted
operators because it grants administrative UI access.

#### Cookies and timetable presentation

The `cookie` object defines public cookie names, lifetimes, and browser flags:

- `alive` lists cookies whose lifetime is extended by the application.
- `property.session` contains the PHP session and selected-sync cookie names.
- `property.msal` contains the MSAL identifier and access-token cookie names.
- `property.user` contains language and smart-banner preferences.
- `option.path`, `option.secure`, and `option.sameSite` apply the shared cookie
	policy. Keep `secure: true` for HTTPS deployments and use an appropriate
	`sameSite` value for the authentication flow.

The `prefix` values are public naming conventions for week and semester keys.
The `length` value controls the generated prefix length and should not be
changed without checking existing client-side storage and URL behavior.

The `map` object controls map display. Set `map.enable` to `false` to disable
maps, choose the default `layer`, and configure each layer's tile URL,
subdomains, and attribution. Keep required attribution for OpenStreetMap,
Google, or Esri tiles and verify that the selected provider permits the
deployment's traffic volume and usage.

The `ott` object defines timetable presentation defaults:

- `author` is the public data/provider attribution.
- `days` controls the displayed weekday order.
- `order` controls period ordering.
- `periods` maps period identifiers to public `from` and `to` times.
- `showTimeRange` controls whether timetable cards display period times.
- `responsiveFirst` controls the responsive timetable presentation mode.

The period keys and timetable data must agree with the imported SQL data. The
`ipBlocklist` array contains public client-side blocklist values supplied to the
security initializer; it can be empty when the server-managed blocklist is
used.

### Server Environment File

The API loads deployment-specific server settings from
`/api/constant/env/env.php` during bootstrap. This is a PHP file, not a dotenv
file: settings are declared with PHP `define()` calls and the database
connections are supplied as the `DB` array. Copy the environment template for
the deployment, replace every placeholder, and keep the resulting file out of
public source control and outside the browser-accessible configuration.

The file controls the following settings.

#### Runtime errors

The template starts by setting PHP's error level to include errors while
excluding warnings, notices, and deprecations. The application-wide `DEBUG`
constant is defined in `/api/igniter.php`; set it appropriately for the
deployment before the API is loaded. Production deployments should keep debug
mode disabled because debug logging and diagnostic responses can expose
operational details.

#### Memcached and sessions

| Constant | Value | Description |
| --- | --- | --- |
| `MEMCACHED_SERVERS` | Array of `[host, port]` pairs | Memcached servers used for application caching and locking. Use an empty host only for an intentionally local/default setup; configure the actual hostname or IP in deployed environments. |
| `MEMCACHED_SESSION` | Boolean | When `true`, PHP sessions use Memcached. When `false`, sessions use the filesystem under `/api/session`. |

If Memcached is enabled, install the PHP `memcached` extension, ensure the PHP
worker can reach every configured server, and ensure the session/cache
directories remain writable when filesystem fallback is used.

#### Database connections

`DB` must define both `DB_LINK_MASTER` and `DB_LINK_MYSQLDUMP`. Each connection
has the following keys:

| Key | Description |
| --- | --- |
| `DB_DEBUG` | Uses the application `DEBUG` setting for database diagnostics. |
| `DB_TYPE` | Database adapter class identifier. The supplied deployment uses `OSQL_MYSQL`. |
| `DB_HOST` | Database hostname or IP address. |
| `DB_PORT` | MySQL-compatible database port, normally `3306`. |
| `DB_SCHEMA` | Database/schema name containing the Open Timetable Cloud tables. |
| `DB_USER` | Database account name. |
| `DB_PASSWORD` | Database account password. |

`MASTER` is the primary application connection used for reads and writes.
`MYSQLDUMP` is used by administrative database export operations and may point
to the same server or to a separately authorised database endpoint. Apply the
schema and upgrades from `/sql` before enabling the application.

Example shape, with credentials omitted:

```php
define('DB', array(
	DB_LINK_MASTER => array(
		DB_DEBUG => DEBUG,
		DB_TYPE => 'OSQL_MYSQL',
		DB_HOST => 'database.example.org',
		DB_PORT => 3306,
		DB_SCHEMA => 'open_timetable',
		DB_USER => 'ott_application',
		DB_PASSWORD => 'replace-with-secret'
	),
	DB_LINK_MYSQLDUMP => array(
		DB_DEBUG => DEBUG,
		DB_TYPE => 'OSQL_MYSQL',
		DB_HOST => 'database.example.org',
		DB_PORT => 3306,
		DB_SCHEMA => 'open_timetable',
		DB_USER => 'ott_backup',
		DB_PASSWORD => 'replace-with-secret'
	)
));
```

#### SMS

| Constant | Description |
| --- | --- |
| `SMS_PROVIDER` | Provider identifier. Set to `APP_SMS_PROVIDER_VONAGE` (`vonage`) to enable the bundled Vonage integration, or leave empty when SMS is unused. |
| `SMS_SENDER` | Sender name/number. The template limits this to 11 characters. |
| `SMS_DEBUG_MOBILE` | Test destination used instead of the requested mobile number while `DEBUG` is enabled. Do not use a real recipient accidentally in development. |
| `SMS_CREDENTIALS` | Provider credentials. For Vonage, set `APP_SMS_API_KEY` and `APP_SMS_API_SECRET` under the `vonage` entry. |

SMS is optional. Configure provider credentials, outbound network access, and a
valid sender before selecting a provider.

#### SMTP and DKIM

| Constant | Description |
| --- | --- |
| `SMTP_HOST` | SMTP server hostname. `null` disables a configured host. |
| `SMTP_PORT` | SMTP server port, commonly `587` for STARTTLS or `465` for SSL. |
| `SMTP_USERNAME` / `SMTP_PASSWORD` | SMTP authentication credentials. |
| `SMTP_AUTHENTICATION` | Boolean indicating whether SMTP authentication is used. |
| `SMTP_SECURE` | PHPMailer transport security mode, such as `ssl` or the deployment's supported TLS mode. |
| `DKIM` | Boolean enabling DKIM signing. |
| `DKIM_DOMAIN` | Signing domain used in the DKIM signature. |
| `DKIM_SELECTOR` | DNS selector corresponding to the DKIM public key. |

When `DKIM` is enabled, place the matching private key at
`/api/constant/dkim/dkim.pem`, publish the matching DNS record, and restrict
the key file to the PHP process. SMTP and DKIM are required only for features
that send email.

#### Signed API and webhook authentication

| Constant | Description |
| --- | --- |
| `SALSA` | Shared secret used to sign and validate protected cron, webhook, and student payload operations. Use a long, random value. |
| `CRYPTO_SIGN_PUBLIC_KEY` | Base64-encoded Ed25519 public key used to verify signed values. |
| `CRYPTO_SIGN_SECRET_KEY` | Base64-encoded Ed25519 secret key used to create signatures. |

These values are security credentials, not application labels. Keep them
consistent with the automation clients that call protected API operations and
never log or commit them. Generate the signing key pair with the PHP sodium
functions referenced in the environment template, then store the encoded keys
in the deployment secret store or protected PHP file.

#### Firebase push notifications

| Constant | Description |
| --- | --- |
| `APP_FIREBASE_PROJECT_ID` | Firebase project ID used in the FCM API URL. |
| `APP_FIREBASE_SCOPE` | Google OAuth scope. The default is `https://www.googleapis.com/auth/cloud-platform`. |
| `APP_FIREBASE_OAUTH2` | JSON service-account credential payload used to obtain an OAuth2 token. Store it as valid JSON, not as a PHP array. |

Firebase is optional and should be configured only when push notifications are
enabled. The JSON credential is sensitive and must not be exposed through the
public `/config/config.json` file.

After changing `env.php`, restart PHP workers where configuration is cached,
check the API logs, and verify database connectivity, email/SMS delivery, or
webhook signing only for the integrations enabled in that deployment.

### API Bootstrap File

The API entry point loads `/api/igniter.php` before the rest of the framework.
This file contains deployment switches that affect request handling and PHP
startup. Unlike `env.php`, it should not contain passwords or service
credentials, but it is still server-side application configuration and should
be changed deliberately for each environment.

#### Runtime switches

| Constant | Default | Description and deployment guidance |
| --- | --- | --- |
| `DEBUG` | `false` | Enables PHP error display, startup error display, additional diagnostics, and debug behavior used by the application. Keep `false` in production because errors and logs can disclose implementation or infrastructure details. |
| `MAINTENANCE` | `false` | Places the JSON-RPC API into maintenance mode. Non-admin API calls are rejected while administrative API calls remain available for operational work. Set back to `false` after maintenance is complete. |
| `STATELESS` | `false` | Controls whether the application starts a PHP session. Set `true` only for deployments that do not use session-backed features; authenticated student and administrative workflows normally require `false`. |
| `SMTP` | `true` | Enables the mailer SMTP transport. Set to `false` when email is intentionally disabled, even if SMTP values exist in `env.php`. SMTP host, credentials, encryption, and DKIM settings are still supplied by `env.php`. |
| `OG_GZHANDLER` | `false` | Enables PHP output-buffer gzip compression. Leave disabled when the web server, reverse proxy, or CDN already compresses responses to avoid duplicate or conflicting compression. |
| `UTF8_DECODE_INPUT` | `false` | Applies the framework's legacy UTF-8 input decoding during request sanitization. Enable only when the upstream client sends input that must be converted to ISO-8859-1; keep disabled for normal UTF-8 deployments. |
| `REVERSE_PROXY` | `null` | Selects the trusted client-IP header when the API is behind a reverse proxy. Supported values are `null`, `'X-Forwarded-For'`, and `'true-client-ip'`. Configure this only when the proxy overwrites the selected header and direct clients cannot spoof it. |

The values must be valid PHP literals, for example:

```php
define('DEBUG', false);
define('MAINTENANCE', false);
define('STATELESS', false);
define('SMTP', true);
define('OG_GZHANDLER', false);
define('UTF8_DECODE_INPUT', false);
define('REVERSE_PROXY', null);
```

#### Startup and error handling

`DEBUG` is evaluated immediately by `igniter.php` to set PHP's
`display_errors` and `display_startup_errors` directives. The application then
loads `path.php`, installs its logging and exception handlers, initializes
sessions, mail, security, caching, cryptography, and database connections, and
loads the public `/config/config.json` file. A production deployment should
therefore keep `DEBUG` disabled and ensure `/api/log`, `/api/cache`, and
`/api/session` are writable by the PHP worker where the selected features use
the filesystem.

### API Logging and Debugging

The API uses the `Log` class for application, PHP, exception, shutdown, and
debug logging. Logging is initialized during API bootstrap after
`/config/config.json` has been loaded. The main log is written to
`/api/log/log`; fatal shutdown handling uses the absolute path derived from the
current working directory so that errors can still be recorded when normal
relative path resolution is unavailable.

#### Log levels and behavior

| Type | Enabled when | Use |
| --- | --- | --- |
| Error | Always | PHP errors, uncaught exceptions, database/cache failures, and fatal shutdown conditions. Errors are added to the current error stack and may cause the request checkpoint to fail. |
| Report | Explicitly requested by application code | Operational reports that are not necessarily failures. |
| Debug | `DEBUG` is `true` | Framework and application diagnostics, request lifecycle messages, SQL diagnostics, cache activity, and benchmark information. No debug entries are written when `DEBUG` is `false`. |

PHP errors and uncaught exceptions are registered with the application handlers
during initialization. The error handler records the PHP version, error code,
message, source file, and line. Notices are only recorded when `DEBUG` is
enabled, while the environment template's `error_reporting()` setting also
controls which PHP error categories reach the handler.

JSON-RPC requests call `Log::Checkpoint()` before and after the API method. If
the request has accumulated an error, the checkpoint throws and the request is
returned as an API error rather than silently continuing. Unexpected
exceptions are logged and converted to the generic JSON-RPC internal-error
response; detailed diagnostics remain in the server log.

#### File rotation and retention

The active file is named `log`. Its maximum size is configured in
`/config/config.json` at `php.log.maxsize`, in bytes; the supplied value is
`1048576` (1 MiB). When the active file exceeds that limit, the logger uses a
filesystem lock and renames it to a timestamped file similar to:

```text
log.2026.09.08@14.30.05_<unique-hash>
```

Rotated files remain in `/api/log` until an administrator deletes the log
history or an external retention process removes them. The active `log` file
is deliberately excluded from the admin history deletion operation. Configure
filesystem permissions so the PHP worker can create, append, rotate, and lock
files in `/api/log`, and monitor the directory because rotated logs have no
automatic age-based retention.

#### Email alerts

Selected errors are logged with an email flag. For those entries, the logger
includes the client IP, request URI, user agent, source location, and message,
then sends the report to the configured webmaster address from
`config.json` using the SMTP and DKIM settings in `env.php`. Email delivery is
best-effort and must not be treated as the only alert channel; keep file or
centralized log monitoring enabled as well. Avoid enabling debug mode in
production because diagnostic log entries can contain request or integration
details.

#### Debug mode and diagnostics

Set `DEBUG` to `true` only in a controlled development or staging environment.
It enables PHP error display, debug log entries, cURL verbose mode, benchmark
timers, and additional environment diagnostics. It also changes a few runtime
behaviors, such as allowing fallback IP values and directing debug SMS traffic
to `SMS_DEBUG_MOBILE`. Restore `DEBUG` to `false` after investigation and
restart PHP workers if configuration is cached.

The administrator system view exposes environment statistics to authorized
administrators, including PHP information and benchmark output. Benchmark
statistics include script time, current/peak memory, configured memory limit,
and Memcached usage when Memcached is available. The API also records request
traffic in `ott_traffic`, including method, byte counts, duration, IP, and a
hashed session identifier; server-to-server traffic is omitted in production.

#### Practical troubleshooting workflow

1. Confirm `DEBUG` is disabled unless the issue requires temporary diagnostic
	detail.
2. Check that the PHP worker can write to `/api/log` and that the active `log`
	file is changing during a reproduced request.
3. Inspect the newest log entries for the request timestamp, source file and
	line, JSON-RPC method, database connection, or external integration named
	in the error.
4. Check rotated files when the active log has rolled over; use the authorized
	admin system view or the deployment's log collection system.
5. For performance issues, inspect benchmark output and the `ott_traffic`
	aggregates, then check database and Memcached connectivity.
6. After remediation, disable debug mode, remove temporary diagnostic logs,
	and verify that protected log files cannot be downloaded through the web
	server.

#### Path discovery

`igniter.php` does not define filesystem paths directly; it includes
`/api/path.php`. The path file discovers the project root by walking upward
until it finds `root.php`, then derives the runtime paths below:

| Constant | Resolved location | Purpose |
| --- | --- | --- |
| `ENVIRONMENT` | Directory containing the API, normally `api` | Identifies the deployed API environment and forms `PATH_API`. |
| `PATH_ROOT` | Project root containing `root.php` | Base directory for application, configuration, language, asset, and SQL files. |
| `PATH_CONFIG` | `/config/` | Public browser configuration, including `config.json`. |
| `PATH_API` | `/api/` | API runtime root. |
| `PATH_LOG` / `PATH_LOG_ABS` | `/api/log/` and the current working directory's `log/` | Relative and absolute log locations. |
| `PATH_CACHE` | `/api/cache/` | Filesystem cache location when applicable. |
| `PATH_CONSTANT` | `/api/constant/` | PHP constants, environment settings, and signing material. |
| `PATH_SESSION` | `/api/session/` | Filesystem session storage when `MEMCACHED_SESSION` is `false`. |
| `PATH_CONSTANT_ENV` | `/api/constant/env/` | Location of `env.php`. |
| `PATH_CONSTANT_DKIM` | `/api/constant/dkim/` | Location of the DKIM private key. |
| `PATH_SQL` and `PATH_SQL_*` | `/sql/` and its application/framework subdirectories | SQL schema, queries, batch operations, and database dump paths. |

Do not rename or relocate `root.php` without updating the path-discovery
assumption. The web server should expose the project root for static assets but
must prevent direct downloading of server-side PHP configuration and private
key files.

## Request and Data Flow
Public and authenticated pages are loaded from `/app`. Their JavaScript calls the PHP API under `/api`, which validates the request, reads or writes data through the database layer, and returns JSON responses. Timetable imports and publication state are persisted through the SQL layer, while `/lang` provides translations and `/config/config.json` supplies the public runtime settings. The service worker and manifest support installation as a Progressive Web App.

## API Automation
In addition to browser AJAX requests, the API supports external automation through RESTful webhooks and callable cron routines.

### RESTful Webhooks
Webhooks are dispatched through the API endpoint using the `webhook` query parameter. For example:

```text
https://example.org/api/?webhook=sync&signedSalsa_base64=<signed-value>
```

Available webhook operations include:

- `upgrade` - apply an application or database upgrade, optionally using a `version` value.
- `batch` - start a batch operation using a signed user value.
- `cleanup` - remove expired or temporary batch data.
- `sync` - start an automatic timetable synchronization.
- `traffic` - remove outdated traffic records.
- `ipblocklist` - refresh the cached IP blocklist.
- `custom` - reserved for deployment-specific webhook behavior.

Webhook requests that perform protected operations require signed authentication values. Requests with an unknown webhook name are rejected with HTTP status `403`. Webhook URLs should therefore be called only by trusted infrastructure and should use HTTPS.

### Cron Jobs as API Requests
Scheduled jobs can call the API JSON-RPC endpoint instead of invoking PHP code directly. The available cron methods are:

- `App.Cron.Update_Notification` - process queued notifications.
- `App.Cron.Update_IpBlocklist` - fetch and cache the latest IP blocklist.
- `App.Cron.Delete_Traffic` - delete traffic records older than the configured retention period.
- `App.Cron.Create_Sync` - create an automatic timetable synchronization.

Cron requests must provide the signed authentication value expected by the selected routine. They can be scheduled with the operating system, a container scheduler, or an external job service, provided the caller can reach the API securely. The webhook `sync`, `traffic`, and `ipblocklist` operations are convenience routes for invoking corresponding cron routines.

## Data Model

This section describes the database model represented by the SQL files under
`/sql`. It is based on `sql/schema/dll.sql`, the upgrade scripts in
`sql/schema/script`, and the application queries and batch statements.

### Scope and confidence

- `ott_*` tables are the implemented application model visible in the DDL.
- `raw_*` and `link_*` tables are integration/staging surfaces. Their current
  definitions contain `TOCONFIGURE` placeholders, so their complete columns
  and relationships cannot be documented from this checkout.
- The DDL declares indexes but no foreign-key constraints. Relationships below
  are therefore logical relationships enforced by application SQL and naming
  conventions, not by the database engine.
- Several queries also reference `ott_notification`, which is not defined in
  `sql/schema/dll.sql`; notification storage is consequently an unresolved
  schema dependency.

### Conceptual model

The database is organized around immutable-ish imports. A batch identifies one
loaded dataset. A sync record gives that batch an import/publication lifecycle.
The normalized timetable, people, academic dimensions, and periods are stored
with the same batch identifier so multiple historical datasets can coexist.

### Table catalog

#### Import and publication

| Table | Grain and purpose | Important columns | Keys and indexes |
| --- | --- | --- | --- |
| `ott_batch` | One import/load batch. | `btc_id`, start and end Unix timestamps. | Primary key `btc_id`; `btc_start_timestamp` is set by a before-insert trigger. |
| `ott_sync` | One synchronization/publication record for a batch. | `snc_btc_id`, status, active/draft flags, live window, creator, deletion flag. | Primary key `snc_id`; unique `snc_active` and `snc_draft`; indexes on batch, status, and delete flag. |

`snc_status` is an enum with `pending`, `error`, `success`, and an empty
value. Publication operations only activate successful, non-deleted syncs.
The unique flag indexes are intended to allow at most one active sync and one
draft sync, although the columns are nullable and the exact transition policy
is implemented by application updates.

#### Timetable and academic dimensions

| Table | Grain and purpose | Important columns | Keys and indexes |
| --- | --- | --- | --- |
| `ott_timetable` | One timetable activity occurrence or row for a batch. | Activity identity/name, module and link, department/course/venue codes, semester/week/day/period, duration, class group, display flag. | Primary key `tmt_id`; composite indexes optimized for lecture, venue, department, student, and module lookups. |
| `ott_period` | One period/calendar entry for a batch. | Week, display week label, week start date/timestamp, semester. | Primary key `prd_id`; indexes on batch and semester. |
| `ott_course` | One course code/name within a batch. | `crs_btc_id`, `crs_code`, `crs_name`. | Primary key `crs_id`; index on batch. No uniqueness is declared for batch plus code. |
| `ott_department` | One department code/name within a batch. | `dpt_btc_id`, `dpt_code`, `dpt_name`. | Primary key `dpt_id`; index on batch. No uniqueness is declared for batch plus code. |
| `ott_venue` | One venue code/name within a batch. | `vnx_btc_id`, `vnx_code`, `vnx_name`. | Primary key `vnx_id`; index on batch. |
| `ott_location` | Global location metadata keyed by venue code. | `lct_code`, latitude/longitude, updater, update timestamp. | Primary key `lct_id`; unique `lct_code`; update trigger maintains `lct_update_timestamp`. |
| `ott_student` | One student/activity association for a batch. | `std_btc_id`, external activity ID, student ID. | Primary key `std_id`; composite lookup index on batch, student ID, activity ID. |

The timetable table is intentionally denormalized for read performance. Its
department, course, venue, and module values are nullable and are commonly
filtered directly by public timetable queries. The application joins venues to
locations by code, not by numeric ID.

#### Configuration and telemetry

| Table | Grain and purpose | Important columns | Keys and indexes |
| --- | --- | --- | --- |
| `ott_setting` | One application setting/versioned value. | Active flag, code, boolean flag, text value, creator, creation timestamp. | Primary key `stt_id`; indexes on active and code; insert trigger sets creation timestamp. |
| `ott_traffic` | One request/traffic measurement. | IP integer, session, bytes in/out, duration in milliseconds, method, creation timestamp. | Primary key `trf_id`; insert trigger sets creation timestamp. |

The traffic cleanup SQL deletes by creation timestamp. Administrative queries
aggregate it by method, size, duration, or IP/session-related dimensions.

#### Raw and link surfaces

| Table | Observed role | Current limitation |
| --- | --- | --- |
| `raw_timetable` | Source rows staged before normalized timetable/course/period loading. | Only `rtm_id` and `rtm_btc_id` are defined; source columns are `TOCONFIGURE`. |
| `raw_student` | Source rows staged before student loading. | Only `rst_id` and `rst_btc_id` are defined; source columns are `TOCONFIGURE`. |
| `raw_module` | Source rows staged before module-related loading. | Only `rmd_id` and `rmd_btc_id` are defined; source columns are `TOCONFIGURE`. |
| `link_module`, `link_student`, `link_timetable` | Reserved integration/link tables. | The table bodies are `TOCONFIGURE`, with no usable columns or keys. |

### Relationships and cardinality

The following relationships are observable in the SQL, but are not declared as
foreign keys:

1. `ott_batch` to all batch-scoped tables: one batch can have many sync,
	timetable, student, course, department, period, and venue rows.
2. `ott_sync` to batch data: one sync selects one batch through `snc_btc_id`;
	many normalized rows are read through that batch.
3. `ott_timetable` to academic dimensions: timetable codes are matched to
	course, department, and venue codes. The queries do not require all codes to
	resolve to dimension rows.
4. `ott_venue` to `ott_location`: the location query matches `vnx_code` to the
	globally unique `lct_code`. A location can therefore be reused across
	batches, while venue names remain batch-scoped.
5. `ott_student` to `ott_timetable`: a student's `std_activity_id` matches a
	timetable `tmt_activity_id` within the selected batch. This is the basis of
	personalized timetable lookup.
6. Raw staging to batch: each raw row is scoped by its raw `*_btc_id`; batch
	processing truncates raw tables and loads normalized rows for a selected
	batch.

### Lifecycle and data flow

1. Create an `ott_batch`; the database trigger records the start timestamp.
2. Load source records into `raw_timetable`, `raw_student`, and `raw_module`
	for that batch.
3. Transform raw data into batch-scoped `ott_period`, `ott_course`,
	`ott_department`, `ott_venue`, `ott_student`, and `ott_timetable` rows.
4. Create an `ott_sync` record for the batch with status `pending`.
5. Mark the sync `success` or `error`. Failed or incomplete syncs are excluded
	from publication operations.
6. Publish by setting `snc_active = 1` and recording the publisher and live
	start timestamp. Draft, unpublish, rollback, delete, and archive operations
	are represented by the sync flags and live timestamps.
7. Public timetable queries receive a sync ID and read only rows sharing its
	batch ID. Student queries additionally join `ott_student` on student ID and
	activity ID.
8. End the batch with `btc_end_timestamp`, and clean up raw, old sync, traffic,
	or notification data through the batch/cron SQL.

### Query access patterns

- Public views are driven by `ott_sync` plus `ott_timetable`, filtering by
  department, course, module, venue, semester, or week.
- Student views join `ott_sync`, `ott_timetable`, and `ott_student`; the
  composite student index and timetable activity index support this path.
- Period selectors read `ott_period` by batch and optionally semester or week.
- Venue detail reads join `ott_location` to `ott_venue` and then to the sync's
  batch.
- Administration reads syncs by active, draft, pending, status, deletion, or
  live-window state and uses the unique active/draft indexes.
- Batch deletes remove normalized rows by their batch identifier before or
  during retention/rollback workflows.
