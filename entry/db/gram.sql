-- Data to be added in this order

INSERT INTO administrators VALUES(7, 'mike_harding', 'macharding@outlook.com',
'2179ec92ccd36795206551965f0a09f72b17cad0f3d0cb2acdd2dab923657c4a9145e0429b9a5eaed3d75c462c2b6d0b0268fd141328b265dc194a11a8ee686c',
'845a4647250dd41eb12f634c531cd76c7cc3661feb0d5bd5f8d5261f51877a21c1ea22b70ce03d131901500423a348e250a9e25f39e7cc37038a10fabb7a493f');

-- Password     : H@rdM1k3
-- Password hash: 2179ec92ccd36795206551965f0a09f72b17cad0f3d0cb2acdd2dab923657c4a9145e0429b9a5eaed3d75c462c2b6d0b0268fd141328b265dc194a11a8ee686c
-- Salt hash    : 845a4647250dd41eb12f634c531cd76c7cc3661feb0d5bd5f8d5261f51877a21c1ea22b70ce03d131901500423a348e250a9e25f39e7cc37038a10fabb7a493f

-- Cheque Address
-- INSERT INTO cheque_address (address_id, line_1, line_2, line_3, postcode, payable_to) VALUES (5, '', '', '', '', 'Jenni and Carsten');

-- Bank Account
INSERT INTO bank_account(account_id, account_name, account_number) VALUES (4, 'Geraldine High SChool PTA', '03-0839-0077839-00');

-- Event Contact
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (13, 'Janet', '', 'janet.jh@gmail.com', '');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (17, 'Jenni', 'Adams', 'spring.rogaine@gmail.com', '021 642 516');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (19, 'GRAM 2019', '2019-03-31-00:00:00', '2019-02-18-00:00:00', '2019-03-25-23:59:59', 5, 13, 8, 'N', 'N', 2147483647, 'N', 'Y', 'N', 4);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (19, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (19, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (19, 'X', 'miXed');
INSERT INTO gender (event_id, code, description) VALUES (19, 'F', 'Family');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (19, 'Y1', 'Year 7-8', 11, 12);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (19, 'Y2', 'Year 9-10', 13, 14);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (19, 'Y3', 'Year 11-13', 15, 17);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (19, 'O', 'Open', 21, 44);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (19, 'V', 'Veteran', 45, 54);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (19, 'SV', 'Super Veteran', 55, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (19, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (19, '6 hour', '6 hour', 60, 30);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (19, '3 hour', '3 hour', 60, 30);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (19, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (19, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (19, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (19, 'BREAKF', 'Sat Breakfast', 15);

-- Sequence for entry number
INSERT INTO sequence (event_id, entry_number) VALUES (19, 100);

