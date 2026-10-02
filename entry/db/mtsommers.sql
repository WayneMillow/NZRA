-- Data to be added in this order

-- Cheque Address
-- INSERT INTO cheque_address (address_id, line_1, line_2, line_3, postcode, payable_to) VALUES (5, '', '', '', '', 'Jenni and Carsten');
-- Use 3 - 27 chipping lane

-- Bank Account
-- INSERT INTO bank_account(account_id, account_name, account_number) VALUES (3, 'Spring Rogaine', '12-3482-0042820-00');
-- Use 1 - NZRA

-- Event Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (7, 'Douglas', 'Woods', 'events@rogaine.org.nz', '021 242 9122');
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (11, 'Kate', 'Oliver', 'TBA', '000 000 0000');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (10, 'Jenni', 'Adams', 'spring.rogaine@gmail.com', '021 642 516');
-- Use 8 - Wayne

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (13, 'Mt Somers Saunter', '2018-02-04-00:00:00', '2017-12-08-00:00:00', '2018-01-30-23:59:59', 3, 11,8, 'N', 'N', 400, 'N', 'Y', 'Y', 1);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (13, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (13, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (13, 'X', 'miXed');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (13, 'J', 'Junior', 0, 20);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (13, 'O', 'Open', 21, 49);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (13, 'V', 'Veteran', 50, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (10, 'SV', 'Super Veteran', 55, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (10, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (13, '6 hour', '6 hour', 50, 25);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (13, '3 hour', '3 hour', 40, 20);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (13, '1 hour', '1 hour', 20, 5);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (10, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (10, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (10, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (10, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, entry_number) VALUES (13, 100);

