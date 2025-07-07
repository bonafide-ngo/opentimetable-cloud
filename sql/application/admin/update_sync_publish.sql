UPDATE
    ott_sync
SET
    snc_active = 1,
    snc_live_by = :snc_live_by,
    snc_live_from_timestamp = UNIX_TIMESTAMP()
WHERE
    snc_id = :snc_id
    AND snc_status = 'success'
    AND snc_active IS NULL
    AND snc_delete = 0