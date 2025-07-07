INSERT INTO
	ott_student (
		std_btc_id,
		std_activity_id,
		std_student_id
	)
SELECT
	DISTINCT rst_btc_id,
	-- TOCONFIGURE
FROM
	raw_student
WHERE
	rst_btc_id = :btc_id;