-- Data to be added in this order

-- Address
-- INSERT INTO address (address_id, line_1, line_2, line_3, postcode) VALUES (3, '27 Chipping Lane', 'Redwood', 'Christchurch', '8051');

-- Event Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (7, 'Douglas', 'Woods', 'events@rogaine.org.nz', '021 242 9122');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');

-- Bank Account
--

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (27, 'Greta Valley Rogaine 2021', '2021-04-11-00:00:00', '2021-01-27-00:00:00', '2021-04-07-23:59:59', 1, 7, 8, 'N', 'N', 2147483647, 'N', 'Y', 'N', 5);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (27, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (27, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (27, 'X', 'miXed');
INSERT INTO gender (event_id, code, description) VALUES (27, 'F', 'Family');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (27, 'J', 'Junior', 10, 17);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (27, 'O', 'Open', 18, 39);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (27, 'V', 'Veteran', 40, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (27, 'SV', 'Super Veteran', 55, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (27, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (27, '6 hour', '6 hour', 50, 25);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (27, '3 hour', '3 hour', 40, 20);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (27, '1.5 hour', '1.5 hour', 20, 5);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (27, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (27, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (27, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (27, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (27, '6 hour', 0);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (27, '3 hour', 150);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (27, '1.5 hour', 300);

