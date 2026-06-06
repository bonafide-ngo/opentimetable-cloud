-- https://gitea.bonafide.ngo/mu.ie/mu.opentimetable.cloud/issues/26
ALTER TABLE
    `ott_period`
ADD
    INDEX(`prd_semester`);