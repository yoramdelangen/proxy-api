CREATE TABLE podcasts (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  original_id varchar(255) NOT NULL DEFAULT '',
  guest_id smallint(5) unsigned DEFAULT NULL,
  type varchar(255) DEFAULT 'guest',
  episode tinyint(3) unsigned NULL DEFAULT NULL,
  title varchar(255) NOT NULL DEFAULT '',
  tags json DEFAULT NULL,
  slug varchar(255) NOT NULL DEFAULT '',
  image varchar(255) NOT NULL DEFAULT '',
  audio_length smallint(5) unsigned NOT NULL DEFAULT '0',
  audio_source varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE guests (
  id smallint(8) unsigned NOT NULL AUTO_INCREMENT,
  hash_name varchar(255) NOT NULL DEFAULT '',
  name varchar(255) NOT NULL DEFAULT '',
  image varchar(255) NOT NULL DEFAULT '',
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE tags (
  id tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  original_id varchar(255) NOT NULL DEFAULT '',
  title varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB;