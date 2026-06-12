# Tổng Hợp Nghiệp Vụ & Kế Hoạch Thực Hiện Chi Tiết (3FC Bookstore)

Tài liệu này ghi chú toàn bộ các nghiệp vụ kinh doanh của hệ thống **3FC Bookstore**, trạng thái phát triển thực tế của từng nghiệp vụ (Cái nào đã hoàn thành, cái nào đã sửa đổi) và các bước thực hiện chi tiết.

---

## 1. Bản Đồ Nghiệp Vụ Hệ Thống (Business Operations Map)

Hệ thống được chia thành hai phân hệ lớn là **Khách hàng** (Customer) và **Quản trị viên** (Admin).

### 1.1. Nghiệp vụ Phân hệ Khách hàng
1. **Đăng ký & Đăng nhập (Auth)**:
   - *Mô tả*: Khách hàng đăng ký tài khoản mới, mật khẩu được băm bảo mật (Bcrypt/MD5). Đăng nhập để kích hoạt session riêng biệt cho từng tab trình duyệt.
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**. Đã bảo vệ toàn bộ các trang mua hàng, giỏ hàng, thanh toán và đơn hàng.
2. **Xem & Tìm kiếm sách**:
   - *Mô tả*: Xem danh sách sách mới nhất, sách nổi bật. Tìm kiếm sách theo tên, tác giả hoặc lọc theo Thể loại.
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**.
3. **Quản lý Giỏ hàng (Cart)**:
   - *Mô tả*: Thêm sách vào giỏ, thay đổi số lượng, xóa sản phẩm.
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**. Đã tối ưu hóa AJAX giúp tăng/giảm/xóa không bị reload trang. Đã sửa lỗi hiển thị co ảnh bìa khi giỏ hàng chỉ có 1 sản phẩm.
4. **Mua Ngay (Buy Now)**:
   - *Mô tả*: Cho phép thanh toán trực tiếp 1 cuốn sách duy nhất từ trang chi tiết mà không ảnh hưởng hay gom các sản phẩm đang có sẵn trong giỏ hàng.
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**. Đã tách riêng biệt luồng thanh toán giỏ hàng và mua ngay.
5. **Thanh toán Đơn hàng (Checkout)**:
   - *Mô tả*: Nhập thông tin giao hàng và chọn phương thức thanh toán:
     - **COD**: Thanh toán tiền mặt khi nhận hàng.
     - **Cổng PayOS**: Nhúng mã QR động từ PayOS và tự động kiểm tra trạng thái thanh toán (polling thời gian thực).
     - **Chuyển khoản VietQR**: Quét mã QR ngân hàng cá nhân của Shop.
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**. Đã cập nhật nội dung chuyển khoản của cả 2 phương thức QR thành: `Thanh toan don hang #madonhang`.
6. **Lịch sử Đơn hàng (Order History)**:
   - *Mô tả*: Xem danh sách đơn hàng đã mua và trạng thái xử lý (Chờ xác nhận, Đã xác nhận, Đang giao, Đã giao, Đã hủy).
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**.
7. **Trang thông tin phụ trợ (Giới thiệu, Khuyến mãi, Liên hệ)**:
   - *Mô tả*: 
     - Giới thiệu thông tin nhà sách.
     - Khuyến mãi hiển thị voucher và có nút copy mã coupon nhanh.
     - Liên hệ hiển thị thông tin và có form gửi ý kiến đóng góp qua AJAX.
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**.

### 1.2. Nghiệp vụ Phân hệ Quản trị viên (Admin)
1. **Bảng điều khiển (Dashboard)**:
   - *Mô tả*: Thống kê doanh số, tổng số sách, đơn hàng.
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**.
2. **Quản lý Sách (Book Management)**:
   - *Mô tả*: Thêm sách mới, cập nhật sách cũ, xóa sách.
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**. Đã sửa lỗi bắt buộc nhập lại URL ảnh khi sửa thông tin sách (chuyển input sang `type="text"`). Đã sửa lỗi trang bị reload khi xóa sách (chuyển sang AJAX xóa).
3. **Quản lý Đơn hàng (Order Management)**:
   - *Mô tả*: Cập nhật trạng thái đơn hàng (Duyệt đơn, Đang giao, Đã giao, Hủy đơn).
   - *Trạng thái*: **ĐÃ HOÀN THÀNH**.

---

## 2. Nhật Ký Từng Bước Thực Hiện Dự Án (Step-by-Step Implementation)

Dưới đây là tiến trình xây dựng và hoàn thiện dự án theo thứ tự các bước logic:

### Bước 1: Xây dựng Cơ sở dữ liệu & Cấu hình môi trường
- [x] Tạo các bảng dữ liệu: `users`, `books`, `orders`, `order_details`, `cart`.
- [x] Thiết lập khóa ngoại và chỉ mục tối ưu.
- [x] Tạo tệp `.env` lưu trữ thông tin cấu hình kết nối DB và API PayOS.
- [x] Viết hàm `load_env_file()` trong `db_helper.php` để nạp các tham số môi trường tự động.
- **Trạng thái**: *Đã hoàn thành.*

