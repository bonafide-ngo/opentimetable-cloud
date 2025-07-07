SELECT
    IFNULL(AVG(_inner.trf_count), 0)
FROM
    (
        SELECT
            COUNT(*) AS trf_count
        FROM
            ott_traffic
        GROUP BY
            trf_ip
    ) _inner