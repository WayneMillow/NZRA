-- CREATE USER sec_user@localhost IDENTIFIED BY 'eKcGZr59zAa2BEWU';
-- GRANT SELECT, INSERT, UPDATE ON nzra.* TO 'sec_user'@'localhost';

DROP FUNCTION IF EXISTS get_entry_number;
DROP TABLE IF EXISTS entrant;
DROP TABLE IF EXISTS member;
DROP TABLE IF EXISTS entry_extra;
DROP TABLE IF EXISTS entry_credit_card;
DROP TABLE IF EXISTS cheque_address;
DROP TABLE IF EXISTS bank_account;
DROP TABLE IF EXISTS find_a_partner;
DROP TABLE IF EXISTS country;
DROP TABLE IF EXISTS entry;
DROP TABLE IF EXISTS gender;
DROP TABLE IF EXISTS age_group;
DROP TABLE IF EXISTS sub_event;
DROP TABLE IF EXISTS extra;
DROP TABLE IF EXISTS sequence;
DROP TABLE IF EXISTS event;
DROP TABLE IF EXISTS address;
DROP TABLE IF EXISTS contact;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS administrators;

CREATE TABLE administrators (
  admin_id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(30) NOT NULL,
  email VARCHAR(50) NOT NULL,
  password CHAR(128) NOT NULL,
  salt CHAR(128) NOT NULL,
  PRIMARY KEY (admin_id)
  );

CREATE TABLE login_attempts (
  user_id INT NOT NULL,
  login_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  );

CREATE TABLE contact(
  contact_id INT NOT NULL AUTO_INCREMENT,
  first_name VARCHAR(32) NOT NUll,
  last_name VARCHAR(32) NOT NUll,
  email VARCHAR(64) NOT NULL,
  phone VARCHAR(16) NOT NULL DEFAULT '',
  PRIMARY KEY (contact_id)
  );
  
CREATE TABLE cheque_address (
  address_id INT NOT NULL AUTO_INCREMENT ,
  line_1 VARCHAR(32) NOT NULL, 
  line_2 VARCHAR(32) NOT NULL DEFAULT '',
  line_3 VARCHAR(32) NOT NULL DEFAULT '',
  postcode CHAR(5) NOT NULL DEFAULT '',
  payable_to VARCHAR(64) NOT NULL DEFAULT 'New Zealand Rogaining Association',
  PRIMARY KEY (address_id) 
  );
 
CREATE TABLE bank_account (
  account_id INT NOT NULL AUTO_INCREMENT ,
  account_name VARCHAR(32) NOT NULL DEFAULT 'NZRA',
  account_number VARCHAR(32) NOT NULL DEFAULT '03 0674 0197735 00',
  PRIMARY KEY (account_id) 
  );
 
CREATE TABLE event (
  event_id INT NOT NULL AUTO_INCREMENT,
  description VARCHAR(64) NOT NULL,
  event_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  entry_start_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  entry_close_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  address_id INT NOT NULL DEFAULT 0,
  event_contact_id INT NOT NULL DEFAULT 0,
  entry_contact_id INT NOT NULL DEFAULT 0,
  use_country CHAR(1) NOT NULL DEFAULT 'N',
  use_date_of_birth CHAR(1) NOT NULL DEFAULT 'N',
  max_entrants INT NOT NULL DEFAULT 2147483647,
  pay_credit_card CHAR(1) NOT NULL DEFAULT 'N', 
  pay_direct_credit CHAR(1) NOT NULL DEFAULT 'Y', 
  pay_cheque CHAR(1) NOT NULL DEFAULT 'Y',
  account_id INT NOT NULL DEFAULT 1,
  PRIMARY KEY(event_id)
  );
  
CREATE TABLE gender (
  event_id INT NOT NULL ,
  code CHAR(1) NOT NULL ,
  description VARCHAR(16) NOT NULL ,
  PRIMARY KEY (event_id, code)
  );
  
CREATE TABLE age_group (
  event_id INT NOT NULL ,
  code CHAR(2) NOT NULL ,
  description VARCHAR(16) NOT NULL ,
  lower_age SMALLINT NOT NULL DEFAULT 0,
  upper_age SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (event_id, code)
  );
  
CREATE TABLE sub_event (
  event_id INT NOT NULL ,
  sequence INT NOT NULL DEFAULT 0,
  code CHAR(8) NOT NULL ,
  description VARCHAR(16) NOT NULL ,
  senior_price INT NOT NULL DEFAULT 0, 
  junior_price INT NOT NULL DEFAULT 0,
  max_entrants INT NOT NULL DEFAULT 2147483647,
  PRIMARY KEY (event_id, code)
  );
  
CREATE TABLE extra (
  event_id INT NOT NULL ,
  sequence INT NOT NULL ,
  code CHAR(8) NOT NULL ,
  description VARCHAR(16) NOT NULL ,
  cost INT NOT NULL DEFAULT 0, 
  PRIMARY KEY (event_id, code)
  );
  
