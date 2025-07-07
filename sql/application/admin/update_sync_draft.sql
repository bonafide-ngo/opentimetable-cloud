UPDATE
    ott_sync
SET
    snc_draft = 1
WHERE
    snc_id = :snc_id
    AND snc_status = 'success'
    AND snc_delete = 0