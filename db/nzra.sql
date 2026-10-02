
--------------------------------------------------------------------------------
CREATE USER 'sec_user'@'localhost' IDENTIFIED BY 'eKcGZr59zAa2BEWU';

GRANT SELECT, INSERT, UPDATE ON `nzra`.* TO 'sec_user'@'localhost';

--------------------------------------------------------------------------------
CREATE TABLE administrators (
  admin_id int NOT NULL AUTO_INCREMENT,
  username VARCHAR(30) NOT NULL,
  email VARCHAR(50) NOT NULL,
  password CHAR(128) NOT NULL,
  salt CHAR(128) NOT NULL,
  PRIMARY KEY (admin_id)
  );

CREATE TABLE login_attempts (
  user_id int NOT NULL,
  login_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  );

--------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS address (
  address_id int(11) NOT NULL AUTO_INCREMENT,
  line_1 varchar(32) NOT NULL,
  line_2 varchar(32) NOT NULL,
  line_3 varchar(32) NOT NULL,
  postcode char(5) NOT NULL,
  PRIMARY KEY (address_id)
);

CREATE TABLE IF NOT EXISTS contact (
  contact_id int(11) NOT NULL AUTO_INCREMENT,
  first_name varchar(32) NOT NULL,
  last_name varchar(32) NOT NULL,
  email varchar(64) NOT NULL,
  phone varchar(16) NOT NULL,
  PRIMARY KEY (contact_id)
);

CREATE TABLE IF NOT EXISTS event (
  event_id int(11) NOT NULL AUTO_INCREMENT,
  description varchar(64) NOT NULL,
  event_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  entry_start_date timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  entry_close_date timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  address_id int(11) NOT NULL,
  event_contact_id int(11) NOT NULL,
  entry_contact_id int(11) NOT NULL,
  PRIMARY KEY (event_id),
);

CREATE TABLE IF NOT EXISTS sub_event (
  sub_event_id int(11) NOT NULL AUTO_INCREMENT,
  event_id int(11) NOT NULL,
  description varchar(16) NOT NULL,
  senior_price int(11) NOT NULL,
  junior_price int(11) NOT NULL,
  PRIMARY KEY (sub_event_id),
);

CREATE TABLE IF NOT EXISTS entry (
  entry_id int(11) NOT NULL AUTO_INCREMENT,
  gender char(1) NOT NULL, 
  age_group char(2) NOT NULL,
  event varchar(16) NOT NULL,
  payment_type varchar(16) NOT NULL,
  payment_value smallint(6) NOT NULL,
  waiver char(1) NOT NULL,
  comments varchar(1024) NOT NULL DEFAULT '',
  status char(8) NOT NULL DEFAULT '', 
  team_name varchar(32) NOT NULL,
  entry_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  payment_received smallint(6) NOT NULL,
  PRIMARY KEY (entry_id)
);
  
CREATE TABLE IF NOT EXISTS member (
  member_id int(11) NOT NULL AUTO_INCREMENT,
  first_name varchar(32) NOT NULL,
  last_name varchar(32) NOT NULL,
  street varchar(32) NOT NULL,
  town varchar(32) NOT NULL,
  postcode char(5) NOT NULL,
  email varchar(64) NOT NULL,
  phone varchar(16) NOT NULL,
  PRIMARY KEY (member_id)
  );
  
CREATE TABLE IF NOT EXISTS entrant (
  event_id int(11) NOT NULL,
  entry_id int(11) NOT NULL,
  member_id int(11) NOT NULL,
  entry_fee smallint(6) NOT NULL,
  PRIMARY KEY (event_id, entry_id, member_id)
  );
  
--------------------------------------------------------------------------------
ALTER TABLE entrant
  ADD CONSTRAINT entrant_event_fk FOREIGN KEY (event_id) REFERENCES event (event_id),
  ADD CONSTRAINT entrant_entry_fk FOREIGN KEY (entry_id) REFERENCES entry (entry_id),
  ADD CONSTRAINT entrant_member_fk FOREIGN KEY (member_id) REFERENCES member (member_id);

ALTER TABLE event
  ADD CONSTRAINT event_address_fk FOREIGN KEY (address_id) REFERENCES address (address_id),
  ADD CONSTRAINT event_contact_fk_1 FOREIGN KEY (event_contact_id) REFERENCES contact (contact_id),
  ADD CONSTRAINT event_contact_fk_2 FOREIGN KEY (entry_contact_id) REFERENCES contact (contact_id);

ALTER TABLE sub_event
  ADD CONSTRAINT sub_event_event_fk FOREIGN KEY (event_id) REFERENCES event (event_id);

--------------------------------------------------------------------------------
INSERT INTO administrators VALUES(1, 'wayne_millow', 'wayne.millow@gmail.com',
'00807432eae173f652f2064bdca1b61b290b52d40e429a7d295d76a71084aa96c0233b82f1feac45529e0726559645acaed6f3ae58a286b9f075916ebf66cacc',
'f9aab579fc1b41ed0c44fe4ecdbfcdb4cb99b9023abb241a6db833288f4eea3c02f76e0d35204a8695077dcf81932aa59006423976224be0390395bae152d4ef');
  
INSERT INTO `address` (`address_id`, `line_1`, `line_2`, `line_3`, `postcode`) VALUES
(1, '108/4 Holmes Road', 'RD2', 'Tai Tapu', '7672'),
(2, '123 Sesame Street', 'The Bronyx', 'New York', '1234');

INSERT INTO `contact` (`contact_id`, `first_name`, `last_name`, `email`, `phone`) VALUES
(1, 'Peter', 'Squires', 'events@rogaine.org.nz', '(03) 329 1714'),
(2, 'Emma', 'de Lacey', 'entries@rogaine.org.nz', ''),
(4, 'Kermit', 'The Frog', 'wayne.millow@gmail.com', '(03) 123 4567'),
(5, 'Big', 'Bird', 'wayne.millow@gmail.com', '(03) 123 4567'),
(6, 'Grant', 'Hunter', 'g.hunter@clear.net.nz', ' (03) 351 6369');

INSERT INTO `event` (`event_id`, `description`, `event_date`, `entry_start_date`, `entry_close_date`, `address_id`, `event_contact_id`, `entry_contact_id`) VALUES
(1, 'Test Closed', '2015-02-26 11:00:00', '2014-12-31 11:00:00', '2015-02-19 10:59:59', 1, 1, 2),
(2, 'NZ Rogaine Championships 2016', '2016-02-26 11:00:00', '2015-12-31 11:00:00', '2016-02-19 10:59:59', 1, 1, 2),
(4, 'TEST second event for Kaikoura', '2016-03-18 11:00:00', '2016-01-19 11:00:00', '2016-02-18 10:59:59', 2, 4, 5),
(5, 'The Charwell Challenge', '2016-03-18 11:00:00', '2016-02-10 11:00:00', '2016-03-11 04:00:00', 1, 6, 2);

INSERT INTO `sub_event` (`sub_event_id`, `event_id`, `description`, `senior_price`, `junior_price`) VALUES
(1, 2, '24 hour', 80, 50),
(2, 2, '7 hour', 40, 20),
(5, 4, '6 hour', 50, 25),
(6, 4, '3 hour', 30, 15),
(11, 5, '6 hour', 50, 25),
(12, 5, '3 hour', 35, 15),
(13, 5, 'Family', 35, 15);

    