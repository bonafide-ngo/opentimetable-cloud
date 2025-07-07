-- Extract student data from the source table
INSERT INTO
	raw_student (rst_btc_id, -- TOCONFIGURE
)
SELECT
	:btc_id,
	-- TOCONFIGURE
FROM
	link_student;