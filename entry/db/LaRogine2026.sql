-- Data to be added in this order

-- INSERT INTO administrators VALUES(12, 'peter_squires', 'petersquires2@gmail.com',
-- 'a4fe85f6fa020d66697d2303b07738afea8644aaf835ecc8ba2d30250bc183b8d5fbbecbeac5da19141b7e3362a4079d864920f6c9d8b3a6c0afe2dfb35c7920',
-- '451483157fbc8f121e56e65e8ccee4c8ccffdd2a2013851121575fac95974fc521ee8fad52249bc01337772122f1844a01cede7380cba47dcd36d9bd2d4d5f9e');
-- Password     : sQ1r3sP3te
-- Password hash: a4fe85f6fa020d66697d2303b07738afea8644aaf835ecc8ba2d30250bc183b8d5fbbecbeac5da19141b7e3362a4079d864920f6c9d8b3a6c0afe2dfb35c7920
-- Salt hash    : 451483157fbc8f121e56e65e8ccee4c8ccffdd2a2013851121575fac95974fc521ee8fad52249bc01337772122f1844a01cede7380cba47dcd36d9bd2d4d5f9e

-- INSERT INTO administrators VALUES(12, 'hadee_thompson', 'hadee.thompson@gmail.com',
-- '1b9d2c94702b868180b03f452c3d0bf2f471e9342fed9d9d3365cda86b7f6a68e0738804b6858e8a64473dd6e48e80ef49c0f0811b757669b6f5d9a72136fb80',
-- 'ed289cf008a1c9e820874aa5df3712632f0db6267811bfc599c1e75839f8c0189543bd83eac85a2cd356d0d4a1010f89bd54d3d5fa80565b52000faa0938d092');
-- Password     : h@d33tH0m0
-- Password hash: 1b9d2c94702b868180b03f452c3d0bf2f471e9342fed9d9d3365cda86b7f6a68e0738804b6858e8a64473dd6e48e80ef49c0f0811b757669b6f5d9a72136fb80
-- Salt hash    : ed289cf008a1c9e820874aa5df3712632f0db6267811bfc599c1e75839f8c0189543bd83eac85a2cd356d0d4a1010f89bd54d3d5fa80565b52000faa0938d092

-- INSERT INTO administrators VALUES(13, 'nicolas_dubois', 'nicolasyves.dubois@gmail.com',
-- 'c12ea84642b6149e22d4d08b47ca7a6cde0ea9b7493699204fb98a00db6ee6b6c398d20fd6ab8efed15dac11cacc98ffb75e39b4ba807a8f93594d0111273b76',
-- '75aa542e9ddeb0b8effda96d681c43ed7d8ea1213605faead07fc23db7c0bcecb7984c3a347ff85427bf6617fe1c0814458c7cb888b42f4f2ec0b09f23c3225f');
-- Password     : n1c0Lasyv3s
-- Password hash: c12ea84642b6149e22d4d08b47ca7a6cde0ea9b7493699204fb98a00db6ee6b6c398d20fd6ab8efed15dac11cacc98ffb75e39b4ba807a8f93594d0111273b76
-- Salt hash    : 75aa542e9ddeb0b8effda96d681c43ed7d8ea1213605faead07fc23db7c0bcecb7984c3a347ff85427bf6617fe1c0814458c7cb888b42f4f2ec0b09f23c3225f

-- Cheque Address
-- INSERT INTO cheque_address (address_id, line_1, line_2, line_3, postcode, payable_to) VALUES (5, '', '', '', '', 'Jenni and Carsten');

-- Bank Account
-- INSERT INTO bank_account(account_id, account_name, account_number) VALUES (3, 'Spring Rogaine', '12-3482-0042820-00');
-- INSERT INTO bank_account(account_id, account_name, account_number) VALUES (9, 'Spring Rogaine', '12-3118-0633616-00');
-- INSERT INTO bank_account(account_id, account_name, account_number) VALUES (11, 'Family of Springlands', '03-0751-0229535-001');

-- Event Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (7, 'Douglas', 'Woods', 'events@rogaine.org.nz', '021 242 9122');
-- Entry Contact
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (8, 'Wayne', 'Millow', 'entries@rogaine.org.nz', '027 212 5785');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (10, 'Jenni', 'Adams', 'spring.rogaine@gmail.com', '021 642 516');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (18, 'Alan', 'Moore', 'springrogaineentries@gmail.com', '');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (19, 'Steve', 'O''Neill', 'springrogaineentries@gmail.com', '');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (20, 'Brigit', 'Beattie', 'familyofspringlands@hotmail.com', '');
-- INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (21, 'Kate', 'Harrison', 'familyofspringlands@hotmail.com', '');
INSERT INTO contact (contact_id, first_name, last_name, email, phone) VALUES (22, 'Hadee', 'Thompson', 'secretary@rogaine.org.nz', '');

-- Event
INSERT INTO event (event_id, description, event_date, entry_start_date, entry_close_date, address_id, event_contact_id, entry_contact_id, use_country, use_date_of_birth
,max_entrants, pay_credit_card, pay_direct_credit, pay_cheque, account_id) 
VALUES (35, ' La Rogaine', '2026-01-30-00:00:00', '2025-08-01-00:00:00', '2026-01-01-00:00:00', 5, 22, 08, 'N', 'N', 2147483647, 'N', 'Y', 'N', 1);

-- Gender
INSERT INTO gender (event_id, code, description) VALUES (35, 'M', 'Men');
INSERT INTO gender (event_id, code, description) VALUES (35, 'W', 'Women');
INSERT INTO gender (event_id, code, description) VALUES (35, 'X', 'miXed');

-- Age Group
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (35, 'J', 'Junior', 10, 22);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (35, 'O', 'Open', 23, 39);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (35, 'V', 'Veteran', 40, 54);
INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (35, 'SV', 'Super Veteran', 55, 99);
-- INSERT INTO age_group (event_id, code, description, lower_age, upper_age) VALUES (35, 'UV', 'Ultra Veteran', 65, 99);

-- Sub Events
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (35, '12 hour', '12 hour', 90, 45);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (35, '6 hour', '6 hour', 50, 25);
INSERT INTO sub_event (event_id, code, description, senior_price, junior_price) VALUES (35, '3 hour', '3 hour', 35, 15);

-- Extra costs
-- INSERT INTO extra (event_id, code, description, cost) VALUES (35, 'BUSTO', 'Bus to event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (35, 'BUSFROM', 'Bus from event', 40);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (35, 'DINNER', 'Fri Dinner', 20);
-- INSERT INTO extra (event_id, code, description, cost) VALUES (35, 'IDENT', 'SI Dobber', 0);

-- Sequence for entry number
-- INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (35, '6 hour', 0);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (35, '12 hour', 100);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (35, '6 hour', 400);
INSERT INTO sequence (event_id, sub_event_code, entry_number) VALUES (35, '3 hour', 700);

