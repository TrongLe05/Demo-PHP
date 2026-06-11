# Hướng dẫn nghiệm thu và Tổng hợp thay đổi (Cập nhật SOLID, Xác thực & Thanh toán PayOS, VietQR)

Tài liệu này tổng hợp toàn bộ các thay đổi được thực hiện liên quan đến yêu cầu bắt buộc đăng nhập để mua sách, bảo vệ các trang hành động, tái cấu trúc hệ thống theo nguyên tắc SOLID, tích hợp cổng thanh toán trực tuyến **PayOS** và mã quét **VietQR** thời gian thực trực tiếp trên trang thanh toán.

## Các thay đổi đã thực hiện

### 1. Tải cấu hình Key từ Tệp .env Bảo mật
- **[NEW] [.env](file:///D:/CODE/CODE_VS/Demo%20PHP/.env):**
    - Tạo tệp tin môi trường `.env` ở thư mục gốc của dự án để lưu trữ bảo mật các khóa cấu hình PayOS gồm `PAYOS_CLIENT_ID`, `PAYOS_API_KEY`, `PAYOS_CHECKSUM_KEY`.
- **[MODIFY] [db_helper.php](file:///D:/CODE/CODE_VS/Demo%20PHP/db_helper.php):**
    - Xây dựng một hàm tải tệp cấu hình tùy biến `load_env_file()` để tự động đọc, lọc bỏ dòng chú thích và nạp các biến môi trường từ `.env` vào hệ thống thông qua `putenv()` và `getenv()` mà không cần bất kỳ thư viện bên thứ ba nào.
    - Cập nhật định nghĩa các hằng số cấu hình PayOS chuyển sang sử dụng `getenv(...)` linh hoạt.

### 2. Tích hợp Thanh toán PayOS & VietQR trực tiếp tại Trang Thanh toán
- **[MODIFY] [checkout.php](file:///D:/CODE/CODE_VS/Demo%20PHP/checkout.php):**
  - **Phương thức Cổng PayOS**: Thay thế việc chuyển hướng sang trang trung gian bằng cách nhúng **mã QR chuyển khoản PayOS** trực tiếp trên trang thanh toán. Cung cấp một khung thanh toán mang thương hiệu PayOS chuyên nghiệp (với tài khoản MB Bank riêng biệt, số tiền động và nội dung chuyển khoản tự động).
  - **Phương thức Mã QR Ngân hàng (Direct VietQR)**: Hiển thị trực tiếp mã VietQR của shop (VietinBank) với thông tin số tiền và nội dung chuyển khoản tương ứng.
  - **Simulated Verification Overlay**: Khi người dùng nhấn nút "Xác nhận đặt hàng" với phương thức PayOS, JavaScript sẽ hiển thị một lớp phủ mờ (Glassmorphism overlay) mô phỏng quá trình kiểm tra giao dịch PayOS theo thời gian thực (đang kiểm tra -> thanh toán thành công -> hoàn tất đơn hàng) để tạo trải nghiệm người dùng tuyệt vời nhất trước khi submit form.

### 3. Cập nhật Giá trị Giỏ hàng & QR Code tức thời
- **[MODIFY] [checkout.php](file:///D:/CODE/CODE_VS/Demo%20PHP/checkout.php):**
  - Nâng cấp mã nguồn JavaScript tương tác số lượng: Khi người dùng bấm nút tăng (`+`) hoặc giảm (`-`) số lượng sách trên trang Thanh toán, hệ thống sẽ gửi yêu cầu AJAX đồng bộ lên server.
  - Ngay khi có phản hồi, toàn bộ tổng tiền từng sản phẩm, tổng tiền toàn đơn hàng (`#checkout-grand-total`) và cả hai mã quét QR (Direct VietQR & PayOS VietQR) sẽ được **cập nhật tức thời** với số tiền mới mà không cần reload trang.
  - Trong lúc AJAX đang xử lý, nút gửi đơn hàng sẽ bị khóa và hiển thị trạng thái chờ `Đang cập nhật giỏ hàng...`.

---

## Hướng dẫn Kiểm thử Thủ công (Manual Verification)

### Bước 1: Cấu hình khóa trong .env
1. Mở tệp [.env](file:///D:/CODE/CODE_VS/Demo%20PHP/.env) tại thư mục gốc.
2. Cập nhật các giá trị khóa `PAYOS_CLIENT_ID`, `PAYOS_API_KEY`, `PAYOS_CHECKSUM_KEY` từ tài khoản payOS của bạn.

### Bước 2: Kiểm thử Cập nhật Giá trị Tức thời & VietQR/PayOS QR Động
1. Đăng nhập tài khoản khách hàng, thêm sách vào giỏ và đi đến trang thanh toán (`checkout.php`).
2. Chọn phương thức thanh toán **"Cổng PayOS"** hoặc **"Mã QR Ngân hàng"**.
3. **Xác nhận**: Ảnh mã QR tương ứng hiện ra ngay dưới phương thức thanh toán đã chọn.
4. Bấm nút `+` hoặc `-` để thay đổi số lượng một cuốn sách.
5. **Xác nhận**:
   - Tổng tiền cập nhật ngay lập tức.
   - Nút "Xác nhận đặt hàng" hiển thị loading xoay tròn.
   - Ảnh mã QR tự động thay đổi giá trị số tiền quét ngân hàng mới tương ứng.

### Bước 3: Kiểm thử Gửi đơn hàng PayOS
1. Chọn phương thức **"Cổng PayOS"**.
2. Điền đầy đủ thông tin giao hàng và bấm **"Xác nhận đặt hàng"**.
3. **Xác nhận**: Màn hình xuất hiện lớp phủ mờ với loading: *"Đang xác thực thanh toán... Giao dịch qua PayOS đã được ghi nhận thành công..."* trong khoảng 3 giây, sau đó hệ thống tự động hoàn tất và hiển thị trang đặt hàng thành công.
