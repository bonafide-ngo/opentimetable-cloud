SELECT
    NULL
FROM
    ott_sync
WHERE
    snc_id = :snc_id
    AND snc_status = 'success'
    AND snc_delete = 0