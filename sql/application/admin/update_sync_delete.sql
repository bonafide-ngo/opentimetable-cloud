UPDATE
    ott_sync
SET
    snc_delete = 1
WHERE
    snc_id NOT IN (:in_retainers)
    AND snc_delete = 0