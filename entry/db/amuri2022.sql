-- Data to be added in this order

-- INSERT INTO administrators VALUES(11, 'renee_crossley', 'crossley@amuri.net',
-- 'b66209cc0a490048e381820399a0ab3dd580d87ad8f23787bb150bd56a14116e5df9497f975a4c18867f8fbb6159bbf432f48993dd402e2dfbbb5a2610e29009',
-- 'f21dc8fbbd6be0de6b2cafef623a14adadfe29b7c9182a4ea66db15ad744cff695e2846b213287caa3ac435d18f5bd0afafb31ae271aa6e5e3bd15dd590bfe4a');

-- Password     : Amur1Cr03s
-- Password hash: b66209cc0a490048e381820399a0ab3dd580d87ad8f23787bb150bd56a14116e5df9497f975a4c18867f8fbb6159bbf432f48993dd402e2dfbbb5a2610e29009
-- Salt hash    : f21dc8fbbd6be0de6b2cafef623a14adadfe29b7c9182a4ea66db15ad744cff695e2846b213287caa3ac435d18f5bd0afafb31ae271aa6e5e3bd15dd590bfe4a

-- Address
-- INSERT INTO address (address_id, line_1, line_2, line_3, postcode) VALUES (3, '27 Chipping Lane', 'Redwood', 'Christchurch', '8051');

-- Event Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (7, 'Douglas', 'Woods', 'events@rogaine.org.nz', '021 242 9122');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (17, 'Renee', 'Dampier', 'crossley@amuri.net', '027 993 646');

-- Bank Account
-- INSERT INTO bank_account(account_id, account_name, account_number) VALUES (5, 'R Dampier-Crossley', '01-0819-0224862-00');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (31, 'Amuri Rogaine 2022', '2022-07-03-00:00:00', '2022-05-27-00:00:00', '2022-06-30-17:00:00', 1, 7, 17, 'N', 'N', 2147483647, 'N', 'Y', 'N', 5);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (31, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (31, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (31, 'X', 'miXed');
INSERT INTO gender (event_id, code, description) VALUES (31, 'F', 'Family');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (31, 'J', 'Junior', 10, 17);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (31, 'O', 'Open', 18, 39);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (31, 'V', 'Veteran', 40, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (31, 'SV', 'Super Veteran', 55, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (31, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (31, '6 hour', '6 hour', 50, 25);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (31, '3 hour', '3 hour', 40, 20);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (31, '1.5 hour', '1.5 hour', 20, 5);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (31, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (31, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (31, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (31, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (31, '6 hour', 100);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (31, '3 hour', 300);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (31, '1.5 hour', 600);

