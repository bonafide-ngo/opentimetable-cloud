DELETE ott_period
FROM
    ott_sync
    JOIN ott_period ON prd_btc_id = snc_btc_id
WHERE
    snc_delete = 1