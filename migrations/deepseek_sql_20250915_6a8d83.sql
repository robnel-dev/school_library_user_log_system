USE library_stats;

-- Insert sample students
INSERT INTO students (student_no, full_name, year_course, contact_no) VALUES
('PM100TL', 'Leven Phillyea R. Livbo', '1st Yr - BSA', '09123456789'),
('1310000M', 'Regalia C. Perco Medusa', '2nd Yr - BST', '09123456788'),
('28100032', 'Motto Lipiola Castardio', '1st Yr - BST', '09123456787'),
('29100596', 'Hiraba Pontino E. Comilla', '1st Yr - BST', '09123456786'),
('25100056', 'Crema Angola B. Luvell', '1st Yr - MIG', '09123456785'),
('25100059', 'Franciscus Beni Zamnacula', '1st Yr - BST', '09123456784'),
('23100031', 'Kim Toma N. Hernada', '1st Yr - BST', '09123456783'),
('23100064', 'Montecitt A. Pernosquico', '1st Yr - BSA', '09123456782'),
('23200021', 'Julia Forma Pommonadaglia', '1st Yr - BST', '09123456781'),
('23200024', 'Giorentina Exteria', '1st Yr - BST', '09123456780'),
('24100034', 'Fugliu S. Gemella', '1st Yr - BST', '09123456779'),
('24100029', 'Eugène G. Gualan', '1st Yr - BST', '09123456778'),
('24100033', 'Latte Alberta', '1st Yr - BST', '09123456777'),
('24200025', 'Airm Willm. Dellina', '1st Yr - BST', '09123456776'),
('22100031', 'Angola Maci C. Trana', '1st Yr - BST', '09123456775'),
('22100043', 'Everkin J. Teatro', '1st Yr - BST', '09123456774'),
('24200027', 'Lebraz Francisco', '1st Yr - BST', '09123456773'),
('24100034', 'P. Vaughn Gebhardt', '1st Yr - BST', '09123456772'),
('24100030', 'E. Viticapo Amica', '1st Yr - BST', '09123456771'),
('24100028', 'Agri Orilliano Shein', '1st Yr - BST', '09123456770');

-- Insert staff members (password: admin123 for both)
INSERT INTO staff (username, password_hash, full_name, role) VALUES
('admin', '$2y$10$r3B7oG5U6W7Y8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6A7B8C9D0', 'Library Administrator', 'admin'),
('librarian', '$2y$10$r3B7oG5U6W7Y8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6A7B8C9D0', 'Library Staff', 'librarian');

-- Insert sample visits
INSERT INTO visits (student_id, date, time_in, time_out, purpose, notes) VALUES
(1, '2025-04-05', '2025-04-05 10:00:00', '2025-04-05 10:40:00', 'Study', ''),
(2, '2025-04-06', '2025-04-06 10:10:00', '2025-04-06 10:37:00', 'Study', ''),
(3, '2025-04-07', '2025-04-07 10:15:00', '2025-04-07 10:57:00', 'Study', ''),
(4, '2025-04-08', '2025-04-08 10:40:00', '2025-04-08 12:22:00', 'Research', ''),
(5, '2025-04-09', '2025-04-09 10:47:00', '2025-04-09 11:41:00', 'Borrowing', ''),
(6, '2025-04-10', '2025-04-10 10:00:00', '2025-04-10 12:19:00', 'Study', ''),
(7, '2025-04-11', '2025-04-11 10:00:00', '2025-04-11 12:50:00', 'Group Work', 'Group study session'),
(8, '2025-04-12', '2025-04-12 10:00:00', '2025-04-12 12:44:00', 'Study', ''),
(9, '2025-04-13', '2025-04-13 10:00:00', '2025-04-13 12:44:00', 'Research', ''),
(10, '2025-04-14', '2025-04-14 10:00:00', '2025-04-14 12:44:00', 'Returning', '');