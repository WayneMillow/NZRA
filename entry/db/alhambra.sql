-- Data to be added in this order

-- INSERT INTO administrators VALUES(6, 'jenni_adams', 'jennia.home@gmail.com',
-- 'a596ff53f2023c7fbfe453b88e1dc82025b0355cde3f18c967412c199fb248969511cce48ca927fdbfa1695660e3e9465ee38f1ae2ebb0e4e6f4ecb3ca217ac0',
-- '0cbbaf433b41ab842abd7a2b87c59e9d88b0f4b5a0921570a385c1fcfd07beb5b903cefa4aae07e7c23380d8fcce077a34078765cf6578682ecc20ba31b8ea48');

-- Password     : j3NniAd@ms
-- Password hash: a596ff53f2023c7fbfe453b88e1dc82025b0355cde3f18c967412c199fb248969511cce48ca927fdbfa1695660e3e9465ee38f1ae2ebb0e4e6f4ecb3ca217ac0
-- Salt hash    : 0cbbaf433b41ab842abd7a2b87c59e9d88b0f4b5a0921570a385c1fcfd07beb5b903cefa4aae07e7c23380d8fcce077a34078765cf6578682ecc20ba31b8ea48

-- Cheque Address
INSERT INTO cheque_address (address_id, line_1, line_2, line_3, postcode, payable_to) VALUES (6, '210 Signal Hill Road', 'Opoho', 'Dunedin', '9010', 'Dunedin Rogaines');

-- Bank Account
INSERT INTO bank_account(account_id, account_name, account_number) VALUES (6, 'Dunedin Rogaines', '38-9005-0281789-00');

-- Event Contact
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (14, 'Bob', 'Cunninghame', 'signalhill@xtra.co.nz', '(03) 473 8330');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (17, 'Jenni', 'Adams', 'spring.rogaine@gmail.com', '021 642 516');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (23, 'Alhambra Antics Rogaine / Town and Around', '2019-11-02-00:00:00', '2019-10-01-00:00:00', '2019-10-29-23:59:59', 6, 14, 8, 'N', 'N', 2147483647, 'N', 'Y', 'Y', 6);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (23, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (23, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (23, 'X', 'miXed');
INSERT INTO gender (event_id, code, description) VALUES (23, 'F', 'Family');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (23, 'J', 'Junior', 0, 20);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (23, 'O', 'Open', 21, 49);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (23, 'V', 'Veteran', 50, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (23, 'SV', 'Super Veteran', 55, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (23, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (23, '6 hour', '6 hour', 45, 25);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (23, '3 hour', '3 hour', 35, 15);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (23, '1.5 hour', '1.5 hour', 25, 5);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (23, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (23, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (23, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (23, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (23, '6 hour', 0);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (23, '3 hour', 150);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (23, '1.5 hour', 300);

