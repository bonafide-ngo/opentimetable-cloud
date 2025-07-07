SELECT
    DISTINCT crs_code,
    crs_name
FROM
    ott_sync
    JOIN ott_course ON snc_btc_id = crs_btc_id
WHERE
    snc_id = :snc_id
ORDER BY
    crs_name ASC;