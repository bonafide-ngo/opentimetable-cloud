INSERT INTO
    ott_venue (
        vnx_btc_id,
        vnx_code,
        vnx_name
    )
SELECT
    DISTINCT rtm_btc_id,
    -- TOCONFIGURE
FROM
    raw_timetable
WHERE
    -- TOCONFIGURE
    AND rtm_btc_id = :btc_id;