-- Data to be added in this order

-- Address
-- INSERT INTO address (address_id, line_1, line_2, line_3, postcode) VALUES (3, '27 Chipping Lane', 'Redwood', 'Christchurch', '8051');

-- Event Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (7, 'Douglas', 'Woods', 'events@rogaine.org.nz', '021 242 9122');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth) 
VALUES (10, 'Amuri Basin Rogaine 2017', '2017-07-09-00:00:00', '2017-05-11-00:00:00', '2017-06-30-17:00:00', 3, 6, 8, 'N', 'N');

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (10, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (10, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (10, 'X', 'miXed');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (10, 'J', 'Junior', 0, 17);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (10, 'O', 'Open', 18, 39);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (10, 'V', 'Veteran', 40, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (10, 'SV', 'Super Veteran', 55, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (10, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (10, '6 hour', '6 hour', 50, 999);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (10, '3 hour', '3 hour', 40, 20);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (10, 'Family 3hr', 'Family 3 hour', 30, 10);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (10, 'Family 1hr', 'Family 1 hour', 15, 5);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (10, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (10, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (10, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (10, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, entry_number) VALUES (10, 100);

