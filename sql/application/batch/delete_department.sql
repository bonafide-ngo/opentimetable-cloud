DELETE ott_department
FROM
    ott_sync
    JOIN ott_department ON dpt_btc_id = snc_btc_id
WHERE
    snc_delete = 1