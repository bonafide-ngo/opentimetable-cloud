SELECT
    ntf_id,
    ntf_post_base64
FROM
    td_notification
ORDER BY
    ntf_create_timestamp ASC
LIMIT
    100