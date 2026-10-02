-- Data to be added in this order

-- Address
-- INSERT INTO address (address_id, line_1, line_2, line_3, postcode) VALUES (3, '27 Chipping Lane', 'Redwood', 'Christchurch', '8051');

-- Event Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (12, 'Matt', 'Bixley', 'mattbixley72@gmail.com', '');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (23, 'Nick', 'Smith', 'nicknzsmith@gmail.com', '');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (36, 'NZ Rogaine Champs 2026', '2026-11-21-00:00:00', '2026-08-20-00:00:00', '2026-11-15-23:59:59', 3, 23, 8, 'N', 'Y', 2147483647, 'N', 'Y', 'N', 1);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (36, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (36, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (36, 'X', 'miXed');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (36, 'J', 'Junior', 16, 22);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (36, 'O', 'Open', 23, 39);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (36, 'V', 'Veteran', 40, 54);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (36, 'SV', 'Super Veteran', 55, 64);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (36, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (36, '24 hour', '24 hour', 150, 0);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (36, '15 hour', '15 in 24 hour', 125, 0);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (36, '12 hour', '12 hour', 100, 0);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (36, '06 hour', '6 hour', 50, 25);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (36, '03 hour', '3 hour', 30, 15);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (36, 'BUSTO', 'Bus to event', 0);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (36, 'BUSFROM', 'Bus from event', 0);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (36, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (36, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (36, '24 hour', 0);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (36, '15 hour', 200);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (36, '12 hour', 400);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (36, '06 hour', 600);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (36, '03 hour', 800);


