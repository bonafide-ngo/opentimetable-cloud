DELETE ott_venue
FROM
    ott_sync
    JOIN ott_venue ON vnx_btc_id = snc_btc_id
WHERE
    snc_delete = 1