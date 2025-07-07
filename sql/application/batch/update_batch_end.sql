UPDATE
    ott_batch
SET
    btc_end_timestamp = UNIX_TIMESTAMP()
WHERE
    btc_id = :btc_id