SELECT
    lct_id,
    lct_code,
    lct_latitude,
    lct_longitude,
    lct_update_by,
    lct_update_timestamp,
    --
    vnx_code,
    vnx_name
FROM
    ott_location
    JOIN ott_venue ON vnx_code = lct_code
    JOIN ott_sync ON snc_btc_id = vnx_btc_id
WHERE
    snc_id = :snc_id