# Kế hoạch thực hiện: Tinh chỉnh Giao diện Sáng, Nút bấm Trạng thái Admin & Khách hàng tự Hủy đơn hàng

Kế hoạch này chi tiết hóa cách sửa đổi giao diện sang tông màu sáng, đơn giản và cách cải tiến cơ chế quản lý trạng thái đơn hàng cho cả Khách hàng và Admin.

## User Review Required

> [!IMPORTANT]
> - **Chuyển sang Giao diện Sáng (Light Theme):** Thay đổi các biến CSS chủ đạo trong `style.css` từ tông màu tối (Glassmorphism nền tối) sang nền sáng trang nhã (`#f8fafc`), chữ tối màu (`#0f172a`) và tinh chỉnh dải màu gradient của tiêu đề trang chủ để không bị chìm trên nền trắng.
> - **Cơ chế nút bấm thay đổi Trạng thái của Admin:** Thay thế ô chọn dropdown bằng nút bấm chuyển đổi tuần tự: bấm lần 1 (Xác nhận đơn hàng) -> bấm lần 2 (Đang giao hàng) -> bấm lần 3 (Đã giao hàng hoàn thành). Có một nút "Hủy đơn" riêng biệt bên cạnh.
> - **Khách hàng tự Hủy đơn hàng:** Bổ sung nút "Hủy đơn hàng" trực tiếp tại danh sách đơn hàng của Khách hàng (`orders.php`). Nút này chỉ hiển thị khi đơn hàng đang ở trạng thái `Chờ xác nhận` hoặc `Đã xác nhận`. Khi hủy, hệ thống sẽ **tự động phục hồi lại số lượng tồn kho** của sách trong đơn hàng.

## Proposed Changes

---

### 1. Nâng cấp Helpers & CSS Giao diện Sáng

#### [MODIFY] [db_helper.php](file:///D:/CODE/CODE_VS/Demo%20PHP/db_helper.php)
- Thêm hàm `cancel_order($order_id)` xử lý hủy đơn hàng tập trung:
  - Cập nhật trạng thái đơn hàng thành `Đã hủy`.
  - Truy vấn toàn bộ sách trong chi tiết đơn hàng đó và chạy câu lệnh `UPDATE books SET quantity = quantity + qty WHERE id = book_id` để hoàn kho.

#### [MODIFY] [style.css](file:///D:/CODE/CODE_VS/Demo%20PHP/style.css)
- Thay đổi hệ màu sắc `:root` sang giao diện sáng (`--bg-primary: #f8fafc;`, `--bg-secondary: #ffffff;`, `--text-main: #0f172a;`, v.v.).
- Thay đổi gradient của `.hero-banner h1` để bắt đầu bằng màu sẫm (`#0f172a`) thay vì màu trắng.
- Tinh chỉnh các hộp glassmorphism, viền bảng và dropdown của Bootstrap để hiển thị đẹp mắt trên nền sáng.

---

### 2. Giao diện xem đơn hàng của Khách hàng

#### [MODIFY] [orders.php](file:///D:/CODE/CODE_VS/Demo%20PHP/orders.php)
- Thêm cột hành động cho đơn hàng: Nếu đơn hàng ở trạng thái `Chờ xác nhận` hoặc `Đã xác nhận`, hiển thị nút "Hủy đơn hàng" màu đỏ.
- Khi người dùng click vào nút Hủy đơn, gửi yêu cầu hủy bằng phương thức POST (hoặc GET gửi kèm ID) đến chính trang `orders.php` và gọi hàm `cancel_order($order_id)`.
- Hiển thị thông báo thành công hoặc lỗi tương ứng.

---

### 3. Bảng quản trị Admin

#### [MODIFY] [admin.php](file:///D:/CODE/CODE_VS/Demo%20PHP/admin.php)
- Thay đổi Form cập nhật trạng thái đơn hàng:
  - Loại bỏ dropdown select.
  - Sử dụng nút bấm hành động tuần tự tùy thuộc vào trạng thái hiện tại:
    - Trạng thái `Chờ xác nhận`: Nút bấm **"Xác nhận đơn hàng"** (cập nhật lên `Đã xác nhận`).
    - Trạng thái `Đã xác nhận`: Nút bấm **"Bắt đầu giao hàng"** (cập nhật lên `Đang giao`).
    - Trạng thái `Đang giao`: Nút bấm **"Hoàn thành giao hàng"** (cập nhật lên `Đã giao`).
    - Trạng thái `Đã giao` hoặc `Đã hủy`: Không hiển thị nút chuyển tiếp.
  - Hiển thị nút **"Hủy đơn hàng"** (màu đỏ) bên cạnh nút hành động trên nếu trạng thái chưa phải là `Đã giao` hoặc `Đã hủy`. Khi Admin hủy đơn hàng, hệ thống cũng gọi hàm `cancel_order($order_id)` để hoàn kho tự động.

---

## Verification Plan

### Manual Verification
1. **Kiểm tra Giao diện Sáng:** Truy cập trang chủ, trang chi tiết sách, trang giỏ hàng và thanh toán. Đảm bảo toàn bộ chữ màu đen dễ đọc, nền sáng dịu mắt, các tiêu đề rõ nét và viền mờ của hộp glassmorphic hiển thị hài hòa.
2. **Kiểm tra Khách hàng Hủy đơn hàng & Hoàn kho:**
   - Xem số lượng tồn kho của một cuốn sách (ví dụ: đang còn 5 cuốn).
   - Đặt một đơn hàng mua 2 cuốn của sách đó (tồn kho giảm còn 3).
   - Vào mục "Đơn hàng của tôi" của Khách hàng, nhấp nút **Hủy đơn** ở trạng thái `Chờ xác nhận` và xác nhận.
   - Kiểm tra xem trạng thái đơn chuyển thành `Đã hủy` và kiểm tra lại tồn kho sách đã tự động tăng lại thành 5 cuốn chưa.
   - Đặt đơn hàng mới, đổi trạng thái sang `Đã xác nhận` (trong admin), sau đó khách hàng nhấp Hủy đơn từ trang cá nhân. Xác nhận đơn hàng hủy được và hoàn kho đúng.
3. **Kiểm tra chuyển tiếp trạng thái Admin:**
   - Vào Admin -> Quản lý đơn hàng.
   - Với đơn hàng mới đặt (`Chờ xác nhận`), click nút **Xác nhận đơn hàng**. Trạng thái chuyển thành `Đã xác nhận`.
   - Click nút **Bắt đầu giao hàng**. Trạng thái chuyển thành `Đang giao`.
   - Click nút **Hoàn thành giao hàng**. Trạng thái chuyển thành `Đã giao` và các nút hành động biến mất.
