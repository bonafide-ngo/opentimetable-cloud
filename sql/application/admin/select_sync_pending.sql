SELECT
    snc_id,
    snc_create_timestamp
FROM
    ott_sync
WHERE
    snc_status = 'pending'