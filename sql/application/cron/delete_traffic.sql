DELETE FROM
    ott_traffic
WHERE
    trf_create_timestamp < UNIX_TIMESTAMP() - :validity