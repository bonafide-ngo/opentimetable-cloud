INSERT INTO
    ott_sync (
        snc_btc_id,
        snc_create_by,
        snc_create_timestamp
    )
VALUES
    (
        :snc_btc_id,
        :snc_create_by,
        UNIX_TIMESTAMP()
    )