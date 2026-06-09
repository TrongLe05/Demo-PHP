# Kế hoạch thực hiện Đề tài 2: Website bán sách online

Tài liệu này trình bày chi tiết kế hoạch xây dựng ứng dụng Website bán sách online bằng ngôn ngữ PHP (sử dụng Session và tệp tin JSON làm cơ sở dữ liệu giả lập), kết hợp với tài liệu thiết kế cơ sở dữ liệu (Database Design) chi tiết dưới dạng tệp tin Markdown.

## User Review Required

> [!IMPORTANT]
> - **Cơ sở dữ liệu giả lập (Mock DB):** Để đáp ứng yêu cầu "không cần làm DB, chỉ cần tạo file .md chứa các bảng, thiết kế, dữ liệu mẫu", toàn bộ hoạt động lưu trữ dữ liệu của website PHP (quản lý sách, tài khoản người dùng, đơn hàng) sẽ được giả lập thông qua các tệp tin JSON trên máy chủ (`books.json`, `users.json`, `orders.json`). Cách tiếp cận này giúp website chạy được đầy đủ tính năng (Đăng ký, Đăng nhập, Thêm/Sửa/Xóa sách của admin, Lưu đơn hàng khi thanh toán) mà không cần cài đặt hoặc kết nối cơ sở dữ liệu MySQL thật.
> - **Giao diện người dùng:** Sẽ sử dụng CSS thuần (Vanilla CSS) kết hợp Bootstrap 5 (được khuyến khích trong đề bài) để thiết kế giao diện Glassmorphism hiện đại, chuyên nghiệp và responsive tốt trên mọi thiết bị.

## Open Questions

*Không có.*

## Proposed Changes

Chúng ta sẽ tạo và chỉnh sửa các tệp tin sau trong thư mục dự án `D:\CODE\CODE_VS\Demo PHP`:

---

### 1. Tài liệu thiết kế Database

#### [NEW] [database_design.md](file:///D:/CODE/CODE_VS/Demo%20PHP/database_design.md)
Tệp tin Markdown chứa thiết kế chi tiết CSDL cho Đề tài 2 (Website bán sách online), bao gồm:
- Sơ đồ cấu trúc các bảng (`books`, `users`, `orders`, `order_items`).
- Kiểu dữ liệu, mô tả các cột, khóa chính, khóa ngoại.
- Dữ liệu mẫu (SQL Insert và dạng bảng trực quan).

---

### 2. Tệp tin dữ liệu giả lập (JSON Mock Database)

#### [NEW] [books.json](file:///D:/CODE/CODE_VS/Demo%20PHP/books.json)
Chứa danh sách sách ban đầu bao gồm các trường: `id`, `title`, `author`, `category`, `price`, `image`, `description`, `featured` (sách nổi bật).

#### [NEW] [users.json](file:///D:/CODE/CODE_VS/Demo%20PHP/users.json)
Chứa danh sách người dùng. Mặc định sẽ tạo sẵn một tài khoản Admin (`admin@bookstore.com` / mật khẩu: `admin123`) và một tài khoản Khách hàng mẫu.

#### [NEW] [orders.json](file:///D:/CODE/CODE_VS/Demo%20PHP/orders.json)
Tệp tin trống (mảng rỗng `[]`) dùng để lưu các đơn hàng được đặt từ phía khách hàng.

---

### 3. Giao diện & Mã nguồn PHP

#### [MODIFY] [index.php](file:///D:/CODE/CODE_VS/Demo%20PHP/index.php)
Trang chủ hiển thị:
- Banner chào mừng/Giới thiệu.
- Danh mục thể loại (Categories) để lọc sách.
- Danh sách sách nổi bật (Featured Books).
- Toàn bộ danh sách sách (hoặc kết quả tìm kiếm/lọc).
- Nút "Thêm vào giỏ hàng" và liên kết xem chi tiết sách.
- Thanh điều hướng (Navbar) hiển thị trạng thái đăng nhập (User/Admin) và số lượng giỏ hàng.

