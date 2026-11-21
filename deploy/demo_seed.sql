-- Akun & data demo (aplikasi ini menyimpan password plaintext, sesuai kode asli)
USE part;
INSERT INTO users (username, email, password, role, status) VALUES
('admin1', 'admin1@demo.local', 'demo123', 'admin', 'approved'),
('courier1', 'courier1@demo.local', 'demo123', 'courier', 'approved'),
('customer1', 'customer1@demo.local', 'demo123', 'customer', 'approved');
INSERT INTO products (id, name, description, price, stock, image) VALUES
(1, 'Kampas Rem Depan', 'Kampas rem untuk motor matic 110-125cc', 65000, 30, 'produk1.png'),
(2, 'Oli Mesin 1L', 'Oli SAE 10W-30 untuk motor 4 tak', 55000, 60, 'produk2.png'),
(3, 'Busi Iridium', 'Busi iridium tahan lama', 90000, 25, 'produk3.png'),
(4, 'Aki Kering 5Ah', 'Aki maintenance free', 250000, 10, 'produk4.png'),
(5, 'Rantai Set', 'Gir + rantai motor bebek', 185000, 12, 'produk5.png'),
(6, 'Filter Udara', 'Filter udara original', 45000, 40, 'produk6.png'),
(7, 'Ban Tubeless 80/90-14', 'Ban tubeless ring 14', 210000, 15, 'produk7.png'),
(8, 'Lampu LED Depan', 'Lampu LED H4 putih terang', 120000, 20, 'produk8.png');
INSERT INTO orders (id, user_id, total_amount, address, payment_method, status) VALUES
(1, 4, 120000, 'Jl. Contoh No. 2, Semarang', 'bank_transfer', 'Selesai'),
(2, 4, 250000, 'Jl. Contoh No. 2, Semarang', 'cash_on_delivery', 'Dikonfirmasi'),
(3, 4, 90000,  'Jl. Contoh No. 2, Semarang', 'bank_transfer', 'Pending');
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (1,2,1,55000),(1,1,1,65000),(2,4,1,250000),(3,3,1,90000);
INSERT INTO reviews (product_id, user_id, review_text, rating) VALUES (2, 4, 'Oli bagus, mesin lebih halus', 5);
