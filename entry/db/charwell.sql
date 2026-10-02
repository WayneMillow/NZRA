-- Data to be added in this order

-- Address
-- INSERT INTO address (address_id, line_1, line_2, line_3, postcode) VALUES (3, '27 Chipping Lane', 'Redwood', 'Christchurch', '8051');

-- Event Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (7, 'Douglas', 'Woods', 'events@rogaine.org.nz', '021 242 9122');
UPDATE contact SET email='ghunternz@gmail.com' WHERE contact_id = 6; 
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (14, 'Charwell Challenge 2018', '2018-05-13-00:00:00', '2018-04-08-00:00:00', '2018-05-04-23:59:59', 3, 6, 8, 'N', 'N', 400, 'N', 'Y', 'Y', 1);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (14, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (14, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (14, 'X', 'miXed');
INSERT INTO gender (event_id, code, description) VALUES (14, 'F', 'Family');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (14, 'YJ', 'Year 7-10', 11, 14);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (14, 'YS', 'Year 11-13', 15, 17);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (14, 'O', 'Open', 18, 39);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (14, 'V', 'Veteran', 40, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (14, 'SV', 'Super Veteran', 55, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (14, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (14, '6 hour', '6 hour', 50, 20);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (14, '3 hour', '3 hour', 35, 10);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (14, '1.5 hour', '1.5 hour', 20, 5);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (14, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (14, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (14, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (14, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, entry_number) VALUES (14, 100);

