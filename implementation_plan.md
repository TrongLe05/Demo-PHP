# Kế hoạch thực hiện: Bắt buộc Đăng nhập để mua sách & Tái cấu trúc theo nguyên tắc SOLID

Kế hoạch này chi tiết hóa cách chặn quyền mua sách đối với người dùng chưa đăng nhập và cách tái cấu trúc toàn bộ tệp `db_helper.php` thành các lớp hướng đối tượng sạch đạt chuẩn SOLID.

## User Review Required

> [!IMPORTANT]
> - **Bắt buộc Đăng nhập trước khi mua sách:** Người dùng chưa đăng nhập vẫn có thể duyệt xem sách bình thường. Tuy nhiên, nếu họ click "Thêm vào giỏ hàng", "Mua ngay", hoặc cố gắng truy cập trực tiếp vào `cart.php`, `checkout.php`, hệ thống sẽ tự động chuyển hướng họ đến `login.php` kèm theo cảnh báo yêu cầu đăng nhập trước.
> - **Tái cấu trúc SOLID và Facade Pattern:** Chúng tôi sẽ viết lại toàn bộ `db_helper.php` theo mô hình Hướng đối tượng (OOP) sạch:
>   - `DatabaseConnection` quản lý kết nối CSDL (Đạt chuẩn SRP).
>   - `BookRepository` quản lý sách (SRP).
>   - `UserRepository` quản lý người dùng (SRP).
>   - `OrderRepository` quản lý đơn hàng (SRP).
>   - `CartManager` quản lý giỏ hàng (SRP).
>   - Sử dụng giao diện kết nối để đảm bảo tính nghịch đảo phụ thuộc (DIP).
>   - Áp dụng mẫu thiết kế **Facade Pattern** để tạo các hàm bao (wrappers) tương thích ngược ở cuối tệp. Nhờ đó, các tệp PHP hiện tại như `index.php`, `admin.php` vẫn hoạt động tốt mà không bị lỗi gọi hàm.

## Proposed Changes

---

### 1. Tái cấu trúc Database & Helper theo SOLID

#### [MODIFY] [db_helper.php](file:///D:/CODE/CODE_VS/Demo%20PHP/db_helper.php)
- Chuyển đổi toàn bộ cấu trúc thủ tục (procedural) thành các Class hướng đối tượng cụ thể:
  - Tách biệt kết nối CSDL, Quản lý sách, Người dùng, Đơn hàng, Giỏ hàng thành các Repository riêng lẻ.
  - Sử dụng Dependency Inversion Principle thông qua `DatabaseConnectionInterface`.
  - Cập nhật hàm `saveOrder` trong `OrderRepository` để kiểm tra chặt chẽ sự tồn tại của `$_SESSION['user_id']`, không cho phép gán ID mặc định nữa.
  - Giữ lại các hàm bao ở cuối tệp để giữ nguyên giao tiếp bên ngoài (Facade).

---

### 2. Áp dụng Khóa Đăng nhập trước khi mua hàng

#### [MODIFY] [index.php](file:///D:/CODE/CODE_VS/Demo%20PHP/index.php)
- Ở phần xử lý `add_to_cart` và `buy_now` (đầu trang), bổ sung kiểm tra: Nếu `$_SESSION['user_id']` chưa được thiết lập, chuyển hướng đến `login.php?status=login_required`.

#### [MODIFY] [book-detail.php](file:///D:/CODE/CODE_VS/Demo%20PHP/book-detail.php)
- Ở phần xử lý submit form `add_to_cart_btn` hoặc `buy_now_btn`, kiểm tra đăng nhập. Nếu chưa đăng nhập, chuyển hướng đến `login.php?status=login_required`.

#### [MODIFY] [cart.php](file:///D:/CODE/CODE_VS/Demo%20PHP/cart.php)
- Thêm kiểm tra ở đầu trang: Nếu chưa có `user_id` trong session, chuyển hướng đến `login.php?status=login_required`.

#### [MODIFY] [checkout.php](file:///D:/CODE/CODE_VS/Demo%20PHP/checkout.php)
- Thêm kiểm tra ở đầu trang: Nếu chưa có `user_id` trong session, chuyển hướng đến `login.php?status=login_required`.

#### [MODIFY] [login.php](file:///D:/CODE/CODE_VS/Demo%20PHP/login.php)
- Kiểm tra tham số `$_GET['status'] == 'login_required'` và hiển thị thông báo màu đỏ: *"Bạn cần đăng nhập tài khoản trước khi thực hiện mua sách hoặc thêm vào giỏ hàng."*

---

## Verification Plan

### Manual Verification
1. **Kiểm tra Mua sách khi chưa đăng nhập (Chặn quyền):**
   - Đăng xuất tài khoản (nếu đang đăng nhập).
   - Truy cập trang chủ, nhấn nút **Mua ngay** hoặc biểu tượng **Thêm vào giỏ** ở một cuốn sách.
   - *Xác nhận:* Bị chuyển hướng ngay về trang `login.php` và hiển thị cảnh báo yêu cầu đăng nhập.
   - Truy cập trang chi tiết sách (`book-detail.php`), nhập số lượng và nhấn **Thêm vào giỏ** hoặc **Mua ngay**.
   - *Xác nhận:* Bị chuyển hướng về trang `login.php` kèm cảnh báo.
   - Cố gắng gõ trực tiếp URL `http://localhost:8000/cart.php` or `checkout.php`.
   - *Xác nhận:* Bị chặn và chuyển hướng về trang `login.php`.
2. **Kiểm tra hoạt động sau khi Đăng nhập:**
   - Đăng nhập tài khoản khách hàng thông thường.
   - Thực hiện mua sách, xem giỏ hàng, thanh toán và hủy đơn.
   - *Xác nhận:* Mọi hoạt động diễn ra trơn tru, không có bất kỳ lỗi cú pháp hoặc hàm nào do việc tái cấu trúc SOLID ở `db_helper.php`.
