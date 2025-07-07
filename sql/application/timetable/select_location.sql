SELECT
    lct_code,
    lct_latitude,
    lct_longitude,
    --
    vnx_code,
    vnx_name
FROM
    ott_location
    JOIN ott_venue ON vnx_code = lct_code
    JOIN ott_sync ON snc_btc_id = vnx_btc_id
WHERE
    snc_id = :snc_id
    AND lct_code = :lct_code