CREATE TABLE entry (
  entry_id INT NOT NULL AUTO_INCREMENT,
  team_number SMALLINT UNSIGNED NOT NULL,
  team_name VARCHAR(32) NOT NULL,
  gender_code CHAR(1) NOT NULL,
  age_group_code CHAR(2) NOT NULL,
  sub_event_code CHAR(8) NOT NULL,
  payment_type VARCHAR(16) NOT NULL,
  payment_value SMALLINT NOT NULL,
  payment_received SMALLINT NOT NULL DEFAULT 0,
  waiver CHAR(1) NOT NULL DEFAULT '',
  comments VARCHAR(1024) NOT NULL DEFAULT '',
  status CHAR(8) NOT NULL DEFAULT '', 
  entry_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (entry_id)
  );
  
CREATE TABLE entry_extra (
  entry_id INT NOT NULL,
  code CHAR(8) NOT NULL,
  quantity SMALLINT NOT NULL,
  PRIMARY KEY (entry_id, code)
);

CREATE TABLE entry_credit_card (
  transaction_id VARCHAR(16) NOT NULL,
  entry_id INT NOT NULL,
  payment_value SMALLINT NOT NULL,
  payment_request_time TIMESTAMP NOT NULL,
  digital_order_time TIMESTAMP,
  error_code INT,
  error_message VARCHAR(256),
  amount INT,
  card_number CHAR(16),
  card_expiry CHAR(8),
  PRIMARY KEY (transaction_id)
);

CREATE TABLE member (
  member_id INT NOT NULL AUTO_INCREMENT,
  first_name VARCHAR(32) NOT NULL,
  last_name VARCHAR(32) NOT NULL,
  street VARCHAR(32) NOT NULL,
  town VARCHAR(32) NOT NULL,
  country VARCHAR(32) NOT NULL DEFAULT 'New Zealand',
  postcode CHAR(5) NOT NULL,
  email VARCHAR(64) NOT NULL,
  phone VARCHAR(16) NOT NULL,
  date_of_birth DATE NOT NULL DEFAULT '1900-01-01',
  PRIMARY KEY (member_id)
  );
  
CREATE TABLE entrant (
  event_id INT NOT NULL,
  entry_id INT NOT NULL,
  member_id INT NOT NULL,
  entry_fee SMALLINT NOT NULL,
  PRIMARY KEY (event_id, entry_id, member_id)
  );
  
CREATE TABLE sequence (
  event_id INT NOT NULL,
  entry_number INT UNSIGNED NOT NULL DEFAULT 100,
  PRIMARY KEY (event_id)
);

CREATE TABLE find_a_partner (
  find_id INT NOT NULL AUTO_INCREMENT,
  event_id INT NOT NULL,
  full_name VARCHAR(32) NOT NULL,
  contact_email VARCHAR(64) NOT NULL,
  gender_code CHAR(1) NOT NULL,
  age_group_code CHAR(2) NOT NULL,
  experience VARCHAR(64) NOT NULL,
  expectations VARCHAR(64) NOT NULL,
  status CHAR(3) NOT NULL DEFAULT '',
  when_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (find_id)
);

CREATE TABLE country (
  country_id VARCHAR(32) NOT NULL,
  country_code CHAR(3) NOT NULL,
  PRIMARY KEY (country_id)
);

CREATE INDEX find_a_partner_1
  ON find_a_partner (event_id);
  
-- CREATE FUNCTION get_entry_number(event_id INT) RETURNS INT UNSIGNED 
-- NOT DETERMINISTIC
-- MODIFIES SQL DATA
-- BEGIN
-- 	UPDATE sequence SET entry_number = last_insert_id(entry_number + 1) WHERE event_id = event_id;
-- 	RETURN last_insert_id();
-- END; 

