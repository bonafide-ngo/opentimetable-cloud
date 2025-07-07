INSERT
    IGNORE INTO ott_location (lct_code, lct_update_by)
SELECT
    DISTINCT vnx_code,
    :lct_update_by
FROM
    ott_venue
WHERE
    vnx_btc_id = :btc_id;