-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 28, 2025 at 08:25 AM
-- Server version: 10.6.22-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `opentimetable_dev`
--

-- --------------------------------------------------------

--
-- Table structure for table `link_module`
--

CREATE TABLE link_module
ENGINE=CONNECT DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci CONNECTION='DSN=OracleDB' TABLE_TYPE='ODBC' TABNAME='V_MODULE';

-- --------------------------------------------------------

--
-- Table structure for table `link_student`
--

CREATE TABLE link_student
ENGINE=CONNECT DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci CONNECTION='DSN=OracleDB' TABLE_TYPE='ODBC' TABNAME='V_STUDENT';

-- --------------------------------------------------------

--
-- Table structure for table `link_timetable`
--

CREATE TABLE link_timetable
ENGINE=CONNECT DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci CONNECTION='DSN=OracleDB' TABLE_TYPE='ODBC' TABNAME='V_TIMETABLE';

-- --------------------------------------------------------

--
-- Table structure for table `ott_batch`
--

CREATE TABLE `ott_batch` (
  `btc_id` int(10) UNSIGNED NOT NULL,
  `btc_start_timestamp` int(10) UNSIGNED NOT NULL,
  `btc_end_timestamp` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `ott_batch`
--
DELIMITER $$
CREATE TRIGGER `btc_start_timestamp` BEFORE INSERT ON `ott_batch` FOR EACH ROW SET NEW.btc_start_timestamp = UNIX_TIMESTAMP()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ott_course`
--

CREATE TABLE `ott_course` (
  `crs_id` int(10) UNSIGNED NOT NULL,
  `crs_btc_id` int(10) UNSIGNED NOT NULL,
  `crs_code` varchar(32) NOT NULL,
  `crs_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ott_department`
--

CREATE TABLE `ott_department` (
  `dpt_id` int(10) UNSIGNED NOT NULL,
  `dpt_btc_id` int(10) UNSIGNED NOT NULL,
  `dpt_code` varchar(32) NOT NULL,
  `dpt_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ott_location`
--

CREATE TABLE `ott_location` (
  `lct_id` int(10) UNSIGNED NOT NULL,
  `lct_code` varchar(32) NOT NULL,
  `lct_latitude` float DEFAULT NULL,
  `lct_longitude` float DEFAULT NULL,
  `lct_update_by` varchar(128) DEFAULT NULL,
  `lct_update_timestamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `ott_location`
--
DELIMITER $$
CREATE TRIGGER `lct_update_timestamp` BEFORE UPDATE ON `ott_location` FOR EACH ROW SET NEW.lct_update_timestamp = UNIX_TIMESTAMP()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ott_period`
--

CREATE TABLE `ott_period` (
  `prd_id` int(10) UNSIGNED NOT NULL,
  `prd_btc_id` int(10) UNSIGNED NOT NULL,
  `prd_week` smallint(6) DEFAULT NULL,
  `prd_week_label` smallint(6) DEFAULT NULL,
  `prd_week_start_date` timestamp NULL DEFAULT NULL,
  `prd_week_start_timestamp` int(10) DEFAULT NULL,
  `prd_semester` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ott_setting`
--

CREATE TABLE `ott_setting` (
  `stt_id` int(10) UNSIGNED NOT NULL,
  `stt_active` tinyint(1) NOT NULL DEFAULT 1,
  `stt_code` varchar(32) NOT NULL,
  `stt_flag` tinyint(1) DEFAULT NULL,
  `stt_text` text DEFAULT NULL,
  `stt_create_by` varchar(128) NOT NULL,
  `stt_create_timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `ott_setting`
--
DELIMITER $$
CREATE TRIGGER `stt_create_timestamp` BEFORE INSERT ON `ott_setting` FOR EACH ROW SET NEW.stt_create_timestamp = UNIX_TIMESTAMP()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ott_student`
--

CREATE TABLE `ott_student` (
  `std_id` int(10) UNSIGNED NOT NULL,
  `std_btc_id` int(10) UNSIGNED NOT NULL,
  `std_activity_id` char(64) DEFAULT NULL,
  `std_student_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ott_sync`
--

CREATE TABLE `ott_sync` (
  `snc_id` int(10) UNSIGNED NOT NULL,
  `snc_btc_id` int(10) UNSIGNED NOT NULL,
  `snc_status` enum('pending','error','success','') NOT NULL DEFAULT 'pending',
  `snc_active` tinyint(4) DEFAULT NULL,
  `snc_draft` tinyint(4) DEFAULT NULL,
  `snc_live_by` varchar(128) DEFAULT NULL,
  `snc_live_from_timestamp` int(11) DEFAULT NULL,
  `snc_live_to_timestamp` int(11) DEFAULT NULL,
  `snc_create_by` varchar(128) NOT NULL,
  `snc_create_timestamp` int(11) NOT NULL,
  `snc_delete` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ott_timetable`
--

CREATE TABLE `ott_timetable` (
  `tmt_id` int(10) UNSIGNED NOT NULL,
  `tmt_btc_id` int(10) UNSIGNED NOT NULL,
  `tmt_activity_id` char(64) DEFAULT NULL,
  `tmt_activity_name` varchar(255) DEFAULT NULL,
  `tmt_module` varchar(255) DEFAULT NULL,
  `tmt_module_link` varchar(255) DEFAULT NULL,
  `tmt_dpt_code` varchar(32) DEFAULT NULL,
  `tmt_crs_code` varchar(32) DEFAULT NULL,
  `tmt_vnx_code` varchar(32) DEFAULT NULL,
  `tmt_semester` smallint(6) DEFAULT NULL,
  `tmt_week` smallint(6) DEFAULT NULL,
  `tmt_day` smallint(6) DEFAULT NULL,
  `tmt_period` smallint(6) DEFAULT NULL,
  `tmt_duration` smallint(6) DEFAULT NULL,
  `tmt_class_group` varchar(10) DEFAULT NULL,
  `tmt_display_class_group` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ott_traffic`
--

CREATE TABLE `ott_traffic` (
  `trf_id` int(10) UNSIGNED NOT NULL,
  `trf_ip` int(10) UNSIGNED DEFAULT NULL,
  `trf_session` char(32) DEFAULT NULL,
  `trf_byte_in` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `trf_byte_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `trf_ms` mediumint(9) NOT NULL,
  `trf_method` varchar(128) DEFAULT NULL,
  `trf_create_timestamp` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `ott_traffic`
--
DELIMITER $$
CREATE TRIGGER `trf_create_timestamp` BEFORE INSERT ON `ott_traffic` FOR EACH ROW SET NEW.trf_create_timestamp = UNIX_TIMESTAMP()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ott_venue`
--

CREATE TABLE `ott_venue` (
  `vnx_id` int(10) UNSIGNED NOT NULL,
  `vnx_btc_id` int(10) UNSIGNED NOT NULL,
  `vnx_code` varchar(32) NOT NULL,
  `vnx_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `raw_module`
--

CREATE TABLE `raw_module` (
  `rmd_id` int(11) NOT NULL,
  `rmd_btc_id` int(11) NOT NULL,
  -- TOCONFIGURE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `raw_student`
--

CREATE TABLE `raw_student` (
  `rst_id` int(10) UNSIGNED NOT NULL,
  `rst_btc_id` int(10) UNSIGNED NOT NULL,
  -- TOCONFIGURE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `raw_timetable`
--

CREATE TABLE `raw_timetable` (
  `rtm_id` int(10) UNSIGNED NOT NULL,
  `rtm_btc_id` int(10) UNSIGNED NOT NULL,
  -- TOCONFIGURE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ott_batch`
--
ALTER TABLE `ott_batch`
  ADD PRIMARY KEY (`btc_id`);

--
-- Indexes for table `ott_course`
--
ALTER TABLE `ott_course`
  ADD PRIMARY KEY (`crs_id`),
  ADD KEY `crs_btc_id` (`crs_btc_id`);

--
-- Indexes for table `ott_department`
--
ALTER TABLE `ott_department`
  ADD PRIMARY KEY (`dpt_id`),
  ADD KEY `dpt_btc_id` (`dpt_btc_id`);

--
-- Indexes for table `ott_location`
--
ALTER TABLE `ott_location`
  ADD PRIMARY KEY (`lct_id`),
  ADD UNIQUE KEY `lct_code` (`lct_code`);

--
-- Indexes for table `ott_period`
--
ALTER TABLE `ott_period`
  ADD PRIMARY KEY (`prd_id`),
  ADD KEY `prd_btc_id` (`prd_btc_id`);

--
-- Indexes for table `ott_setting`
--
ALTER TABLE `ott_setting`
  ADD PRIMARY KEY (`stt_id`),
  ADD KEY `stt_active` (`stt_active`),
  ADD KEY `stt_code` (`stt_code`);

--
-- Indexes for table `ott_student`
--
ALTER TABLE `ott_student`
  ADD PRIMARY KEY (`std_id`),
  ADD KEY `OPTIMISE` (`std_btc_id`,`std_student_id`,`std_activity_id`) USING BTREE;

--
-- Indexes for table `ott_sync`
--
ALTER TABLE `ott_sync`
  ADD PRIMARY KEY (`snc_id`),
  ADD UNIQUE KEY `snc_draft` (`snc_draft`),
  ADD UNIQUE KEY `snc_active` (`snc_active`),
  ADD KEY `snc_btc_id` (`snc_btc_id`),
  ADD KEY `snc_status` (`snc_status`),
  ADD KEY `snc_delete` (`snc_delete`);

--
-- Indexes for table `ott_timetable`
--
ALTER TABLE `ott_timetable`
  ADD PRIMARY KEY (`tmt_id`),
  ADD KEY `ix_optimise_lecture` (`tmt_btc_id`,`tmt_crs_code`,`tmt_module`) USING BTREE,
  ADD KEY `ix_optimise_venue` (`tmt_btc_id`,`tmt_vnx_code`) USING BTREE,
  ADD KEY `ix_optimise_department` (`tmt_btc_id`,`tmt_dpt_code`,`tmt_crs_code`,`tmt_module`) USING BTREE,
  ADD KEY `ix_optimise_student` (`tmt_btc_id`,`tmt_activity_id`) USING BTREE,
  ADD KEY `ix_optimise_module` (`tmt_btc_id`,`tmt_module`);

--
-- Indexes for table `ott_traffic`
--
ALTER TABLE `ott_traffic`
  ADD PRIMARY KEY (`trf_id`);

--
-- Indexes for table `ott_venue`
--
ALTER TABLE `ott_venue`
  ADD PRIMARY KEY (`vnx_id`),
  ADD KEY `vnx_btc_id` (`vnx_btc_id`);

--
-- Indexes for table `raw_module`
--
ALTER TABLE `raw_module`
  ADD PRIMARY KEY (`rmd_id`),
  ADD KEY `ix_optimise_module` (
    `rmd_btc_id`,
    -- TOCONFIGURE
    );

--
-- Indexes for table `raw_student`
--
ALTER TABLE `raw_student`
  ADD PRIMARY KEY (`rst_id`),
  ADD KEY `rst_btc_id` (`rst_btc_id`);

--
-- Indexes for table `raw_timetable`
--
ALTER TABLE `raw_timetable`
  ADD PRIMARY KEY (`rtm_id`),
  ADD KEY `rtm_btc_id` (`rtm_btc_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ott_batch`
--
ALTER TABLE `ott_batch`
  MODIFY `btc_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_course`
--
ALTER TABLE `ott_course`
  MODIFY `crs_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_department`
--
ALTER TABLE `ott_department`
  MODIFY `dpt_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_location`
--
ALTER TABLE `ott_location`
  MODIFY `lct_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_period`
--
ALTER TABLE `ott_period`
  MODIFY `prd_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_setting`
--
ALTER TABLE `ott_setting`
  MODIFY `stt_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_student`
--
ALTER TABLE `ott_student`
  MODIFY `std_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_sync`
--
ALTER TABLE `ott_sync`
  MODIFY `snc_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_timetable`
--
ALTER TABLE `ott_timetable`
  MODIFY `tmt_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_traffic`
--
ALTER TABLE `ott_traffic`
  MODIFY `trf_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ott_venue`
--
ALTER TABLE `ott_venue`
  MODIFY `vnx_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `raw_module`
--
ALTER TABLE `raw_module`
  MODIFY `rmd_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `raw_student`
--
ALTER TABLE `raw_student`
  MODIFY `rst_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `raw_timetable`
--
ALTER TABLE `raw_timetable`
  MODIFY `rtm_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
