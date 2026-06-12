TRƯỜNG ĐẠI HỌC ĐỒNG THÁP

MÔN HỌC: THIẾT KẾ WEBSITE VỚI PHP

GIẢNG VIÊN: THS. NGUYỄN TRUNG TRÍ

-------------------------------------------------------------------------------

Đề tài 1 — Website bán điện thoại

Mô tả

Xây dựng website bán điện thoại bằng PHP và MySQL.

Chức năng cơ bản

* Trang chủ hiển thị danh sách điện thoại
* Trang chi tiết sản phẩm
* Thêm vào giỏ hàng
* Xem giỏ hàng
* Tính tổng tiền
Thông tin sản phẩm

* Tên điện thoại
* Hãng sản xuất
* Giá bán
* Hình ảnh
* Mô tả
Yêu cầu nâng cao

* Tìm kiếm sản phẩm
* Lọc theo hãng
* Cập nhật số lượng trong giỏ hàng
* Xóa sản phẩm khỏi giỏ hàng
* Thiết kế responsive bằng Bootstrap
CSDL gợi ý

Bảng products

* id
* name
* brand
* price
* image
* description

Đề tài 2 — Website bán sách online

Mô tả

Thiết kế website bán sách trực tuyến.

Chức năng cơ bản

* Hiển thị danh sách sách
* Trang chi tiết sách
* Thêm vào giỏ hàng
* Hiển thị tổng tiền
Thông tin sách

* Tên sách
* Tác giả
* Giá
* Ảnh bìa
* Mô tả
Yêu cầu nâng cao

* Đăng ký / đăng nhập
* Phân loại sách theo thể loại
* Hiển thị sách nổi bật
* Lưu đơn hàng vào database
* Trang quản trị thêm/sửa/xóa sách
CSDL gợi ý

Bảng books

* id
* title
* author
* category
* price
* image
* description
Bảng users

* id
* fullname
* email
* password
Bảng orders

* id
* user_id
* total_price
* created_at

Đề tài 3 — Website đặt đồ ăn online

Mô tả

Xây dựng website đặt món ăn trực tuyến.

Chức năng cơ bản

* Hiển thị danh sách món ăn
* Xem chi tiết món ăn
* Thêm món vào giỏ hàng
* Tính tổng hóa đơn
Thông tin món ăn

* Tên món
* Giá
* Hình ảnh
* Mô tả
* Loại món ăn
Yêu cầu nâng cao

* Tìm kiếm món ăn
* Lọc theo loại món
* Chức năng đặt hàng
* Lưu lịch sử đơn hàng
* Trang admin quản lý món ăn
CSDL gợi ý

Bảng foods

* id
* name
* category
* price
* image
* description
Bảng orders

* id
* customer_name
* address
* phone
* total

Đề tài 4 — Website bán laptop nâng cao

Mô tả

Thiết kế website thương mại điện tử bán laptop.

Chức năng cơ bản

* Danh sách laptop
* Chi tiết laptop
* Giỏ hàng
* Tổng tiền
Chức năng nâng cao

* Đăng nhập / đăng ký
* Phân quyền Admin/User
* Quản lý sản phẩm CRUD
* Upload hình ảnh
* Thanh toán giả lập
* Tìm kiếm và lọc sản phẩm
* Thống kê doanh thu
* Responsive giao diện
* Bảo mật SQL Injection
Thông tin laptop

* Tên laptop
* CPU
* RAM
* SSD
* Giá
* Ảnh
* Mô tả
CSDL gợi ý

Bảng products

* id
* name
* cpu
* ram
* ssd
* price
* image
* description
Bảng users

* id
* username
* password
* role
Bảng orders

* id
* user_id
* total_price
* created_at
Bảng order_details

* id
* order_id
* product_id
* quantity
* subtotal

Đề tài 5 — Website bán vé xem phim trực tuyến

Mô tả

Xây dựng hệ thống đặt vé xem phim trực tuyến, cho phép người dùng xem lịch chiếu và chọn suất chiếu phù hợp.

Chức năng cơ bản

* Hiển thị danh sách phim đang chiếu
* Trang chi tiết phim và thông tin lịch chiếu
* Chọn suất chiếu và số lượng vé cần mua
* Tính tổng tiền thanh toán
Thông tin phim

* Tên phim
* Thể loại
* Thời lượng (phút)
* Đạo diễn
* Hình ảnh poster
* Mô tả nội dung
Yêu cầu nâng cao

* Đăng nhập / Đăng ký tài khoản cho khách hàng
* Chọn vị trí ghế ngồi (mô phỏng sơ đồ ghế đơn giản)
* Quản lý lịch sử đặt vé của người dùng
* Trang Admin: Quản lý phim (CRUD) và thêm suất chiếu
* Thiết kế giao diện Dark Mode (giao diện rạp phim) bằng Bootstrap
CSDL gợi ý

* Bảng movies: id, title, genre, duration, director, poster, description
* Bảng showtimes: id, movie_id, show_date, show_time, ticket_price
* Bảng tickets: id, user_id, showtime_id, seat_number, total_price, booking_date

Đề tài 6 — Website cho thuê xe máy/ô tô tự lái

Mô tả

Thiết kế ứng dụng web giới thiệu và đặt dịch vụ thuê xe tự lái theo ngày.

