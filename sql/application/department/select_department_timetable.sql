SELECT
    DISTINCT tmt_day,
    tmt_period,
    tmt_duration,
    tmt_vnx_code,
    tmt_module,
    tmt_module_link,
    tmt_activity_name,
    tmt_class_group,
    tmt_display_class_group,
    tmt_semester
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
            :in_list_modules = 1
            AND tmt_module IN (:in_modules)
        )
        OR (
            :in_list_modules = 0
            AND tmt_module IS NOT NULL
        )
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
    tmt_day ASC,
    tmt_period ASC,
    tmt_module ASC;