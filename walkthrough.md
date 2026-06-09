# Báo cáo hoàn thành & Hướng dẫn sử dụng Đề tài 2: Website bán sách online

Dự án đã được triển khai hoàn chỉnh với đầy đủ các tính năng cơ bản, nâng cao và cấu trúc giao diện hiện đại Glassmorphism.

---

## 1. Cấu trúc thư mục nguồn

Dưới đây là sơ đồ tệp tin đã tạo và chỉnh sửa:

*   **Tài liệu thiết kế:**
    *   [database_design.md](file:///D:/CODE/CODE_VS/Demo%20PHP/database_design.md) - Chi tiết thiết kế cơ sở dữ liệu (ERD, định nghĩa các bảng, kiểu dữ liệu, các truy vấn tạo bảng và dữ liệu mẫu).
    *   [implementation_plan.md](file:///D:/CODE/CODE_VS/Demo%20PHP/implementation_plan.md) - Kế hoạch thực hiện chi tiết đã duyệt.
*   **Cơ sở dữ liệu giả lập (JSON):**
    *   [books.json](file:///D:/CODE/CODE_VS/Demo%20PHP/books.json) - Chứa danh sách sách mẫu.
    *   [users.json](file:///D:/CODE/CODE_VS/Demo%20PHP/users.json) - Lưu trữ danh sách tài khoản người dùng và quản trị viên.
    *   [orders.json](file:///D:/CODE/CODE_VS/Demo%20PHP/orders.json) - Lưu trữ các đơn hàng đặt từ phía khách hàng.
*   **Mã nguồn PHP & Giao diện:**
    *   [db_helper.php](file:///D:/CODE/CODE_VS/Demo%20PHP/db_helper.php) - Thư viện Helper thao tác CRUD dữ liệu JSON. Cấu hình kết nối MySQL đã được khai báo sẵn nhưng ẩn đi (commented out) để chạy Mock Data mặc định.
    *   [style.css](file:///D:/CODE/CODE_VS/Demo%20PHP/style.css) - Hệ thống CSS Glassmorphism cao cấp, responsive.
    *   [header.php](file:///D:/CODE/CODE_VS/Demo%20PHP/header.php) & [footer.php](file:///D:/CODE/CODE_VS/Demo%20PHP/footer.php) - Layout dùng chung cho dự án.
    *   [index.php](file:///D:/CODE/CODE_VS/Demo%20PHP/index.php) - Trang chủ: Hiển thị sách nổi bật, lọc theo thể loại, tìm kiếm, thêm nhanh vào giỏ hàng.
    *   [book-detail.php](file:///D:/CODE/CODE_VS/Demo%20PHP/book-detail.php) - Chi tiết thông tin sách và chọn số lượng trước khi thêm vào giỏ.
    *   [cart.php](file:///D:/CODE/CODE_VS/Demo%20PHP/cart.php) - Quản lý giỏ hàng: Cập nhật số lượng, xóa sản phẩm, tính tiền.
    *   [checkout.php](file:///D:/CODE/CODE_VS/Demo%20PHP/checkout.php) - Trang điền thông tin và thanh toán đơn đặt hàng.
    *   [login.php](file:///D:/CODE/CODE_VS/Demo%20PHP/login.php) & [register.php](file:///D:/CODE/CODE_VS/Demo%20PHP/register.php) - Hệ thống xác thực người dùng.
    *   [logout.php](file:///D:/CODE/CODE_VS/Demo%20PHP/logout.php) - Đăng xuất tài khoản.
    *   [admin.php](file:///D:/CODE/CODE_VS/Demo%20PHP/admin.php) - Bảng điều khiển quản trị: CRUD Sách, xem lịch sử đặt hàng của toàn bộ khách hàng.

---

## 2. Thông tin tài khoản đăng nhập mẫu

| Loại tài khoản | Email đăng nhập | Mật khẩu | Quyền hạn |
| :--- | :--- | :--- | :--- |
| **Quản trị viên** | `admin@bookstore.com` | `admin123` | Admin (Được vào `admin.php`) |
| **Khách hàng** | `customer@example.com` | `user123` | User thường |

---

## 3. Hướng dẫn chạy và kiểm thử ứng dụng

Bạn có thể chạy thử trang web trên môi trường local cực kỳ dễ dàng thông qua các bước sau:

1.  **Chạy Server:**
    Mở Terminal/CMD tại thư mục `D:\CODE\CODE_VS\Demo PHP` và khởi chạy máy chủ PHP tích hợp sẵn bằng lệnh sau:
    ```bash
    php -S localhost:8000
    ```
2.  **Mở trình duyệt:**
    Truy cập vào địa chỉ [http://localhost:8000](http://localhost:8000) trên trình duyệt để trải nghiệm website.
3.  **Kịch bản kiểm thử đề xuất:**
    *   **Xem sách và Giỏ hàng:** Ngoài trang chủ, bấm lọc theo các thể loại (Kỹ năng sống, Công nghệ, Tiểu thuyết,...) hoặc nhập từ khóa tìm kiếm. Nhấn biểu tượng dấu cộng `+` hoặc xem chi tiết sách rồi thêm vào giỏ.
    *   **Thanh toán:** Vào giỏ hàng, cập nhật số lượng, nhấn "Tiến hành thanh toán" để điền thông tin người nhận và đặt hàng thành công.
    *   **Xác thực:** Đăng ký một tài khoản mới tại trang Đăng ký và đăng nhập thử.
    *   **Quản trị viên:** Đăng nhập bằng tài khoản `admin@bookstore.com` / `admin123` để vào trang Quản trị (`admin.php`). Thực hiện thêm sách mới, cập nhật hoặc xóa sách, xem danh sách đơn hàng đã đặt.