Chức năng cơ bản

* Hiển thị danh sách các dòng xe sẵn sàng cho thuê
* Trang xem chi tiết thông số xe và giá thuê theo ngày
* Chọn ngày thuê (Từ ngày - Đến ngày) để ước tính chi phí
* Form gửi yêu cầu đặt xe đơn giản
Thông tin xe

* Tên xe
* Hãng xe
* Loại xe (Số sàn / Số tự động)
* Giá thuê (VND/ngày)
* Hình ảnh xe
* Trạng thái (Còn trống / Đã được thuê)
Yêu cầu nâng cao

* Bộ lọc tìm kiếm: Lọc xe theo hãng, theo giá hoặc theo loại xe
* Tính toán chính xác tổng tiền dựa trên số ngày thuê giữa 2 mốc thời gian
* Quản lý phân quyền: Admin và Khách hàng
* Trang Admin: Duyệt yêu cầu thuê xe, cập nhật trạng thái xe (CRUD)
* Responsive giao diện hiển thị tốt trên thiết bị di động
CSDL gợi ý

* Bảng vehicles: id, name, brand, type, price_per_day, image, status
* Bảng rentals: id, customer_name, customer_phone, vehicle_id, start_date, end_date, total_cost, rental_status

Đề tài 7 — Website bán khóa học trực tuyến (E-Learning)

Mô tả

Xây dựng website giới thiệu, bán và quản lý học viên tham gia các khóa học video trực tuyến.

Chức năng cơ bản

* Hiển thị danh sách các khóa học theo danh mục
* Trang chi tiết khóa học (Lộ trình, thông tin giảng viên)
* Thêm khóa học vào danh sách đăng ký
* Tính tổng tiền các khóa học đã chọn
Thông tin khóa học

* Tên khóa học
* Giảng viên
* Giá bán
* Hình ảnh đại diện (Thumbnail)
* Thời lượng tổng thể
* Mô tả ngắn
Yêu cầu nâng cao

* Hệ thống Đăng nhập / Đăng ký cho Học viên và Giảng viên
* Trang Admin/Giảng viên: Upload bài học (CRUD khóa học và các link video bài học)
* Mô phỏng chức năng "Học thử" (cho phép xem trước 1 video miễn phí)
* Trang cá nhân của học viên: Hiển thị các khóa học đã mua thành công
* Bảo mật cơ bản: Kiểm tra quyền truy cập (chỉ học viên đã mua mới xem được video bài học)
CSDL gợi ý

* Bảng courses: id, title, instructor, price, thumbnail, description, category
* Bảng lessons: id, course_id, lesson_title, video_url, is_free
* Bảng enrollments: id, user_id, course_id, payment_status, enrolled_at

Đề tài 8 — Website bán phụ kiện thời trang & Giày dép

Mô tả

Thiết kế website thương mại điện tử chuyên sâu về mảng thời trang, có xử lý thuộc tính sản phẩm phức tạp.

Chức năng cơ bản

* Hiển thị danh sách sản phẩm thời trang theo bộ sưu tập
* Trang chi tiết sản phẩm
* Giỏ hàng tính tổng tiền tự động
Thông tin sản phẩm

* Tên sản phẩm
* Danh mục (Giày, Nón, Kính mát, Ví...)
* Giá bán
* Hình ảnh chính
* Mô tả chất liệu, phong cách
Yêu cầu nâng cao

* Xử lý biến thể sản phẩm: Cho phép chọn Size (39, 40, 41...) và Màu sắc trước khi thêm vào giỏ hàng
* Trang quản trị (Admin Panel) phân quyền quản lý hoàn chỉnh
* Quản lý đơn hàng chi tiết: Xem danh sách đơn hàng, cập nhật trạng thái đơn (Chờ duyệt / Đang giao / Đã giao)
* Thống kê doanh thu cơ bản theo tháng bằng biểu đồ hoặc bảng số liệu
* Tối ưu hóa giao diện hiển thị lưới (Grid system) đẹp mắt bằng Bootstrap 5
CSDL gợi ý

* Bảng products: id, name, category, price, image, description
* Bảng product_variants: id, product_id, size, color, stock_quantity
* Bảng orders: id, customer_name, email, phone, address, total_amount, status, created_at
* Bảng order_items: id, order_id, variant_id, quantity, price

Yêu cầu chung cho tất cả nhóm

Công nghệ bắt buộc

* PHP
* MySQL
* HTML/CSS
* Session
* Bootstrap (khuyến khích)
Nội dung báo cáo cần có

* Giới thiệu đề tài
* Phân tích chức năng
* Thiết kế CSDL
* Thiết kế giao diện
* Source code chính
* Kết quả chạy chương trình
* Hướng phát triển
Yêu cầu trình bày

* Demo chạy thực tế
* Báo cáo Word/PDF
* Slide thuyết trình
* Có phân chia công việc nhóm
Thang điểm gợi ý

| Nội dung | Điểm |
| --- | --- |
| Giao diện | 2 |
| Chức năng cơ bản | 3 |
| Chức năng nâng cao | 2 |
| CSDL | 1 |
| Báo cáo + Slide | 1 |
| Demo + Thuyết trình | 1 |
