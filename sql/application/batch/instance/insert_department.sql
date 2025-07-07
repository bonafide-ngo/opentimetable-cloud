INSERT INTO
    ott_department (
        dpt_btc_id,
        dpt_code,
        dpt_name
    )
SELECT
    -- TOCONFIGURE
FROM
    raw_timetable
WHERE
    -- TOCONFIGURE
    AND rtm_btc_id = :btc_id;