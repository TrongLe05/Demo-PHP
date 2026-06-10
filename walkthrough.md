# Hướng dẫn nghiệm thu và Tổng hợp thay đổi (Cập nhật)

Tài liệu này tổng hợp toàn bộ các thay đổi được thực hiện liên quan đến giao diện sáng, cơ chế tiến trình đơn hàng tuần tự của Admin, và tính năng Khách hàng tự hủy đơn kèm hoàn kho tự động.

## Các thay đổi đã thực hiện

### 1. Thay đổi Giao diện Sáng (Light Theme)
- **[MODIFY] [style.css](file:///D:/CODE/CODE_VS/Demo%20PHP/style.css):**
  - Chuyển đổi toàn bộ biến màu sắc chủ đạo `:root` sang tông sáng: nền sáng nhạt (`#f8fafc`), nền thẻ trắng tinh (`#ffffff`), chữ màu tối (`#0f172a`), và bóng đổ mờ nhẹ.
  - Cập nhật gradient của tiêu đề chính `.hero-banner h1` để hiển thị tương phản và sắc nét trên nền sáng.
  - Tích hợp thêm các lớp CSS ghi đè dropdown menu mặc định của Bootstrap sang giao diện sáng (`.dropdown-menu-dark` được tùy chỉnh lại nền sáng chữ tối).
  - Tinh chỉnh các viền bảng biểu và hộp chi tiết đơn hàng sang viền mờ nhạt hiện đại.

### 2. Xử lý Hủy đơn hàng và Hoàn kho tập trung
- **[MODIFY] [db_helper.php](file:///D:/CODE/CODE_VS/Demo%20PHP/db_helper.php):**
  - Bổ sung hàm `cancel_order($order_id)` thực hiện trong một Transaction: cập nhật trạng thái đơn hàng thành `Đã hủy`, đồng thời tự động hoàn lại số lượng sách đã đặt mua vào kho tồn của cửa hàng (`books.quantity`).

### 3. Xem và Hủy đơn hàng phía Khách hàng
- **[MODIFY] [orders.php](file:///D:/CODE/CODE_VS/Demo%20PHP/orders.php):**
  - Bổ sung nút **"Hủy đơn hàng"** (màu đỏ) ở chân mỗi thẻ đơn hàng.
  - Điều kiện hiển thị: Nút chỉ xuất hiện đối với các đơn hàng đang có trạng thái là `Chờ xác nhận` hoặc `Đã xác nhận`.
  - Khi click, hệ thống yêu cầu xác nhận trước khi gọi hàm hủy đơn hàng hoàn kho, sau đó hiển thị thông báo thành công.

### 4. Cơ chế chuyển tiếp trạng thái tuần tự phía Admin
- **[MODIFY] [admin.php](file:///D:/CODE/CODE_VS/Demo%20PHP/admin.php):**
  - Loại bỏ ô chọn dropdown trạng thái cũ.
  - Thay thế bằng **nút hành động chuyển đổi tuần tự** thông minh theo trạng thái hiện tại:
    - Nếu là `Chờ xác nhận`: Hiển thị nút **"Xác nhận đơn hàng"** (chuyển sang `Đã xác nhận`).
    - Nếu là `Đã xác nhận`: Hiển thị nút **"Bắt đầu giao hàng"** (chuyển sang `Đang giao`).
    - Nếu là `Đang giao`: Hiển thị nút **"Hoàn thành giao hàng"** (chuyển sang `Đã giao`).
    - Nếu là `Đã giao` hoặc `Đã hủy`: Không cần xử lý thêm.
  - Hiển thị nút **"Hủy đơn hàng"** (màu đỏ) bên cạnh nút hành động trên nếu trạng thái chưa phải là `Đã giao` hoặc `Đã hủy`.
  - Tích hợp hàm `cancel_order($order_id)` khi hủy đơn từ phía admin để đảm bảo hoàn trả số lượng sách tồn kho chính xác.

---

## Hướng dẫn Kiểm thử Thủ công (Manual Verification)

### Bước 1: Kiểm thử Giao diện Sáng
1. Khởi động server: `php -S localhost:8000`.
2. Mở trình duyệt truy cập ứng dụng.
3. *Xác nhận:*
   - Nền trang web có màu sáng nhạt hài hòa, chữ tối màu rõ nét và dễ đọc.
   - Menu dropdown tài khoản hiển thị nền trắng chữ đen sang trọng.
   - Các bảng giỏ hàng, bảng quản trị và chi tiết sách hiển thị đồng bộ theo giao diện sáng.

### Bước 2: Kiểm thử Nút bấm chuyển trạng thái tuần tự của Admin
1. Đăng nhập tài khoản Admin (`admin@gmail.com` / `123456`).
2. Vào mục **Quản lý đơn hàng**.
3. Tìm đơn hàng mới đặt có trạng thái `Chờ xác nhận`.
4. *Xác nhận:*
   - Thấy xuất hiện nút xanh chàm **"Xác nhận đơn hàng"** và nút viền đỏ **"Hủy đơn hàng"**.
   - Bấm **Xác nhận đơn hàng**: Trạng thái cập nhật thành `Đã xác nhận`.
   - Nút hành động tự động đổi thành nút xám sáng **"Bắt đầu giao hàng"**.
   - Bấm **Bắt đầu giao hàng**: Trạng thái đổi thành `Đang giao`.
   - Nút hành động đổi thành nút xanh lá **"Hoàn thành giao hàng"**.
   - Bấm **Hoàn thành giao hàng**: Trạng thái đổi thành `Đã giao`. Các nút hành động biến mất, hiển thị dòng chữ thông báo đơn hàng đã hoàn thành.

### Bước 3: Kiểm thử Khách hàng tự Hủy đơn hàng và Hoàn kho
1. Kiểm tra số lượng tồn kho của một cuốn sách (ví dụ: đang còn 15 cuốn).
2. Dùng tài khoản khách hàng đặt đơn hàng mua 3 cuốn của sách đó. Số lượng tồn kho giảm còn 12.
3. Vào trang **Đơn hàng của tôi** (`orders.php`).
4. Tại đơn hàng vừa đặt, thấy nút **Hủy đơn hàng** xuất hiện (vì trạng thái đang là `Chờ xác nhận`).
5. Bấm nút **Hủy đơn hàng** và bấm Đồng ý tại hộp thoại xác nhận.
6. *Xác nhận:*
   - Trạng thái đơn chuyển thành `Đã hủy`.
   - Kiểm tra lại tồn kho cuốn sách trên trang quản trị hoặc trang chi tiết sách: số lượng tồn kho đã tự phục hồi lại thành 15 cuốn như ban đầu.
7. Đặt một đơn hàng khác, chuyển trạng thái sang `Đã xác nhận` (ở bảng Admin). Khách hàng truy cập đơn hàng của tôi, bấm hủy đơn. Xác nhận đơn hàng vẫn hủy thành công và hoàn trả kho sách chính xác.
8. Admin chuyển trạng thái sang `Đang giao`:
   - *Xác nhận:* Khách hàng tải lại trang đơn hàng sẽ thấy nút **Hủy đơn hàng** đã biến mất hoàn toàn (Khách hàng không được phép tự hủy đơn khi đơn đang được giao hoặc đã giao).

### Bước 4: Bảo vệ Cơ sở dữ liệu chống việc Reset tự động
Để tránh việc trình duyệt tự động tải lại tệp `setup_db.php` khi mã nguồn thay đổi (nếu bạn đang mở tab này hoặc dùng công cụ Auto-Reload), chúng tôi đã bổ sung chốt bảo vệ bằng tham số xác nhận:
1. Nếu bạn vô tình truy cập `setup_db.php`, trang web sẽ hiển thị cảnh báo màu đỏ và yêu cầu bạn bấm nút xác nhận.
2. Cơ sở dữ liệu sẽ chỉ bị reset khi bạn truy cập đúng liên kết: `setup_db.php?confirm=yes`.

