SELECT
    snc_id,
    btc_id
FROM
    ott_sync
    JOIN ott_batch ON btc_id = snc_btc_id
WHERE
    snc_delete = 1