-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 13, 2026 lúc 06:49 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ban_sach_online`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `quantity` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `category_id`, `price`, `image`, `description`, `featured`, `quantity`) VALUES
(1, 'Đắc Nhân Tâm', 'Dale Carnegie', 1, 85000.00, 'uploads/books/0040753922c128bd1d57b9bb50b6e84f.jpg', 'Cuốn sách nghệ thuật thu phục lòng người.', 1, 13),
(2, 'Nhà Giả Kim', 'Paulo Coelho', 2, 79000.00, 'uploads/books/050b5d582cafcbe093bd5554a31dab59.webp', 'Hành trình đi tìm vận mệnh của cậu bé chăn cừu.', 1, 19),
(3, 'Clean Code', 'Robert C. Martin', 3, 250000.00, 'uploads/books/d5cd1eb5ae182e76498a6328175f9cfd.png', 'Sách hướng dẫn viết mã sạch cho lập trình viên.', 1, 5),
(5, 'Tuổi Trẻ Đáng Giá Bao Nhiêu', 'Rosie Nguyễn', 1, 80000.00, 'uploads/books/dac7b5867519da4b272982583fcbf264.jpg', 'Sách truyền cảm hứng cho giới trẻ về việc học tập, làm việc và trải nghiệm.', 0, 8),
(6, 'Tôi Thấy Hoa Vàng Trên Cỏ Xanh', 'Nguyễn Nhật Ánh', 2, 95000.00, 'uploads/books/b14a368fa968898e5ef136adec2a9cbd.jpg', 'Câu chuyện tuổi thơ đầy xúc động và ý nghĩa tại một làng quê nghèo.', 0, 10),
(7, 'Thiết Kế Web Với HTML, CSS & JS', 'Jon Duckett', 3, 220000.00, 'uploads/books/d9292b9d8313c9e7f66ae731f7d8416b.jpg', 'Cuốn sách trực quan và dễ hiểu nhất để học thiết kế giao diện website.', 0, 6),
(8, 'Tư Duy Nhanh Và Chậm', 'Daniel Kahneman', 7, 185000.00, 'uploads/books/0a67a88ee90e66a596261172584e4790.jpg', 'Khám phá hai hệ thống tư duy chi phối mọi quyết định của con người.', 0, 14);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Tâm lý - Kỹ năng sống', 'Sách hướng dẫn về kỹ năng sống, giao tiếp, và phát triển bản thân.'),
(2, 'Tiểu thuyết', 'Các tác phẩm văn học hư cấu mang tính chất tiểu thuyết.'),
(3, 'Công nghệ thông tin', 'Sách về lập trình, thiết kế web, hệ quản trị cơ sở dữ liệu và công nghệ.'),
(4, 'Văn học Việt Nam', 'Các tác phẩm văn học nổi tiếng của các tác giả Việt Nam.'),
(5, 'Khoa học vũ trụ', 'Sách nghiên cứu và phổ biến kiến thức về thiên văn và vũ trụ.'),
(7, 'Tâm lý học', 'Các cuốn sách nghiên cứu về tâm lý, hành vi con người.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(15) NOT NULL,
  `customer_address` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Chờ xác nhận',
  `payment_method` varchar(50) NOT NULL DEFAULT 'COD',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `customer_name`, `customer_phone`, `customer_address`, `status`, `payment_method`, `created_at`) VALUES
(1, 3, 329000.00, 'Trần Thị B', '0912345678', '123 Đường Nguyễn Trãi, Quận 1, TP. Hồ Chí Minh', 'Đã giao', 'Ví điện tử', '2024-05-15 03:30:00'),
(2, 2, 185000.00, 'Nguyễn Văn A', '0777895656', '1', 'Đã hủy', 'COD', '2026-06-13 16:22:15'),
(3, 2, 320000.00, 'Nguyễn Văn A', '0777895656', '123', 'Đã giao', 'COD', '2026-06-13 16:22:27'),
(4, 2, 79000.00, 'Nguyễn Văn A', '0777895656', '12', 'Đã xác nhận', 'COD', '2026-06-13 16:41:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `book_id`, `quantity`, `price`) VALUES
(1, 1, 3, 1, 250000.00),
(2, 1, 2, 1, 79000.00),
(3, 2, 8, 1, 185000.00),
(5, 3, 1, 2, 85000.00),
(6, 4, 2, 1, 79000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `email`, `password`, `role`) VALUES
(1, 'admin', 'Quản Trị Viên', 'admin@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'admin'),
(2, 'nguyenvana', 'Nguyễn Văn A', 'nguyenvana@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'user'),
(3, 'tranthib', 'Trần Thị B', 'tranthib@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'user'),
(4, 'lehoangc', 'Lê Hoàng C', 'lehoangc@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'user');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
