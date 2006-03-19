
create table if not exists phph_config (
	config_name varchar(255) character set utf8 collate utf8_general_ci not null unique,
	config_value text character set utf8 collate utf8_general_ci,
	primary key(config_name)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_users (
	user_id integer not null unique auto_increment,
	user_login varchar(128) character set utf8 collate utf8_general_ci not null unique,
	user_pass varchar(32) character set utf8 collate utf8_general_ci not null, -- password MD5 
	user_name varchar(256) character set utf8 collate utf8_general_ci,
	user_title varchar(256) character set utf8 collate utf8_general_ci,
	user_email varchar(256) character set utf8 collate utf8_general_ci,
	user_jid varchar(256) character set utf8 collate utf8_general_ci,
	user_www varchar(256) character set utf8 collate utf8_general_ci,
	user_from varchar(256) character set utf8 collate utf8_general_ci,
	user_registered integer(11) default 0,
	user_lastlogin integer(11) default 0,
	user_language varchar(8),
	user_admin_language varchar(8),
	user_level integer not null default 0,
	user_admin bool not null default 0,
	user_activation varchar(32),
	user_activated integer(11) not null default 0,
	primary key(user_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_sessions (
	session_id varchar(32) not null unique,
	session_start integer(11) not null,
	session_time integer(11) not null,
	session_ip char(8) not null,
	user_id integer default null references phph_users(user_id) on delete cascade,
	primary key(session_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_session_history (
	session_id varchar(32) not null unique,
	session_start integer(11) not null,
	session_ip char(8) not null,
	user_id integer default null references phph_users(user_id) on delete cascade,
	primary key(session_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_user_ip (
	user_id integer not null references phph_users(user_id) on delete cascade,
	ip char(8) not null,
	last_visit integer(11) not null,
	primary key(user_id, ip)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_groups (
	group_id integer not null unique auto_increment,
	group_name varchar(128) character set utf8 collate utf8_general_ci not null unique,
	group_description text character set utf8 collate utf8_general_ci,
	group_created integer(11) not null,
	group_creator integer references phph_users(user_id) on delete set null,
	group_level integer not null default 0,
	primary key(group_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_group_users (
	group_id integer not null references phph_groups(group_id) on delete cascade,
	user_id integer not null references phph_users(user_id) on delete cascade,
	add_time integer(11) not null,
	added_by integer references phph_users(user_id) on delete set null,
	primary key(group_id, user_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_permissions (
	user_id integer default null references phph_users(user_id) on delete cascade,
	group_id integer default null references phph_groups(group_id) on delete cascade,
	permission varchar(128) not null
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_categories (
	category_id integer not null unique auto_increment,
	category_parent integer references phph_categories(category_id) on delete cascade,
	category_name varchar(128) character set utf8 collate utf8_general_ci not null,
	category_description text character set utf8 collate utf8_general_ci,
	category_created integer(11) not null,
	category_creator integer references phph_users(user_id) on delete set null,
	category_order integer,
	primary key(category_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_photos (
	photo_id integer not null unique auto_increment,
	user_id integer not null references phph_users(user_id),
	photo_title varchar(255) character set utf8 collate utf8_general_ci,
	photo_description text character set utf8 collate utf8_general_ci,
	photo_added integer(11) not null,
	photo_approved integer(11),
	photo_approved_by integer references phph_users(user_id),
	photo_width integer,
	photo_height integer,
	primary key(photo_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_photos_categories (
	photo_id integer not null references phph_photos(photo_id),
	category_id integer not null references phph_categories(category_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_files (
	file_id integer not null unique auto_increment,
	photo_id integer not null references phph_photos(photo_id),
	file_name varchar(255) not null unique,
	file_created integer(11) not null,
	file_accessed integer(11) not null,
	file_width integer,
	file_height integer,
	file_original bool not null default 0,
	primary key(file_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_comments (
	comment_id integer not null unique auto_increment,
	photo_id integer not null references phph_photos(photo_id),
	user_id integer not null references phph_users(user_id),
	comment_title varchar(255),
	comment_text text,
	comment_date integer(11) not null,
	primary key(comment_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

create table if not exists phph_subscriptions (
	category_id integer not null references phph_categories(category_id),
	user_id integer not null references phph_users(user_id),
	subscription_date integer(11) not null,
	primary key(category_id, user_id)
) character set utf8 collate utf8_general_ci ENGINE=InnoDB;