#### [NEW] [book-detail.php](file:///D:/CODE/CODE_VS/Demo%20PHP/book-detail.php)
Hiển thị thông tin chi tiết một cuốn sách (Tên sách, Tác giả, Giá, Ảnh bìa, Mô tả chi tiết, Thể loại) và nút thêm vào giỏ hàng.

#### [NEW] [cart.php](file:///D:/CODE/CODE_VS/Demo%20PHP/cart.php)
Quản lý giỏ hàng:
- Hiển thị danh sách sách trong giỏ.
- Cho phép cập nhật số lượng, xóa sách khỏi giỏ hàng.
- Tính và hiển thị tổng tiền tự động.
- Nút tiến hành thanh toán (Checkout).

#### [NEW] [checkout.php](file:///D:/CODE/CODE_VS/Demo%20PHP/checkout.php)
Trang thanh toán:
- Nhập thông tin người nhận (Họ tên, Số điện thoại, Địa chỉ giao hàng).
- Xác nhận đơn hàng và lưu đơn hàng vào `orders.json` (tương đương lưu vào DB).
- Xóa giỏ hàng sau khi đặt hàng thành công.

#### [NEW] [login.php](file:///D:/CODE/CODE_VS/Demo%20PHP/login.php) & [register.php](file:///D:/CODE/CODE_VS/Demo%20PHP/register.php)
Trang đăng ký và đăng nhập tài khoản. Lưu thông tin người dùng mới vào `users.json`. Quản lý trạng thái bằng PHP Session.

#### [NEW] [admin.php](file:///D:/CODE/CODE_VS/Demo%20PHP/admin.php)
Trang quản trị dành cho tài khoản Admin (kiểm tra quyền bằng session):
- Hiển thị danh sách toàn bộ sách hiện có.
- Thêm sách mới (nhập tên, tác giả, giá, thể loại, ảnh bìa trực tuyến hoặc đường dẫn, mô tả).
- Sửa thông tin sách hiện tại.
- Xóa sách khỏi danh sách.
- Hiển thị danh sách đơn hàng đã được đặt bởi khách hàng.

#### [NEW] [logout.php](file:///D:/CODE/CODE_VS/Demo%20PHP/logout.php)
Xử lý đăng xuất tài khoản.

#### [NEW] [header.php](file:///D:/CODE/CODE_VS/Demo%20PHP/header.php) & [footer.php](file:///D:/CODE/CODE_VS/Demo%20PHP/footer.php)
Các file layout dùng chung để tránh trùng lặp mã nguồn HTML/CSS.

#### [MODIFY] [style.css](file:///D:/CODE/CODE_VS/Demo%20PHP/style.css)
Thay đổi toàn bộ giao diện thành giao diện Glassmorphism sang trọng, tông màu xanh mực/vàng nhạt trang nhã phù hợp với cửa hàng sách trực tuyến.

---

## Verification Plan

### Automated Tests
Không áp dụng (Dự án PHP thuần không tích hợp PHPUnit).

### Manual Verification
1. Truy cập trang chủ `index.php` bằng trình duyệt web thông qua máy chủ Apache (hoặc chạy lệnh `php -S localhost:8000`).
2. Kiểm tra bộ lọc theo thể loại (Category filter) và tìm kiếm sách.
3. Thực hiện Đăng ký tài khoản mới và Đăng nhập.
4. Thêm một vài cuốn sách vào giỏ hàng, cập nhật số lượng và kiểm tra tổng tiền.
5. Tiến hành thanh toán, nhập thông tin và kiểm tra xem đơn hàng đã được lưu vào tệp `orders.json` chưa.
6. Đăng nhập tài khoản Admin (`admin@bookstore.com` / `admin123`).
7. Truy cập trang quản trị `admin.php`, thực hiện:
   - Thêm một cuốn sách mới (và xác minh nó xuất hiện ngoài trang chủ).
   - Sửa thông tin sách.
   - Xóa sách.
   - Xem danh sách đơn hàng khách hàng vừa đặt.
8. Đăng xuất và kiểm tra phân quyền (không cho tài khoản thường truy cập `admin.php`).