### Bước 2: Tái cấu trúc mã nguồn theo chuẩn SOLID
- [x] Viết lại các hàm thủ tục trong `db_helper.php` thành các lớp hướng đối tượng:
  - `DatabaseConnection` quản lý kết nối CSDL (Single Responsibility - SRP).
  - `BookRepository` quản lý sách.
  - `UserRepository` quản lý người dùng.
  - `OrderRepository` quản lý đơn hàng.
  - `CartManager` quản lý giỏ hàng trong DB/Session.
- [x] Áp dụng Dependency Inversion (DIP) thông qua Interface kết nối.
- [x] Áp dụng Facade Pattern để tạo các hàm bao ở cuối tệp tương thích ngược cho hệ thống cũ.
- **Trạng thái**: *Đã hoàn thành.*

### Bước 3: Bảo vệ Quyền truy cập & Luồng Mua Sách
- [x] Ràng buộc đăng nhập bắt buộc trước khi thêm vào giỏ hàng hoặc mua ngay.
- [x] Chặn truy cập trực tiếp vào `cart.php` và `checkout.php` nếu chưa có Session đăng nhập hợp lệ.
- [x] Khắc phục lỗi dùng chung Session trên nhiều tab ẩn danh bằng cách phân rã session ID.
- **Trạng thái**: *Đã hoàn thành.*

### Bước 4: Tách riêng luồng "Mua Ngay" và "Giỏ Hàng"
- [x] Khi người dùng nhấn "Mua ngay", sản phẩm được gửi đến trang thanh toán qua tham số GET/POST riêng biệt (`?buy_now=id`).
- [x] Trang thanh toán xử lý tính toán số tiền và lưu đơn hàng chỉ chứa duy nhất cuốn sách "Mua ngay" đó mà không đụng chạm đến giỏ hàng hiện tại của khách hàng.
- **Trạng thái**: *Đã hoàn thành.*

### Bước 5: Tối ưu tương tác AJAX (Tránh Reload Trang)
- [x] **Trang giỏ hàng**: Chuyển đổi các hành động tăng/giảm số lượng và xóa sách sang AJAX Fetch API. Giỏ hàng cập nhật số tiền tức thời không cần reload.
- [x] **Trang quản trị sách**: Chuyển đổi hành động xóa sách sang API AJAX xóa sách, tự động ẩn dòng sách khỏi bảng mà không reload trang.
- [x] **Giao diện giỏ hàng**: Thiết lập kích thước cột ảnh cố định là `80px` và ảnh `min-width: 65px` để ảnh không bị co nhỏ khi giỏ hàng có duy nhất 1 sản phẩm.
- **Trạng thái**: *Đã hoàn thành.*

### Bước 6: Thay thế cảnh báo alert bằng thông báo Toast cao cấp
- [x] Phát triển hệ thống thông báo Toast thủy tinh mờ (`showToast(message, type)`).
- [x] Chuyển đổi toàn bộ các cảnh báo `alert()` mặc định của trình duyệt và các thông báo PHP tĩnh sang Toast ở các trang: Trang chủ, Chi tiết sách, Giỏ hàng, Đăng ký/Đăng nhập, Thanh toán.
- **Trạng thái**: *Đã hoàn thành.*

### Bước 7: Nâng cấp Footer & Giao diện Header
- [x] **Footer**: Chuyển toàn bộ style inline sang CSS. Thiết kế hiệu ứng Glassmorphism mờ và viền trên phát sáng glow. Chuyển đổi form newsletter dùng `showToast`. Thay thế các badge Bootstrap mặc định bằng `.payment-badge-custom`.
- [x] **Header**: Thêm 3 tab menu động: *Giới thiệu*, *Khuyến mãi*, *Liên hệ*. Thiết kế nút Đăng nhập dạng pill bo tròn và hiệu ứng hover đồng bộ với nút Giỏ hàng.
- [x] **Tạo trang**:
  - `about.php`: Câu chuyện 3FC, sứ mệnh và đội ngũ phát triển.
  - `promotions.php`: Thẻ voucher và tính năng Copy mã tự động thông báo Toast.
  - `contact.php`: Địa chỉ, Hotline, giờ làm việc và form gửi phản hồi AJAX mượt mà.
- **Trạng thái**: *Đã hoàn thành.*

### Bước 8: Tối ưu nội dung QR Code chuyển khoản
- [x] Điều chỉnh tham số `addInfo` và mô tả trong mã quét QR (VietQR và PayOS) thành chuỗi thống nhất: `Thanh toan don hang #madonhang` (sử dụng mã đơn hàng tiếp theo dự kiến từ cơ sở dữ liệu `MAX(id) + 1`).
- [x] Đối với PayOS: Lọc ký tự đặc biệt `#` gửi lên API PayOS để tuân thủ quy chuẩn, đồng thời hiển thị đầy đủ ký tự `#` ngoài giao diện cho khách hàng đối chiếu.
- **Trạng thái**: *Đã hoàn thành.*

### Bước 9: Sửa lỗi cập nhật Sách trong trang Quản trị
- [x] Chuyển loại thẻ nhập đường dẫn ảnh trực tuyến từ `type="url"` sang `type="text"`.
- [x] Giải quyết triệt để lỗi trình duyệt chặn gửi biểu mẫu do đường dẫn tương đối nội bộ của ảnh (`uploads/books/...`) bị coi là URL không hợp lệ. Admin có thể chỉnh sửa thông tin sách mà không cần phải nhập lại ảnh bìa nếu không có nhu cầu thay đổi.
- **Trạng thái**: *Đã hoàn thành.*
