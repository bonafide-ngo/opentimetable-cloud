UPDATE
    ott_location
SET
    lct_latitude = :lct_latitude,
    lct_longitude = :lct_longitude,
    lct_update_by = :lct_update_by
WHERE
    lct_id = :lct_id