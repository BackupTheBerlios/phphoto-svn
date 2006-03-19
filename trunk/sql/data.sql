insert into phph_config (config_name, config_value) values ("site_url", "http://phphoto.nmax.eu.org");
insert into phph_config (config_name, config_value) values ("default_language", "en");
insert into phph_config (config_name, config_value) values ("default_admin_language", "en");
insert into phph_config (config_name, config_value) values ("site_title", "PHPhoto test site");
insert into phph_config (config_name, config_value) values ("cookie_domain", "http://phphoto.nmax.eu.org");
insert into phph_config (config_name, config_value) values ("cookie_name", "phph");
insert into phph_config (config_name, config_value) values ("cookie_path", "/");
insert into phph_config (config_name, config_value) values ("session_lifetime", "3600");
insert into phph_config (config_name, config_value) values ("session_cookie_name", "sid");
insert into phph_config (config_name, config_value) values ("max_file_size", "150");
insert into phph_config (config_name, config_value) values ("max_width", "640");
insert into phph_config (config_name, config_value) values ("max_height", "600");
insert into phph_config (config_name, config_value) values ("auto_approve", "0");

insert into phph_users (user_login, user_pass, user_email, user_registered, user_level, user_admin) values
('admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@admin.pl', UNIX_TIMESTAMP(), 100, 1);

