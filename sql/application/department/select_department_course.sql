SELECT
    DISTINCT crs_code,
    crs_name
FROM
    ott_sync
    JOIN ott_timetable ON tmt_btc_id = snc_btc_id
    JOIN ott_course ON crs_btc_id = snc_btc_id
WHERE
    snc_id = :snc_id
    AND (
        (
            :in_list_departments = 1
            AND tmt_dpt_code IN (:in_departments)
        )
        OR (
            :in_list_departments = 0
            AND tmt_dpt_code IS NOT NULL
        )
    )
    AND crs_code = tmt_crs_code
ORDER BY
    crs_name ASC;