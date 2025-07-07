SELECT
    DISTINCT prd_week,
    prd_week_label,
    prd_week_start_date,
    prd_week_start_timestamp
FROM
    ott_sync
    JOIN ott_period ON snc_btc_id = prd_btc_id
WHERE
    snc_id = :snc_id
ORDER BY
    prd_week ASC