UPDATE
    ott_setting
SET
    stt_active = 0
WHERE
    stt_code = :stt_code
    AND stt_active = 1