DELETE ott_timetable
FROM
    ott_sync
    JOIN ott_timetable ON tmt_btc_id = snc_btc_id
WHERE
    snc_delete = 1