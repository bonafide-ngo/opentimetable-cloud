-- Active
SELECT
    snc_id
FROM
    ott_sync
WHERE
    snc_active = 1
UNION
-- Draft
SELECT
    snc_id
FROM
    ott_sync
WHERE
    snc_draft = 1
UNION
-- Pending
SELECT
    snc_id
FROM
    ott_sync
WHERE
    snc_status = 'pending'
UNION
-- Archive
SELECT
    snc_id
FROM
    (
        SELECT
            snc_id
        FROM
            ott_sync
        WHERE
            snc_status = 'success'
            AND snc_active IS NULL
            AND snc_live_to_timestamp IS NOT NULL
            AND snc_delete = 0
        ORDER BY
            snc_id DESC
        LIMIT
            10
    ) sql_archive
UNION
-- Sync
SELECT
    snc_id
FROM
    (
        SELECT
            snc_id
        FROM
            ott_sync
        WHERE
            snc_status = 'success'
            AND snc_active IS NULL
            AND snc_live_to_timestamp IS NULL
            AND snc_delete = 0
        ORDER BY
            snc_id DESC
        LIMIT
            10
    ) sql_sync
UNION
-- Error
SELECT
    snc_id
FROM
    (
        SELECT
            snc_id
        FROM
            ott_sync
        WHERE
            snc_status = 'error'
            AND snc_delete = 0
        ORDER BY
            snc_id DESC
        LIMIT
            10
    ) sql_error