create table if not exists yandex_metrika_actions (
    ID int(18) not null auto_increment,
    UID varchar(50) not null,
    EC_ACTION text not null,
    SID varchar(50) not null,
    primary key (ID),
    index IX_SID (SID)
);