SELECT
    DISTINCT tmt_module
FROM
    ott_sync
    JOIN ott_timetable ON tmt_btc_id = snc_btc_id
WHERE
    snc_id = :snc_id
    AND tmt_module IS NOT NULL
ORDER BY
    tmt_module ASC;