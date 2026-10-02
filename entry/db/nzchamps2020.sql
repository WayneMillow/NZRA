-- Data to be added in this order

-- Address
-- INSERT INTO address (address_id, line_1, line_2, line_3, postcode) VALUES (3, '27 Chipping Lane', 'Redwood', 'Christchurch', '8051');

-- Event Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (12, 'Matt', 'Bixley', 'mattbixley72@gmail.com', '');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (26, 'NZ Rogaine Champs 2020', '2020-11-07-00:00:00', '2020-09-11-00:00:00', '2020-10-31-23:59:59', 3, 7, 8, 'N', 'Y', 2147483647, 'N', 'Y', 'N', 1);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (26, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (26, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (26, 'X', 'miXed');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (26, 'J', 'Junior', 16, 22);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (26, 'O', 'Open', 23, 39);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (26, 'V', 'Veteran', 40, 54);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (26, 'SV', 'Super Veteran', 55, 64);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (26, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (26, '24 hour', '24 hour', 100, 0);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (26, '15 hour', '15 in 24 hour', 100, 0);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (26, 'BUSTO', 'Bus to event', 0);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (26, 'BUSFROM', 'Bus from event', 0);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (26, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (26, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (26, '24 hour', 0);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (26, '15 hour', 500);


