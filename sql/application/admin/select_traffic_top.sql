SELECT
    COUNT(*) AS trf_count,
    SUM(trf_byte_in) AS trf_size_in,
    SUM(trf_byte_out) AS trf_size_out,
    INET_NTOA(trf_ip) AS trf_ip
FROM
    ott_traffic
GROUP BY
    trf_ip
ORDER BY
    trf_count DESC
LIMIT
    10