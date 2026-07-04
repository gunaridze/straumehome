CREATE TABLE IF NOT EXISTS `ipol_sdek_stores`
(
    `ID`                               INT(11) NOT NULL auto_increment,
    `IS_ACTIVE`                        CHAR(1) NOT NULL DEFAULT 'Y',
    `NAME`                             VARCHAR(255) NOT NULL,

    `IS_SENDER_DATA_SENT`              CHAR(1) NOT NULL DEFAULT 'Y',
    `SENDER_COMPANY`                   VARCHAR(255),
    `SENDER_NAME`                      VARCHAR(255),
    `SENDER_PHONE_NUMBER`              VARCHAR(255),
    `SENDER_PHONE_ADDITIONAL`          VARCHAR(255), -- additional info about phone or second number
    `NEED_CALL`                        CHAR(1) NOT NULL DEFAULT 'N',
    `POWER_OF_ATTORNEY`                CHAR(1) NOT NULL DEFAULT 'N',
    `IDENTITY_CARD`                    CHAR(1) NOT NULL DEFAULT 'N',

    `IS_SELLER_DATA_SENT`              CHAR(1) NOT NULL DEFAULT 'Y',
    `SELLER_NAME`                      VARCHAR(255),
    `SELLER_PHONE`                     VARCHAR(255),
    `SELLER_ADDRESS`                   VARCHAR(255),

    `IS_DEFAULT_FOR_LOCATION`          CHAR(1) NOT NULL DEFAULT 'N',
    `IS_ADDRESS_DATA_SENT`             CHAR(1) NOT NULL DEFAULT 'Y',
    `FROM_LOCATION_CODE`               INT(11) NOT NULL, -- CDEK location ID
    `FROM_LOCATION_STREET`             VARCHAR(255) NOT NULL,
    `FROM_LOCATION_HOUSE`              VARCHAR(255),
    `FROM_LOCATION_FLAT`               VARCHAR(255),
    `COMMENT`                          VARCHAR(255),

    `INTAKE_TIME_FROM`                 VARCHAR(10) NOT NULL,
    `INTAKE_TIME_TO`                   VARCHAR(10) NOT NULL,
    `LUNCH_TIME_FROM`                  VARCHAR(10),
    `LUNCH_TIME_TO`                    VARCHAR(10),

    PRIMARY KEY (`ID`)
);