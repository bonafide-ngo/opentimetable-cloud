UPDATE
    ott_sync
SET
    snc_active = NULL,
    snc_live_to_timestamp = UNIX_TIMESTAMP()
WHERE
    snc_active = 1