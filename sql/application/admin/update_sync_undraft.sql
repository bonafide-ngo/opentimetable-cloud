UPDATE
    ott_sync
SET
    snc_draft = NULL
WHERE
    snc_draft = 1
    AND snc_delete = 0