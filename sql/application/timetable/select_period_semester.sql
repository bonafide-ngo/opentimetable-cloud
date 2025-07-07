SELECT
    DISTINCT prd_semester
FROM
    ott_sync
    JOIN ott_period ON snc_btc_id = prd_btc_id
WHERE
    snc_id = :snc_id
ORDER BY
    prd_semester ASC