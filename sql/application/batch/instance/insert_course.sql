INSERT INTO
    ott_course (
        crs_btc_id,
        crs_code,
        crs_name
    )
SELECT
    -- TOCONFIGURE
FROM
    raw_timetable
WHERE
    -- TOCONFIGURE
    AND rtm_btc_id = :btc_id;