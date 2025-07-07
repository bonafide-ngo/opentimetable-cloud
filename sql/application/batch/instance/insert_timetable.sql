INSERT INTO
    ott_timetable (
        tmt_btc_id,
        tmt_day,
        tmt_period,
        tmt_duration,
        tmt_vnx_code,
        tmt_dpt_code,
        tmt_module,
        tmt_module_link,
        tmt_class_group,
        tmt_display_class_group,
        tmt_week,
        tmt_semester,
        tmt_activity_name,
        tmt_activity_id,
        tmt_crs_code
    )
SELECT
    -- TOCONFIGURE
FROM
    raw_timetable
WHERE
    rtm_btc_id = :btc_id;