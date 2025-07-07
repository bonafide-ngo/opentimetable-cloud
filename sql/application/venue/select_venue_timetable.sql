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
            :in_list_venues = 1
            AND tmt_vnx_code IN (:in_venues)
        )
        OR (
            :in_list_venues = 0
            AND tmt_vnx_code IS NOT NULL
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