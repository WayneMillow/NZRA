-- Data to be added in this order

-- Address
-- INSERT INTO address (address_id, line_1, line_2, line_3, postcode) VALUES (3, '27 Chipping Lane', 'Redwood', 'Christchurch', '8051');

-- Event Contact
--INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (15, 'Viv', 'Prince', 'vivienneprince@hotmail.com', '');
-- Entry Contact
--INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (16, 'Dave', 'Armstrong', 'djarmstrong@hotmail.co.nz', '');

-- Bank Account
--INSERT INTO bank_account (account_id, account_name, account_number) VALUES (7, 'PAPO', '03-0823-0425931-00');


-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
, max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (28, 'Heights of Winter 2021', '2021-06-19-00:00:00', '2021-05-01-00:00:00', '2021-06-15-23:59:59', 3, 2, 8, 'N', 'N', 400, 'N', 'Y', 'N', 1);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (28, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (28, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (28, 'X', 'miXed');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (28, 'J', 'Junior', 0, 22);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (28, 'O', 'Open', 23, 39);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (28, 'V', 'Veteran', 40, 54);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (28, 'SV', 'Super Veteran', 55, 64);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (28, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (28, '12 hour', '12 hour', 75, 30);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (28, '06 hour', '6 hour', 45, 15);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (28, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (28, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (28, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (28, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (28, '12 hour', 100);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (28, '06 hour', 500);

