SELECT
    dpt_code,
    dpt_name
FROM
    ott_sync
    JOIN ott_department ON snc_btc_id = dpt_btc_id
WHERE
    snc_id = :snc_id
ORDER BY
    dpt_name ASC;