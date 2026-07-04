create table if not exists ipol_sdek
(
	ID int(11) NOT NULL auto_increment,
	MESS_ID int(6),
	PARAMS text,
	ORDER_ID int(11),
	SOURCE int(1),
	SDEK_ID varchar(20),
	SDEK_UID varchar(36),
	STATUS varchar(6),
	MESSAGE text,
	ACCOUNT int(3),
	OK varchar(1),
	UPTIME varchar(10),
	PRIMARY KEY(ID),
	INDEX ix_ipol_sdekoi (ORDER_ID)
);