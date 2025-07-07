SELECT
    COALESCE(stt_flag, stt_text) AS value
FROM
    ott_setting
WHERE
    stt_code = :stt_code
    AND stt_active = 1
LIMIT
    1