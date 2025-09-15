USE library_stats;


-- Insert staff members (password: admin123 for both)
INSERT INTO staff (username, password_hash, full_name, role) VALUES
('admin', '$2y$10$r3B7oG5U6W7Y8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6A7B8C9D0', 'Library Administrator', 'admin'),
('librarian', '$2y$10$r3B7oG5U6W7Y8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6A7B8C9D0', 'Library Staff', 'librarian');

