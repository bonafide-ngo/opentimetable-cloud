SELECT
    DISTINCT tmt_module
FROM
    ott_sync
    JOIN ott_timetable ON tmt_btc_id = snc_btc_id
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
    AND (
        (
            :in_list_courses = 1
            AND tmt_crs_code IN (:in_courses)
        )
        OR :in_list_courses = 0
    )
    AND (
        (
            :in_semester <> ''
            AND tmt_semester = :in_semester
        )
        OR (
            :in_week <> ''
            AND tmt_week = :in_week
        )
        OR (
            :in_semester = ''
            AND :in_week = ''
        )
    )
ORDER BY
    tmt_module ASC;