# Tài Liệu Chi Tiết Dự Án: 3FC Bookstore - Website Bán Sách Online

Tài liệu này cung cấp cái nhìn toàn diện về dự án **3FC Bookstore** (Đề tài 2 - Website bán sách online thuộc môn học *Thiết kế Web với PHP*). Dự án được thiết kế và xây dựng theo mô hình tối giản nhưng áp dụng các nguyên tắc kiến trúc phần mềm sạch (Clean Architecture), tích hợp thanh toán trực tuyến tự động và trải nghiệm người dùng cao cấp (UX/UI).

---

## 1. Giới Thiệu Chung

- **Tên dự án**: 3FC Bookstore
- **Môn học**: Thiết kế Web với PHP
- **Đơn vị thực hiện**: Nhóm sinh viên Khoa Công nghệ Thông tin - Trường Đại học Đồng Tháp
- **Mục tiêu**: Xây dựng một website bán sách trực tuyến hiện đại, hỗ trợ người dùng tìm kiếm, xem chi tiết, quản lý giỏ hàng, thanh toán qua cổng ngân hàng (VietQR/PayOS) và trang quản trị quản lý sách/đơn hàng cho Admin.

### Công nghệ sử dụng:
- **Ngôn ngữ**: PHP 8.x (PHP thuần hướng đối tượng)
- **Cơ sở dữ liệu**: MySQL (kết nối thông qua PDO bảo mật)
- **Giao diện**: HTML5, Vanilla CSS3 (tùy biến hoàn toàn), Bootstrap 5 (chỉ dùng grid và cấu trúc gốc), FontAwesome 6 icons.
- **Tương tác**: JavaScript ES6 (AJAX Fetch API).
- **Cổng thanh toán**: PayOS API & VietQR API.

---

## 2. Kiến Trúc & Nguyên Tắc Thiết Kế

Dự án áp dụng chặt chẽ các nguyên tắc **SOLID** nhằm tăng tính bảo trì và mở rộng code:

- **Single Responsibility Principle (SRP)**: Mỗi lớp chỉ chịu trách nhiệm cho một tác vụ duy nhất.
  - `DatabaseConnection`: Chỉ xử lý kết nối CSDL và chạy migrations tự động.
  - `BookRepository`: Chuyên trách truy vấn, thêm, sửa, xóa sách.
  - `CartManager`: Quản lý logic giỏ hàng (trong database và session).
  - `OrderRepository`: Xử lý lưu đơn hàng và trạng thái đơn hàng.
  - `PayOSService`: Chuyên trách giao tiếp với cổng API PayOS.
- **Dependency Inversion Principle (DIP)**: Các module cấp cao không phụ thuộc trực tiếp vào module cấp thấp mà phụ thuộc vào trừu tượng (Interface).
  - Kết nối CSDL được tiêm (inject) qua `DatabaseConnectionInterface`.
- **Environment Configuration**: Bảo mật thông tin cấu hình (`.env`) không được ghi đè trực tiếp trong mã nguồn. Hệ thống tự động nạp từ file `.env` nhờ hàm `load_env_file()`.

---

## 3. Cấu Trúc Cơ Sở Dữ Liệu

Hệ thống cơ sở dữ liệu bao gồm 5 bảng chính, có liên kết khóa ngoại chặt chẽ:

### Sơ đồ quan hệ thực thể (ERD):
- **users** (id, username, password, fullname, email, role, created_at)
- **books** (id, title, author, category, price, image, description, featured, quantity)
- **orders** (id, user_id, total_price, customer_name, customer_phone, customer_address, status, payment_method, created_at)
- **order_details** (id, order_id, book_id, quantity, price)
- **cart** (id, user_id, book_id, quantity, created_at)

---

## 4. Các Chức Năng Chính

### 4.1. Phân Hệ Khách Hàng (Customer Frontend)
- **Trang chủ (`index.php`)**:
  - Lọc sách theo thể loại, tìm kiếm sách theo tên/tác giả.
  - Thêm vào giỏ hàng bằng AJAX không reload trang kèm theo hiệu ứng toast thông báo.
- **Chi tiết sách (`book-detail.php`)**:
  - Hiển thị mô tả đầy đủ, đánh giá giả lập và danh sách sách cùng thể loại liên quan.
  - Chức năng **Mua ngay** đưa thẳng sản phẩm đến trang thanh toán bỏ qua giỏ hàng.
- **Giỏ hàng (`cart.php`)**:
  - Tăng, giảm số lượng sách hoặc xóa sách bằng AJAX, tự động cập nhật lại tổng tiền tức thời không cần reload.
- **Thanh toán (`checkout.php`)**:
  - Nhập thông tin nhận hàng (Họ tên, SĐT, Địa chỉ).
  - Tích hợp **Cổng PayOS** nhúng mã QR tự động thay đổi số tiền động.
  - Tích hợp **Mã QR Ngân hàng (Direct VietQR)** hiển thị QR quét có sẵn số tiền và nội dung chuyển khoản chuẩn hóa dạng `Thanh toan don hang #madonhang`.
- **Trang tĩnh phụ trợ**:
  - `about.php` (Giới thiệu nhóm phát triển, sứ mệnh).
  - `promotions.php` (Các voucher ưu đãi, tích hợp nút Copy mã nhanh).
  - `contact.php` (Thông tin liên hệ, form đóng góp ý kiến gửi AJAX không reload).

