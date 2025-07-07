-- Extract student data from the source table
INSERT INTO
	raw_module (rmd_btc_id, -- TOCONFIGURE
)
SELECT
	:btc_id,
	-- TOCONFIGURE
FROM
	link_module;