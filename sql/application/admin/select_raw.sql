SELECT
    EXISTS (
        SELECT
            NULL
        FROM
            raw_student
        LIMIT
            1
    )
    OR EXISTS (
        SELECT
            NULL
        FROM
            raw_timetable
        LIMIT
            1
    ) AS raws