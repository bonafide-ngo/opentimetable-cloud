SELECT
    vnx_code,
    vnx_name
FROM
    ott_sync
    JOIN ott_venue ON snc_btc_id = vnx_btc_id
WHERE
    snc_id = :snc_id
ORDER BY
    vnx_name ASC;