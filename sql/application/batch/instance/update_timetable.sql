UPDATE
    ott_timetable
SET
    tmt_module_link = NULL
WHERE
    tmt_btc_id = :btc_id
    -- TOCONFIGURE