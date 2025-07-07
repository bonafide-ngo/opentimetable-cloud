SELECT
    snc_id,
    snc_status
FROM
    ott_sync
WHERE
    snc_delete = 0
ORDER BY
    snc_id DESC
LIMIT
    1