### 4.2. Phân Hệ Quản Trị (Admin Dashboard - `admin.php`)
- **Bảng điều khiển**: Thống kê doanh thu, tổng số sách, tổng số đơn hàng đã hoàn tất.
- **Quản lý Sách**:
  - Thêm, sửa thông tin sách trực quan.
  - Xử lý ảnh bìa thông minh: Cho phép tải ảnh trực tiếp từ máy tính lên thư mục `uploads/books/` hoặc nhập liên kết ảnh trực tuyến (URL). Khi sửa thông tin, nếu không có nhu cầu đổi ảnh bìa, hệ thống sẽ tự giữ nguyên ảnh cũ.
  - Xóa sách bằng AJAX không bị tải lại trang.
- **Quản lý Đơn hàng**: Xem danh sách đơn, lọc đơn theo phương thức thanh toán, cập nhật trạng thái đơn (Chờ xác nhận, Đã xác nhận, Đang giao, Đã giao, Đã hủy).

---

## 5. Thiết Kế UX/UI & Mỹ Thuật Giao Diện

Giao diện của **3FC Bookstore** được định hướng theo phong cách thiết kế hiện đại và sang trọng:

- **Chủ đề Glassmorphism**:
  - Sử dụng các lớp phủ kính mờ (`backdrop-filter: blur(16px)`) kết hợp viền bán trong suốt (`rgba(255, 255, 255, 0.1)`) tạo cảm giác chiều sâu, cao cấp.
- **Hệ thống theme Sáng/Tối (Light/Dark Mode)**:
  - Đồng bộ màu sắc bằng các biến CSS (`:root` và `[data-theme="dark"]`). Người dùng có thể chuyển đổi theme linh hoạt, toàn bộ các thành phần (nút, bảng, card, thanh điều hướng, chân trang) tự động thích ứng.
- **Thông báo Toast tự phát triển (`showToast`)**:
  - Loại bỏ hoàn toàn hộp thoại `alert()` truyền thống thô kệch. Thay thế bằng các thẻ Toast thông báo góc màn hình chuyển động trượt mượt mà với 4 trạng thái: `success` (xanh), `danger`/`error` (đỏ), `warning` (vàng), `info` (xanh dương).
- **Chân trang (Footer) sang trọng**:
  - Thiết kế mờ, có đường line phát sáng glow màu đồng bộ ở viền trên. Các huy hiệu thanh toán (COD, VietQR, PayOS, Visa) tự thiết kế sang trọng và co giãn tốt trên di động.
- **Micro-animations**:
  - Hiệu ứng rê chuột (hover) trên các card sách, biểu tượng mạng xã hội hay liên kết chân trang tạo cảm giác giao diện "sống động", phản hồi tốt với hành động người dùng.

---

## 6. Hướng Dẫn Cài Đặt & Chạy Dự Án (Localhost)

### Bước 1: Chuẩn bị môi trường
- Cài đặt phần mềm **XAMPP** (hỗ trợ PHP từ 8.0 trở lên và MySQL).
- Khởi động service **Apache** và **MySQL** trên XAMPP Control Panel.

### Bước 2: Sao chép mã nguồn
- Copy toàn bộ thư mục dự án `Demo-PHP` vào đường dẫn thư mục root của XAMPP:
  `C:\xampp\htdocs\Demo-PHP`

### Bước 3: Nhập Cơ sở dữ liệu (Database Import)
1. Mở trình duyệt, truy cập trang quản trị database: `http://localhost/phpmyadmin/`
2. Tạo mới một cơ sở dữ liệu có tên là `ban_sach_online` với mã hóa `utf8mb4_general_ci`.
3. Chọn cơ sở dữ liệu vừa tạo, chuyển sang tab **Import**, nhấn **Choose File** chọn tệp tin SQL tại:
   `C:\xampp\htdocs\Demo-PHP\database\CSDL.txt` (hoặc tệp `.sql` tương đương).
4. Nhấn **Import** ở cuối trang để hoàn tất nạp cấu trúc và dữ liệu mẫu.

### Bước 4: Cấu hình Khóa Môi trường
- Tại thư mục gốc `C:\xampp\htdocs\Demo-PHP`, mở tệp tin cấu hình `.env`.
- Cấu hình kết nối MySQL và các khóa API PayOS (nếu có):
  ```ini
  DB_HOST=127.0.0.1
  DB_NAME=ban_sach_online
  DB_USER=root
  DB_PASS=

  # Cấu hình ví dụ cho PayOS
  PAYOS_CLIENT_ID=your_client_id
  PAYOS_API_KEY=your_api_key
  PAYOS_CHECKSUM_KEY=your_checksum_key

  # Thông tin tài khoản ngân hàng nhận VietQR của Shop
  MERCHANT_BANK_ID=vietinbank
  MERCHANT_ACCOUNT_NO=113366668888
  MERCHANT_ACCOUNT_NAME=3FC BOOKSTORE
  ```

### Bước 5: Truy cập và Kiểm thử
- Mở trình duyệt và truy cập: `http://localhost/Demo-PHP/public/index.php`
- **Tài khoản đăng nhập demo**:
  - **Khách hàng**: `user` / mật khẩu: `123`
  - **Quản trị viên**: `admin` / mật khẩu: `123`

---

## 7. Kết Luận

Dự án **3FC Bookstore** là kết quả của việc kết hợp lập trình hướng đối tượng PHP thuần, quy tắc thiết kế Clean Code cùng các công nghệ tương tác AJAX và API cổng thanh toán phổ biến hiện nay. Dự án không chỉ đạt tiêu chí thẩm mỹ sang trọng mà còn mang lại giá trị thực tế cao với tốc độ xử lý nhanh chóng, an toàn.