ALTER TABLE entrant
  ADD CONSTRAINT entrant_event_fk FOREIGN KEY (event_id)
  REFERENCES event (event_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE entrant
  ADD CONSTRAINT entrant_entry_fk FOREIGN KEY (entry_id)
  REFERENCES entry (entry_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE entrant
  ADD CONSTRAINT entrant_member_fk FOREIGN KEY (member_id)
  REFERENCES member (member_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE extra
  ADD CONSTRAINT extra_event_fk FOREIGN KEY (event_id)
  REFERENCES event (event_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE sequence
  ADD CONSTRAINT sequence_event_fk FOREIGN KEY (event_id)
  REFERENCES event (event_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE sub_event
  ADD CONSTRAINT sub_event_event_fk FOREIGN KEY (event_id)
  REFERENCES event (event_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE age_group
  ADD CONSTRAINT age_group_event_fk FOREIGN KEY (event_id)
  REFERENCES event (event_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE gender
  ADD CONSTRAINT gender_event_fk FOREIGN KEY (event_id)
  REFERENCES event (event_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE event
  ADD CONSTRAINT event_contact_fk_1 FOREIGN KEY (event_contact_id)
  REFERENCES contact (contact_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;
  
-- ALTER TABLE event
--   ADD CONSTRAINT event_contact_fk_2 FOREIGN KEY (entry_contact_id)
--   REFERENCES contact (contact_id)
--   ON DELETE RESTRICT
--   ON UPDATE RESTRICT;
  
ALTER TABLE event
  ADD CONSTRAINT event_address_fk FOREIGN KEY (address_id)
  REFERENCES address (address_id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE find_a_partner
  ADD INDEX (event_id);
  
-- Salted password
INSERT INTO administrators (admin_id, username, email, password, salt) 
VALUES(1, 'wayne_millow', 'wayne.millow@gmail.com',
'00807432eae173f652f2064bdca1b61b290b52d40e429a7d295d76a71084aa96c0233b82f1feac45529e0726559645acaed6f3ae58a286b9f075916ebf66cacc',
'f9aab579fc1b41ed0c44fe4ecdbfcdb4cb99b9023abb241a6db833288f4eea3c02f76e0d35204a8695077dcf81932aa59006423976224be0390395bae152d4ef');


-- Data to be added in this order
INSERT INTO address (address_id, line_1, line_2, line_3, postcode) VALUES (1, '108/4 Holmes Road', 'RD2', 'Tai Tapu', '7672');

-- Event Contact
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (1, 'Peter', 'Squires', 'peter.squires@ihug.co.nz', '(03) 329 1714');
-- Entry Contact
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (2, 'Emma', 'de Lacey', 'nzra.entries@gmail.com', '');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth) 
VALUES (1, 'NZ Rogaine Championships 2016', '2016-10-08-00:00:00', '2016-01-01-00:00:00', '2016-12-31-23:59:59', 1, 1, 2, 'Y', 'Y');

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (1, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (1, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (1, 'X', 'miXed');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (1, 'J', 'Junior', 16, 21);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (1, 'O', 'Open', 22, 44);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (1, 'V', 'Veteran', 45, 54);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (1, 'SV', 'Super Veteran', 55, 64);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (1, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (1, '24 hour', '24 hour', 90, 70);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (1, '12 hour', '12 hour', 70, 50);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (1, '6 hour', '6 hour', 50, 25);

-- Extra costs
INSERT INTO extra (event_id, code, description, cost) VALUES (1, 'BUSTO', 'Bus to event', 25);
INSERT INTO extra (event_id, code, description, cost) VALUES (1, 'BUSFROM', 'Bus from event', 25);
INSERT INTO extra (event_id, code, description, cost) VALUES (1, 'BEANIE', 'Event Beanie', 10);

-- Sequence for entry number
INSERT INTO sequence (event_id, entry_number) VALUES (1, 100);

-- entries are added via application
-- BUT

-- Entry
INSERT INTO entry (entry_id, gender_code, age_group_code, sub_event_code, payment_type, payment_value, waiver, comments, status, team_name, entry_date, payment_received) 
VALUES (1, 'X', 'SV', '24 hour', 'cheque', 150, 'Y', 'These are comments', 'recd', 'The A team', '2016-01-01-00:00:00', 150);

-- Member
INSERT INTO member (member_id, first_name, last_name, street, town, country, postcode, email, phone, date_of_birth) 
VALUES (1, 'Wayne', 'Millow', '471 Tweed Street', 'Invercargill', 'New Zealand', '6015', 'wayne.millow@gmail.com', '02168902', '1956-10-08');
INSERT INTO member (member_id, first_name, last_name, street, town, country, postcode, email, phone, date_of_birth) 
VALUES (2, 'Elizabeth', 'Tobin', '65 Warden Street', 'Dunedin', 'New Zealand', '1234', 'liz.millow@gmail.com', '033529076', '1955-09-28');

-- Entrant
INSERT INTO entrant (event_id, entry_id, member_id, entry_fee) VALUES(1, 1, 1, 75);
INSERT INTO entrant (event_id, entry_id, member_id, entry_fee) VALUES(1, 1, 2, 75);

-- Entry Extras
INSERT INTO entry_extra (entry_id, code, quantity) VALUES(1, 'BUSTO', 2);
INSERT INTO entry_extra (entry_id, code, quantity) VALUES(1, 'BUSFROM', 2);
INSERT INTO entry_extra (entry_id, code, quantity) VALUES(1, 'BEANIE', 1);

-- Country
INSERT INTO country (country_id, country_code) VALUES('NZ', 'NZL');
INSERT INTO country (country_id, country_code) VALUES('NEWZEALAND', 'NZL');
INSERT INTO country (country_id, country_code) VALUES('AUSTRALIA', 'AUS');
