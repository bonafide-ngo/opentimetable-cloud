DELETE ott_course
FROM
    ott_sync
    JOIN ott_course ON crs_btc_id = snc_btc_id
WHERE
    snc_delete = 1