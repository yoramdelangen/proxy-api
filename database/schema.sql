/* podcast_db */
CREATE TABLE podcasts (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  original_id varchar(255) NOT NULL DEFAULT '',
  guest_id smallint(5) unsigned DEFAULT NULL,
  type varchar(255) DEFAULT 'guest',
  episode smallint(5) unsigned DEFAULT NULL,
  title varchar(255) NOT NULL DEFAULT '',
  tags json DEFAULT NULL,
  slug varchar(255) NOT NULL DEFAULT '',
  image varchar(255) NOT NULL DEFAULT '',
  audio_length smallint(5) unsigned NOT NULL DEFAULT '0',
  audio_source varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=268 DEFAULT CHARSET=latin1;

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
  slug varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB;

/* webdav_db */
CREATE TABLE users (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARBINARY(50),
    digesta1 VARBINARY(32),
    UNIQUE(username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* digest = md5(username:realm:password) */
INSERT INTO users (username, digesta1) VALUES
('admin',  md5('admin:D$FfTFsh23*H4c#PPK?*%mX$FaVRF7$&mA9z$bajfDBpgW8JfPezVcad3&XhzTAh:@iZ3p2ikeag3UTeRZ9LwFwG6YMJt7Y4kN_PARhoWaaNc3Wg3VaHdg98p2zP*H8sg'));
