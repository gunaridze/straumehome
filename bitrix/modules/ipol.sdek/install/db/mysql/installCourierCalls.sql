CREATE TABLE IF NOT EXISTS `ipol_sdek_courier_calls`
(
    `ID`                               INT(11) NOT NULL auto_increment,
    `INTAKE_UUID`                      VARCHAR(36),
    `INTAKE_NUMBER`                    INT(12),
    `STATUS_CODE`                      VARCHAR(30),
    `STATUS_DATE`                      DATETIME,
    `STATE_CODE`                       VARCHAR(20),
    `STATE_DATE`                       DATETIME,

    `TYPE`                             INT(11) NOT NULL, -- Intake type: consolidation, single order
    `CDEK_ORDER_ID`                    INT(12),
    `CDEK_ORDER_UUID`                  VARCHAR(36),  -- Reserved for future use

    `ACCOUNT`                          INT(11), -- ID from ipol_sdeklogs

    `STORE_ID`                         INT(11), -- ID from ipol_sdek_stores

    `INTAKE_DATE`                      DATETIME NOT NULL,
    `INTAKE_TIME_FROM`                 VARCHAR(10) NOT NULL,
    `INTAKE_TIME_TO`                   VARCHAR(10) NOT NULL,
    `LUNCH_TIME_FROM`                  VARCHAR(10),
    `LUNCH_TIME_TO`                    VARCHAR(10),

    `PACK_NAME`                        VARCHAR(255),
    `PACK_WEIGHT`                      INT(11),
    `PACK_LENGTH`                      INT(11),
    `PACK_WIDTH`                       INT(11),
    `PACK_HEIGHT`                      INT(11),

    `SENDER_COMPANY`                   VARCHAR(255),
    `SENDER_NAME`                      VARCHAR(255),
    `SENDER_PHONE_NUMBER`              VARCHAR(255),
    `SENDER_PHONE_ADDITIONAL`          VARCHAR(255), -- additional info about phone or second number
    `NEED_CALL`                        CHAR(1) NOT NULL DEFAULT 'N',
    `POWER_OF_ATTORNEY`                CHAR(1) NOT NULL DEFAULT 'N',
    `IDENTITY_CARD`                    CHAR(1) NOT NULL DEFAULT 'N',

    `FROM_LOCATION_CODE`               INT(11), -- CDEK location ID
    `FROM_LOCATION_ADDRESS`            VARCHAR(255),
    `COMMENT`                          VARCHAR(255),

    `STATUS`                           VARCHAR(30),
    `MESSAGE`                          TEXT,
    `OK`                               CHAR(1) NOT NULL DEFAULT 'N',
    `UPTIME`                           DATETIME NOT NULL,

    PRIMARY KEY (`ID`),
    INDEX IX_IPOL_SDEK_COURIER_CALLS_CDEK_ORDER_ID (`CDEK_ORDER_ID`),
    INDEX IX_IPOL_SDEK_COURIER_CALLS_INTAKE_NUMBER (`INTAKE_NUMBER`)
);