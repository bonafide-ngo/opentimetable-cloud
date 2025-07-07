DELETE ott_student
FROM
    ott_sync
    JOIN ott_student ON std_btc_id = snc_btc_id
WHERE
    snc_delete = 1