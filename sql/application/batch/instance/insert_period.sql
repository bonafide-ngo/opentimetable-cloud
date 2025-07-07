INSERT INTO
    ott_period (
        prd_btc_id,
        prd_week,
        prd_week_label,
        prd_week_start_date,
        prd_week_start_timestamp,
        prd_semester
    )
SELECT
    -- TOCONFIGURE
FROM
    raw_timetable
WHERE
    rtm_btc_id = :btc_id;