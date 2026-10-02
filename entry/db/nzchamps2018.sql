-- Data to be added in this order

-- Address
-- INSERT INTO address (address_id, line_1, line_2, line_3, postcode) VALUES (3, '27 Chipping Lane', 'Redwood', 'Christchurch', '8051');

-- Event Contact
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (12, 'Matt', 'Bixley', 'mattbixley72@gmail.com', '');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (15, 'NZ Rogaine Champs 2018', '2018-11-24-00:00:00', '2018-04-11-00:00:00', '2018-11-16-23:59:59', 3, 12, 8, 'N', 'Y', 400, 'N', 'Y', 'Y', 1);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (15, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (15, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (15, 'X', 'miXed');
INSERT INTO gender (event_id, code, description) VALUES (15, 'A', 'Adventure');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (15, 'J', 'Junior', 16, 22);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (15, 'O', 'Open', 23, 39);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (15, 'V', 'Veteran', 40, 54);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (15, 'SV', 'Super Veteran', 55, 64);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (15, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (15, '24 hour', '24 hour', 90, 60);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (15, '06 hour', '6 hour', 50, 25);

-- Extra costs
INSERT INTO extra (event_id, code, description, cost) VALUES (15, 'BUSTO', 'Bus to event', 0);
INSERT INTO extra (event_id, code, description, cost) VALUES (15, 'BUSFROM', 'Bus from event', 0);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (15, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (15, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, entry_number) VALUES (15, 100);

