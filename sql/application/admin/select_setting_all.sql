SELECT
    stt_code AS code,
    COALESCE(stt_flag, stt_text) AS value
FROM
    ott_setting
WHERE
    stt_active = 1