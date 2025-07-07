INSERT INTO
    ott_sync (
        snc_btc_id,
        snc_status,
        snc_create_by,
        snc_create_timestamp
    )
SELECT
    snc_btc_id,
    snc_status,
    snc_create_by,
    snc_create_timestamp
FROM
    ott_sync
WHERE
    snc_id = :snc_id
    AND snc_status = 'success'
    AND snc_active IS NULL
    AND snc_delete = 0