INSERT INTO
    ott_traffic (
        trf_ip,
        trf_session,
        trf_byte_in,
        trf_byte_out,
        trf_ms,
        trf_method
    )
VALUES
    (
        INET_ATON(:trf_ip),
        :trf_session,
        :trf_byte_in,
        :trf_byte_out,
        :trf_ms,
        :trf_method
    )