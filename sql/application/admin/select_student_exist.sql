SELECT
    NULL
FROM
    ott_sync
    JOIN ott_student ON std_btc_id = snc_btc_id
WHERE
    snc_id = :snc_id
    AND std_student_id = CAST(:in_student AS CHAR)
LIMIT
    1