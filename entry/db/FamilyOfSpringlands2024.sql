-- Data to be added in this order

-- INSERT INTO administrators VALUES(6, 'jenni_adams', 'jennia.home@gmail.com',
-- 'a596ff53f2023c7fbfe453b88e1dc82025b0355cde3f18c967412c199fb248969511cce48ca927fdbfa1695660e3e9465ee38f1ae2ebb0e4e6f4ecb3ca217ac0',
-- '0cbbaf433b41ab842abd7a2b87c59e9d88b0f4b5a0921570a385c1fcfd07beb5b903cefa4aae07e7c23380d8fcce077a34078765cf6578682ecc20ba31b8ea48');

-- Password     : j3NniAd@ms
-- Password hash: a596ff53f2023c7fbfe453b88e1dc82025b0355cde3f18c967412c199fb248969511cce48ca927fdbfa1695660e3e9465ee38f1ae2ebb0e4e6f4ecb3ca217ac0
-- Salt hash    : 0cbbaf433b41ab842abd7a2b87c59e9d88b0f4b5a0921570a385c1fcfd07beb5b903cefa4aae07e7c23380d8fcce077a34078765cf6578682ecc20ba31b8ea48

-- INSERT INTO administrators VALUES(12, 'alan_moore', 'pommie.al@gmail.com',
-- 'fd9327b02f6ea0ed1f7fdfa3d115113b9a5cec3e159b763e8d607b1ed5635e895aef87ca4635b845aa82ac243283253768d0be9942c5caca97f1a735ddada3cf',
-- '589dd840ce40823900e33743710c46b2fd872075f9ab7171bb5984565c7e66bdc4e6d9cab1ae7384c9f98a63190b9f33e5507c9adfd4aad982afa4bd3a65e406');

-- Password     : A1@nMo0r3
-- Password hash: fd9327b02f6ea0ed1f7fdfa3d115113b9a5cec3e159b763e8d607b1ed5635e895aef87ca4635b845aa82ac243283253768d0be9942c5caca97f1a735ddada3cf
-- Salt hash    : 589dd840ce40823900e33743710c46b2fd872075f9ab7171bb5984565c7e66bdc4e6d9cab1ae7384c9f98a63190b9f33e5507c9adfd4aad982afa4bd3a65e406

-- Cheque Address
-- INSERT INTO cheque_address (address_id, line_1, line_2, line_3, postcode, payable_to) VALUES (5, '', '', '', '', 'Jenni and Carsten');

-- Bank Account
-- INSERT INTO bank_account(account_id, account_name, account_number) VALUES (3, 'Spring Rogaine', '12-3482-0042820-00');
-- INSERT INTO bank_account(account_id, account_name, account_number) VALUES (9, 'Spring Rogaine', '12-3118-0633616-00');
INSERT INTO bank_account(account_id, account_name, account_number) VALUES (11, 'Family of Springlands', '03-0751-0229535-001');

-- Event Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (7, 'Douglas', 'Woods', 'events@rogaine.org.nz', '021 242 9122');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (10, 'Jenni', 'Adams', 'spring.rogaine@gmail.com', '021 642 516');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (18, 'Alan', 'Moore', 'springrogaineentries@gmail.com', '');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (19, 'Steve', 'O''Neill', 'springrogaineentries@gmail.com', '');
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (20, 'Brigit', 'Beattie', 'familyofspringlands@hotmail.com', '');
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (21, 'Kate', 'Harrison', 'familyofspringlands@hotmail.com', '');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (34, ' Family of Springlands Fundraising Rogaine', '2024-05-05-00:00:00', '2024-03-28-00:00:00', '2024-04-28-09:00:00', 5, 20, 21, 'N', 'N', 2147483647, 'N', 'Y', 'N', 11);

-- Gender
-- INSERT INTO gender (event_id, code, description) VALUES (34, 'M', 'Men');
-- INSERT INTO gender (event_id, code, description) VALUES (34, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (34, 'O', 'Open');
-- INSERT INTO gender (event_id, code, description) VALUES (34, 'F', 'Family');

-- Age Group
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (34, 'J', 'Junior', 0, 20);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (34, 'O', 'Open', 0, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (34, 'V', 'Veteran', 50, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (34, 'SV', 'Super Veteran', 55, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (34, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
-- INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (34, '6 hour', '6 hour', 50, 30);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (34, '2 hour', '2 hour', 40, 20);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (34, 'Family', 'Family', 80, 0);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (34, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (34, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (34, 'DINNER', 'Fri Dinner', 20);
INSERT INTO extra (event_id, code, description, cost) VALUES (34, 'IDENT', 'SI Dobber', 0);

-- Sequence for entry number
-- INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (34, '6 hour', 0);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (34, '2 hour', 100);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (34, 'Family', 500);

