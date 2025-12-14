-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 14, 2025 lúc 06:15 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `booking`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `admin_role` enum('Super','HR_Admin','IT_Support') NOT NULL DEFAULT 'HR_Admin',
  `hashed_pass` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admins`
--

INSERT INTO `admins` (`admin_id`, `full_name`, `email`, `phone_number`, `admin_role`, `hashed_pass`, `created_at`, `updated_at`) VALUES
(1, 'Quản Trị Viên', 'admin@system.com', '0987654321', 'Super', '$2y$10$4RGNscV.KHKzvePU9V8CL.P75pPsP06kCY/cotC7m8mvkEdgaLE9i', '2025-12-14 02:17:04', '2025-12-14 02:17:25'),
(2, 'Quản Lý Nhân Sự', 'hr@system.com', '0901122334', 'HR_Admin', '$2y$10$4RGNscV.KHKzvePU9V8CL.P75pPsP06kCY/cotC7m8mvkEdgaLE9i', '2025-12-14 02:17:04', '2025-12-14 02:17:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `reason_for_visit` text DEFAULT NULL,
  `status` enum('Pending','Scheduled','Confirmed','Waiting','Examining','Completed','Cancelled') DEFAULT 'Pending',
  `paid_amount` decimal(15,0) DEFAULT 0,
  `service_id` int(11) DEFAULT NULL,
  `is_emergency` tinyint(1) DEFAULT 0,
  `is_walkin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `queued_at` datetime DEFAULT NULL,
  `diagnosis` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `reason_for_visit`, `status`, `paid_amount`, `service_id`, `is_emergency`, `is_walkin`, `created_at`, `updated_at`, `queued_at`, `diagnosis`) VALUES
(17, 1, 1, '2025-12-27', '17:00:00', 'Nhồi', 'Completed', 750000, 4, 0, 0, '2025-12-14 15:54:08', NULL, '2025-12-14 22:54:29', 'Nguuu'),
(18, 1, 4, '2025-12-19', '17:00:00', '123', 'Waiting', 400000, 40, 0, 0, '2025-12-14 17:04:03', NULL, '2025-12-15 00:07:21', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `icon_class` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `icon_class`, `description`) VALUES
(1, 'Nội Tổng Quát', 'fa-stethoscope', 'Chẩn đoán và điều trị các bệnh lý nội khoa phổ biến.'),
(2, 'Tim Mạch', 'fa-heartbeat', 'Chuyên về các bệnh lý tim và mạch máu.'),
(3, 'Nhi Khoa', 'fa-child', 'Khám và điều trị cho trẻ em.'),
(4, 'Răng Hàm Mặt', 'fa-tooth', 'Chăm sóc sức khỏe răng miệng.'),
(5, 'Da Liễu', 'fa-syringe', 'Chẩn đoán và điều trị các bệnh về da.'),
(6, 'Sản Phụ Khoa', 'fa-baby', 'Chăm sóc sức khỏe phụ nữ và thai sản.'),
(7, 'Tai Mũi Họng', 'fa-head-side-mask', 'Chẩn đoán và điều trị các bệnh về TMH.'),
(8, 'Cơ Xương Khớp', 'fa-bone', 'Chuyên về các bệnh lý xương, khớp và cơ.'),
(9, 'Mắt', 'fa-eye', 'Khám và điều trị các bệnh về mắt.'),
(10, 'Truyền Nhiễm', 'fa-virus', 'Chẩn đoán và điều trị các bệnh truyền nhiễm.'),
(11, 'Hồi Sức Cấp Cứu', 'fa-first-aid', 'Cung cấp chăm sóc y tế chuyên sâu cho bệnh nhân nặng và khẩn cấp.'),
(12, 'Y Học Cổ Truyền', 'fa-spa', 'Sử dụng các phương pháp chữa bệnh truyền thống, bao gồm châm cứu và thuốc Đông y.'),
(13, 'Phục Hồi Chức Năng', 'fa-wheelchair', 'Giúp bệnh nhân phục hồi sau chấn thương hoặc bệnh tật.'),
(14, 'Gây Mê Hồi Sức', 'fa-lungs', 'Thực hiện các quy trình gây mê và theo dõi bệnh nhân trong và sau phẫu thuật.'),
(15, 'Ung Bướu', 'fa-dna', 'Chẩn đoán và điều trị các loại ung thư.'),
(16, 'Thận Tiết Niệu', 'fa-filter', 'Khám và điều trị các bệnh lý về thận và đường tiết niệu.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `license_code` varchar(50) NOT NULL DEFAULT 'BS000000',
  `date_of_birth` date DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `login_id` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL COMMENT 'Mã băm mật khẩu cho việc đăng nhập hệ thống',
  `department_id` int(11) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `biography` text DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE','ON_LEAVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `experience_years` int(11) DEFAULT 0,
  `education` varchar(255) DEFAULT NULL,
  `working_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `full_name`, `license_code`, `date_of_birth`, `phone_number`, `email`, `login_id`, `profile_picture`, `password_hash`, `department_id`, `license_number`, `biography`, `status`, `created_at`, `experience_years`, `education`, `working_location`) VALUES
(1, 'TS. BS. Nguyễn Văn Thành', 'BS000000', '1975-05-20', '0901234567', 'thanh.nv@example.com', NULL, NULL, '$2y$10$586nhH2maoWJ4njSZr7GMOZPShaiJ3F2MWHVLrWXM6WX7PFUZNnqG', 2, 'L1002A', 'Thạc sĩ, Bác sĩ chuyên khoa Nội Tổng Quát. Với hơn 18 năm kinh nghiệm, Bác sĩ đã chẩn đoán và điều trị thành công hàng ngàn trường hợp bệnh lý nội khoa phức tạp, đặc biệt là các bệnh lý chuyển hóa, hô hấp và tiêu hóa mãn tính. Ông từng là Trưởng khoa Nội Tổng Quát tại một bệnh viện lớn. Bác sĩ luôn ưu tiên phương pháp điều trị cá thể hóa, kết hợp giữa thuốc men và điều chỉnh lối sống để đạt hiệu quả tốt nhất cho bệnh nhân.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Bác sĩ nội trú', 'Bệnh viện Đa khoa Trung ương'),
(2, 'ThS. BS. Trần Thị Mai', 'BS000000', '1988-11-10', '0987654321', 'mai.tt@example.com', NULL, NULL, 'hashed_pass_2', 3, 'L1003B', 'Phó Giáo sư, Tiến sĩ, Bác sĩ chuyên khoa Tim Mạch can thiệp. Tốt nghiệp Tiến sĩ Y khoa tại Pháp, Bác sĩ là một trong những chuyên gia hàng đầu về đặt stent, điều trị suy tim và rối loạn nhịp tim. Ông có hơn 25 năm kinh nghiệm, đã tham gia nhiều hội thảo quốc tế và là tác giả của nhiều công trình nghiên cứu khoa học uy tín được công bố rộng rãi.', 'ACTIVE', '2025-12-13 06:22:30', 15, 'Thạc sĩ Y học', 'Phòng khám Chuyên khoa Z, Quận 1'),
(3, 'BSCKI. Lê Văn Hùng', 'BS000000', '1982-03-01', '0912345678', 'hung.lv@example.com', NULL, NULL, 'hashed_pass_3', 1, 'L1001C', 'Bác sĩ Chuyên khoa II, chuyên ngành Nhi khoa. Với kinh nghiệm 12 năm làm việc tại Khoa Cấp cứu Nhi, Bác sĩ có chuyên môn sâu trong điều trị các bệnh lý thường gặp ở trẻ em như viêm phổi, sốt xuất huyết và các vấn đề dinh dưỡng. Bác sĩ nổi tiếng với sự nhẹ nhàng, tận tâm và khả năng giao tiếp hiệu quả với cả trẻ em và phụ huynh.', 'ACTIVE', '2025-12-13 06:22:30', 4, 'Thạc sĩ Y học', 'Khoa Nội, Bệnh viện Tỉnh A'),
(4, 'BS. Nguyễn Thị Hoa', 'BS000000', '1995-07-25', '0977889900', 'hoa.nt@example.com', NULL, NULL, 'hashed_pass_4', 4, 'L1004D', 'Bác sĩ Răng Hàm Mặt uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 18, 'Bác sĩ đa khoa', 'Khoa Nội, Bệnh viện Tỉnh A'),
(5, 'PGS. TS. Hà Thị Minh', 'BS000000', '1968-09-12', '0919876543', 'minh.ht@example.com', NULL, NULL, 'hashed_pass_5', 2, 'L1005E', 'Bác sĩ Tim Mạch uy tín hàng đầu. Tiến sĩ Y khoa, chuyên sâu trong lĩnh vực tim mạch can thiệp, điều trị suy tim và các bệnh lý mạch vành phức tạp. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 11, 'Tiến sĩ Y khoa (MD)', 'Khoa Nội, Bệnh viện Tỉnh A'),
(6, 'BS. Lê Quốc Đạt', 'BS000000', '1990-01-20', '0988776655', 'dat.lq@example.com', NULL, NULL, 'hashed_pass_6', 5, 'L1006F', 'Bác sĩ Da Liễu uy tín hàng đầu. Chuyên gia trong điều trị mụn trứng cá, các bệnh viêm da dị ứng, và các thủ thuật thẩm mỹ da liễu hiện đại. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Bác sĩ nội trú', 'Cơ sở Y tế địa phương'),
(7, 'TS. BS. Võ Văn Kiên', 'BS000000', '1970-04-15', '0903112233', 'kien.vv@example.com', NULL, NULL, 'hashed_pass_7', 6, 'L1007G', 'Bác sĩ Sản Phụ Khoa uy tín hàng đầu. Bác sĩ chuyên khoa II, cung cấp dịch vụ chăm sóc thai sản toàn diện, điều trị vô sinh và các bệnh lý phụ khoa khác. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 14, 'Thạc sĩ Y học', 'Phòng khám Chuyên khoa Z, Quận 1'),
(8, 'BSCKII. Phan Thanh Nhàn', 'BS000000', '1978-06-30', '0915443322', 'nhan.pt@example.com', NULL, NULL, 'hashed_pass_8', 1, 'L1008H', 'Bác sĩ Nội Tổng Quát uy tín hàng đầu. Có hơn 20 năm kinh nghiệm trong chẩn đoán và điều trị các bệnh lý mãn tính, ưu tiên phương pháp tiếp cận toàn diện cho từng bệnh nhân. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 3, 'Tiến sĩ Y khoa (MD)', 'Bệnh viện Đa khoa Trung ương'),
(9, 'BS. Nguyễn Thị Yến', 'BS000000', '1985-08-05', '0979111222', 'yen.nt@example.com', NULL, NULL, 'hashed_pass_9', 3, 'L1009I', 'Bác sĩ Nhi Khoa uy tín hàng đầu. Thạc sĩ Y học, chuyên môn về các bệnh lý hô hấp, dinh dưỡng và phát triển trẻ em. Được phụ huynh tin tưởng bởi sự tận tâm và nhẹ nhàng. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 19, 'Bác sĩ chuyên khoa II', 'Bệnh viện Đa khoa Trung ương'),
(10, 'ThS. BS. Phạm Ngọc Sơn', 'BS000000', '1980-12-29', '0908775533', 'son.pn@example.com', NULL, NULL, 'hashed_pass_10', 8, 'L1010J', 'Bác sĩ Cơ Xương Khớp uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 14, 'Bác sĩ chuyên khoa II', 'Phòng khám Chuyên khoa Z, Quận 1'),
(11, 'BS. Đỗ Trọng Nghĩa', 'BS000000', '1992-02-18', '0934567890', 'nghia.dt@example.com', NULL, NULL, 'hashed_pass_11', 7, 'L1011K', 'Bác sĩ Tai Mũi Họng uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Tiến sĩ Y khoa (MD)', 'Cơ sở Y tế địa phương'),
(12, 'BS. Đặng Văn Tú', 'BS000000', '1973-10-03', '0945678901', 'tu.dv@example.com', NULL, NULL, 'hashed_pass_12', 4, 'L1012L', 'Bác sĩ Răng Hàm Mặt uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'INACTIVE', '2025-12-13 06:22:30', 8, 'Bác sĩ nội trú', 'Cơ sở Y tế địa phương'),
(13, 'BS. Bùi Thị Hồng', 'BS000000', '1989-05-22', '0966778899', 'hong.bt@example.com', NULL, NULL, 'hashed_pass_13', 5, 'L1013M', 'Bác sĩ Da Liễu uy tín hàng đầu. Chuyên gia trong điều trị mụn trứng cá, các bệnh viêm da dị ứng, và các thủ thuật thẩm mỹ da liễu hiện đại. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Thạc sĩ Y học', 'Cơ sở Y tế địa phương'),
(14, 'BS. Cao Minh Đức', 'BS000000', '1983-01-14', '0978123456', 'duc.cm@example.com', NULL, NULL, 'hashed_pass_14', 1, 'L1014N', 'Bác sĩ Nội Tổng Quát uy tín hàng đầu. Có hơn 20 năm kinh nghiệm trong chẩn đoán và điều trị các bệnh lý mãn tính, ưu tiên phương pháp tiếp cận toàn diện cho từng bệnh nhân. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ON_LEAVE', '2025-12-13 06:22:30', 8, 'Bác sĩ đa khoa', 'Cơ sở Y tế địa phương'),
(15, 'ThS. BS. Phan Văn Lực', 'BS000000', '1984-07-17', '0912111333', 'luc.pv@email.com', NULL, NULL, 'hashed_pass_15', 9, 'L1015P', 'Bác sĩ Mắt uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 8, 'Tiến sĩ Y khoa (MD)', 'Bệnh viện Đa khoa Trung ương'),
(16, 'BSCKI. Nguyễn Thị Thanh', 'BS000000', '1979-10-05', '0913222444', 'thanh.nt@email.com', NULL, NULL, 'hashed_pass_16', 10, 'L1016Q', 'Bác sĩ Truyền Nhiễm uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 14, 'Bác sĩ chuyên khoa II', 'Bệnh viện Đa khoa Trung ương'),
(17, 'TS. BS. Hoàng Minh Quân', 'BS000000', '1970-02-28', '0914333555', 'quan.hm@email.com', NULL, NULL, 'hashed_pass_17', 11, 'L1017R', 'Bác sĩ Hồi Sức Cấp Cứu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 16, 'Bác sĩ nội trú', 'Bệnh viện Đa khoa Trung ương'),
(18, 'BS. Lê Thị Phương', 'BS000000', '1986-09-09', '0915444666', 'phuong.lt@email.com', NULL, NULL, 'hashed_pass_18', 12, 'L1018S', 'Bác sĩ Y Học Cổ Truyền uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 16, 'Bác sĩ đa khoa', 'Cơ sở Y tế địa phương'),
(19, 'BSCKII. Trần Văn Nam', 'BS000000', '1976-04-14', '0916555777', 'nam.tv@email.com', NULL, NULL, 'hashed_pass_19', 13, 'L1019T', 'Bác sĩ Phục Hồi Chức Năng uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 10, 'Tiến sĩ Y khoa (MD)', 'Khoa Nội, Bệnh viện Tỉnh A'),
(20, 'BS. Đỗ Thị Mai Anh', 'BS000000', '1989-11-25', '0917666888', 'anh.dtm@email.com', NULL, NULL, 'hashed_pass_20', 14, 'L1020U', 'Bác sĩ Gây Mê Hồi Sức uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 17, 'Thạc sĩ Y học', 'Phòng khám Chuyên khoa Z, Quận 1'),
(21, 'GS. TS. Phạm Quang Vinh', 'BS000000', '1965-01-01', '0918777999', 'vinh.pq@email.com', NULL, NULL, 'hashed_pass_21', 15, 'L1021V', 'Bác sĩ Ung Bướu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 11, 'Thạc sĩ Y học', 'Bệnh viện Đa khoa Trung ương'),
(22, 'BSCKI. Vũ Đình Thắng', 'BS000000', '1981-03-19', '0919888000', 'thang.vd@email.com', NULL, NULL, 'hashed_pass_22', 16, 'L1022W', 'Bác sĩ Thận Tiết Niệu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 20, 'Bác sĩ đa khoa', 'Bệnh viện Đa khoa Trung ương'),
(23, 'BS. Nguyễn Phương Thảo', 'BS000000', '1993-12-08', '0920112233', 'thao.np@email.com', NULL, NULL, 'hashed_pass_23', 3, 'L1023X', 'Bác sĩ Nhi Khoa uy tín hàng đầu. Thạc sĩ Y học, chuyên môn về các bệnh lý hô hấp, dinh dưỡng và phát triển trẻ em. Được phụ huynh tin tưởng bởi sự tận tâm và nhẹ nhàng. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 10, 'Tiến sĩ Y khoa (MD)', 'Bệnh viện Đa khoa Trung ương'),
(24, 'BSCKII. Mai Xuân Tùng', 'BS000000', '1974-06-03', '0921223344', 'tung.mx@email.com', NULL, NULL, 'hashed_pass_24', 2, 'L1024Y', 'Bác sĩ Tim Mạch uy tín hàng đầu. Tiến sĩ Y khoa, chuyên sâu trong lĩnh vực tim mạch can thiệp, điều trị suy tim và các bệnh lý mạch vành phức tạp. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 9, 'Thạc sĩ Y học', 'Cơ sở Y tế địa phương'),
(25, 'BSCKII. Nguyễn Văn Trọng', 'BS000000', '1965-03-25', '0922334455', 'trong.nv@email.com', NULL, NULL, 'hashed_pass_25', 1, 'L1025Z', 'Bác sĩ Nội Tổng Quát uy tín hàng đầu. Có hơn 20 năm kinh nghiệm trong chẩn đoán và điều trị các bệnh lý mãn tính, ưu tiên phương pháp tiếp cận toàn diện cho từng bệnh nhân. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 15, 'Bác sĩ đa khoa', 'Bệnh viện Đa khoa Trung ương'),
(26, 'TS. BS. Hồ Thị Lệ', 'BS000000', '1972-09-10', '0923445566', 'le.ht@email.com', NULL, NULL, 'hashed_pass_26', 2, 'L1026AA', 'Bác sĩ Tim Mạch uy tín hàng đầu. Tiến sĩ Y khoa, chuyên sâu trong lĩnh vực tim mạch can thiệp, điều trị suy tim và các bệnh lý mạch vành phức tạp. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 14, 'Tiến sĩ Y khoa (MD)', 'Phòng khám Chuyên khoa Z, Quận 1'),
(27, 'BS. Lê Quốc Minh', 'BS000000', '1990-05-18', '0924556677', 'minh.lq@email.com', NULL, NULL, 'hashed_pass_27', 3, 'L1027BB', 'Bác sĩ Nhi Khoa uy tín hàng đầu. Thạc sĩ Y học, chuyên môn về các bệnh lý hô hấp, dinh dưỡng và phát triển trẻ em. Được phụ huynh tin tưởng bởi sự tận tâm và nhẹ nhàng. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 4, 'Tiến sĩ Y khoa (MD)', 'Cơ sở Y tế địa phương'),
(28, 'ThS. BS. Phan Văn Tiến', 'BS000000', '1985-11-02', '0925667788', 'tien.pv@email.com', NULL, NULL, 'hashed_pass_28', 4, 'L1028CC', 'Bác sĩ Răng Hàm Mặt uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Thạc sĩ Y học', 'Khoa Nội, Bệnh viện Tỉnh A'),
(29, 'BS. Đặng Thị Thu', 'BS000000', '1992-04-29', '0926778899', 'thu.dt@email.com', NULL, NULL, 'hashed_pass_29', 5, 'L1029DD', 'Bác sĩ Da Liễu uy tín hàng đầu. Chuyên gia trong điều trị mụn trứng cá, các bệnh viêm da dị ứng, và các thủ thuật thẩm mỹ da liễu hiện đại. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 3, 'Bác sĩ chuyên khoa II', 'Phòng khám Chuyên khoa Z, Quận 1'),
(30, 'BSCKI. Mai Thị Yến', 'BS000000', '1978-01-15', '0927889900', 'yen.mt@email.com', NULL, NULL, 'hashed_pass_30', 6, 'L1030EE', 'Bác sĩ Sản Phụ Khoa uy tín hàng đầu. Bác sĩ chuyên khoa II, cung cấp dịch vụ chăm sóc thai sản toàn diện, điều trị vô sinh và các bệnh lý phụ khoa khác. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 5, 'Bác sĩ nội trú', 'Phòng khám Chuyên khoa Z, Quận 1'),
(31, 'BS. Phạm Quốc Đạt', 'BS000000', '1983-10-06', '0928990011', 'dat.pq@email.com', NULL, NULL, 'hashed_pass_31', 7, 'L1031FF', 'Bác sĩ Tai Mũi Họng uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 19, 'Tiến sĩ Y khoa (MD)', 'Cơ sở Y tế địa phương'),
(32, 'BSCKII. Nguyễn Văn Tài', 'BS000000', '1970-12-20', '0929001122', 'tai.nv@email.com', NULL, NULL, 'hashed_pass_32', 8, 'L1032GG', 'Bác sĩ Cơ Xương Khớp uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ON_LEAVE', '2025-12-13 06:22:30', 13, 'Bác sĩ đa khoa', 'Khoa Nội, Bệnh viện Tỉnh A'),
(33, 'ThS. BS. Hồ Ngọc Hà', 'BS000000', '1988-06-12', '0930112233', 'ha.hn@email.com', NULL, NULL, 'hashed_pass_33', 9, 'L1033HH', 'Bác sĩ Mắt uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 5, 'Bác sĩ nội trú', 'Khoa Nội, Bệnh viện Tỉnh A'),
(34, 'BS. Trần Đình Phương', 'BS000000', '1991-08-27', '0931223344', 'phuong.td@email.com', NULL, NULL, 'hashed_pass_34', 10, 'L1034II', 'Bác sĩ Truyền Nhiễm uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 12, 'Tiến sĩ Y khoa (MD)', 'Phòng khám Chuyên khoa Z, Quận 1'),
(35, 'BSCKI. Vũ Quang Mạnh', 'BS000000', '1975-02-01', '0932334455', 'manh.vq@email.com', NULL, NULL, 'hashed_pass_35', 11, 'L1035JJ', 'Bác sĩ Hồi Sức Cấp Cứu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 12, 'Thạc sĩ Y học', 'Cơ sở Y tế địa phương'),
(36, 'BS. Lê Hồng Sơn', 'BS000000', '1980-04-19', '0933445566', 'son.lh@email.com', NULL, NULL, 'hashed_pass_36', 12, 'L1036KK', 'Bác sĩ Y Học Cổ Truyền uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 19, 'Bác sĩ chuyên khoa II', 'Phòng khám Chuyên khoa Z, Quận 1'),
(37, 'BS. Nguyễn Thị Kim', 'BS000000', '1994-07-07', '0934556677', 'kim.nt@email.com', NULL, NULL, 'hashed_pass_37', 13, 'L1037LL', 'Bác sĩ Phục Hồi Chức Năng uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 15, 'Bác sĩ nội trú', 'Khoa Nội, Bệnh viện Tỉnh A'),
(38, 'TS. BS. Phạm Văn Nam', 'BS000000', '1968-09-30', '0935667788', 'nam.pv@email.com', NULL, NULL, 'hashed_pass_38', 14, 'L1038MM', 'Bác sĩ Gây Mê Hồi Sức uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 9, 'Bác sĩ nội trú', 'Khoa Nội, Bệnh viện Tỉnh A'),
(39, 'PGS. TS. Trần Văn Chung', 'BS000000', '1960-12-05', '0936778899', 'chung.tv@email.com', NULL, NULL, 'hashed_pass_39', 15, 'L1039NN', 'Bác sĩ Ung Bướu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 15, 'Tiến sĩ Y khoa (MD)', 'Cơ sở Y tế địa phương'),
(40, 'BSCKI. Hồ Thị Thủy', 'BS000000', '1982-03-11', '0937889900', 'thuy.ht@email.com', NULL, NULL, 'hashed_pass_40', 16, 'L1040OO', 'Bác sĩ Thận Tiết Niệu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 9, 'Bác sĩ chuyên khoa II', 'Khoa Nội, Bệnh viện Tỉnh A'),
(41, 'BS. Mai Thanh Tùng', 'BS000000', '1993-01-22', '0938990011', 'tung.mt@email.com', NULL, NULL, 'hashed_pass_41', 1, 'L1041PP', 'Bác sĩ Nội Tổng Quát uy tín hàng đầu. Có hơn 20 năm kinh nghiệm trong chẩn đoán và điều trị các bệnh lý mãn tính, ưu tiên phương pháp tiếp cận toàn diện cho từng bệnh nhân. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 3, 'Bác sĩ đa khoa', 'Cơ sở Y tế địa phương'),
(42, 'BS. Vũ Thị Hồng', 'BS000000', '1987-05-01', '0939001122', 'hong.vt@email.com', NULL, NULL, 'hashed_pass_42', 2, 'L1042QQ', 'Bác sĩ Tim Mạch uy tín hàng đầu. Tiến sĩ Y khoa, chuyên sâu trong lĩnh vực tim mạch can thiệp, điều trị suy tim và các bệnh lý mạch vành phức tạp. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ON_LEAVE', '2025-12-13 06:22:30', 18, 'Tiến sĩ Y khoa (MD)', 'Bệnh viện Đa khoa Trung ương'),
(43, 'BSCKI. Nguyễn Hải Long', 'BS000000', '1977-11-16', '0940112233', 'long.nh@email.com', NULL, NULL, 'hashed_pass_43', 3, 'L1043RR', 'Bác sĩ Nhi Khoa uy tín hàng đầu. Thạc sĩ Y học, chuyên môn về các bệnh lý hô hấp, dinh dưỡng và phát triển trẻ em. Được phụ huynh tin tưởng bởi sự tận tâm và nhẹ nhàng. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 7, 'Thạc sĩ Y học', 'Phòng khám Chuyên khoa Z, Quận 1'),
(44, 'BS. Trịnh Văn A', 'BS000000', '1995-08-04', '0941223344', 'a.tv@email.com', NULL, NULL, 'hashed_pass_44', 5, 'L1044SS', 'Bác sĩ Da Liễu uy tín hàng đầu. Chuyên gia trong điều trị mụn trứng cá, các bệnh viêm da dị ứng, và các thủ thuật thẩm mỹ da liễu hiện đại. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 14, 'Bác sĩ nội trú', 'Khoa Nội, Bệnh viện Tỉnh A'),
(45, 'ThS. BS. Nguyễn Minh Đức', 'BS000000', '1980-01-01', '0942334455', 'duc.nm@email.com', NULL, NULL, 'hashed_pass_45', 1, 'L1045TT', 'Bác sĩ Nội Tổng Quát uy tín hàng đầu. Có hơn 20 năm kinh nghiệm trong chẩn đoán và điều trị các bệnh lý mãn tính, ưu tiên phương pháp tiếp cận toàn diện cho từng bệnh nhân. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 16, 'Bác sĩ nội trú', 'Cơ sở Y tế địa phương'),
(46, 'BS. Lê Quốc Việt', 'BS000000', '1991-02-02', '0943445566', 'viet.lq@email.com', NULL, NULL, 'hashed_pass_46', 2, 'L1046UU', 'Bác sĩ Tim Mạch uy tín hàng đầu. Tiến sĩ Y khoa, chuyên sâu trong lĩnh vực tim mạch can thiệp, điều trị suy tim và các bệnh lý mạch vành phức tạp. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 8, 'Tiến sĩ Y khoa (MD)', 'Bệnh viện Đa khoa Trung ương'),
(47, 'BSCKI. Trần Thị Lan', 'BS000000', '1975-03-03', '0944556677', 'lan.tt@email.com', NULL, NULL, 'hashed_pass_47', 3, 'L1047VV', 'Bác sĩ Nhi Khoa uy tín hàng đầu. Thạc sĩ Y học, chuyên môn về các bệnh lý hô hấp, dinh dưỡng và phát triển trẻ em. Được phụ huynh tin tưởng bởi sự tận tâm và nhẹ nhàng. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 14, 'Thạc sĩ Y học', 'Bệnh viện Đa khoa Trung ương'),
(48, 'BS. Võ Văn Trường', 'BS000000', '1987-04-04', '0945667788', 'truong.vv@email.com', NULL, NULL, 'hashed_pass_48', 4, 'L1048WW', 'Bác sĩ Răng Hàm Mặt uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 12, 'Bác sĩ nội trú', 'Phòng khám Chuyên khoa Z, Quận 1'),
(49, 'BSCKII. Hoàng Thị Hương', 'BS000000', '1969-05-05', '0946778899', 'huong.ht@email.com', NULL, NULL, 'hashed_pass_49', 5, 'L1049XX', 'Bác sĩ Da Liễu uy tín hàng đầu. Chuyên gia trong điều trị mụn trứng cá, các bệnh viêm da dị ứng, và các thủ thuật thẩm mỹ da liễu hiện đại. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 13, 'Bác sĩ chuyên khoa II', 'Bệnh viện Đa khoa Trung ương'),
(50, 'PGS. TS. Hồ Văn Khánh', 'BS000000', '1962-06-06', '0947889900', 'khanh.hv@email.com', NULL, NULL, 'hashed_pass_50', 6, 'L1050YY', 'Bác sĩ Sản Phụ Khoa uy tín hàng đầu. Bác sĩ chuyên khoa II, cung cấp dịch vụ chăm sóc thai sản toàn diện, điều trị vô sinh và các bệnh lý phụ khoa khác. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 8, 'Bác sĩ đa khoa', 'Bệnh viện Đa khoa Trung ương'),
(51, 'BS. Nguyễn Thị Kim Oanh', 'BS000000', '1994-07-07', '0948990011', 'oanh.ntk@email.com', NULL, NULL, 'hashed_pass_51', 7, 'L1051ZZ', 'Bác sĩ Tai Mũi Họng uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 18, 'Tiến sĩ Y khoa (MD)', 'Khoa Nội, Bệnh viện Tỉnh A'),
(52, 'TS. BS. Đào Minh Khoa', 'BS000000', '1977-08-08', '0949001122', 'khoa.dm@email.com', NULL, NULL, 'hashed_pass_52', 8, 'L1052A1', 'Bác sĩ Cơ Xương Khớp uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 7, 'Bác sĩ nội trú', 'Cơ sở Y tế địa phương'),
(53, 'BS. Phạm Văn Chính', 'BS000000', '1981-09-09', '0950112233', 'chinh.pv@email.com', NULL, NULL, 'hashed_pass_53', 9, 'L1053B2', 'Bác sĩ Mắt uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 14, 'Thạc sĩ Y học', 'Khoa Nội, Bệnh viện Tỉnh A'),
(54, 'BSCKI. Vũ Thị Hà', 'BS000000', '1973-10-10', '0951223344', 'ha.vt@email.com', NULL, NULL, 'hashed_pass_54', 10, 'L1054C3', 'Bác sĩ Truyền Nhiễm uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Bác sĩ chuyên khoa II', 'Khoa Nội, Bệnh viện Tỉnh A'),
(55, 'BS. Mai Văn Tú', 'BS000000', '1989-11-11', '0952334455', 'tu.mv@email.com', NULL, NULL, 'hashed_pass_55', 11, 'L1055D4', 'Bác sĩ Hồi Sức Cấp Cứu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 11, 'Bác sĩ nội trú', 'Khoa Nội, Bệnh viện Tỉnh A'),
(56, 'ThS. BS. Nguyễn Hồng Nhung', 'BS000000', '1982-12-12', '0953445566', 'nhung.nh@email.com', NULL, NULL, 'hashed_pass_56', 12, 'L1056E5', 'Bác sĩ Y Học Cổ Truyền uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Bác sĩ đa khoa', 'Bệnh viện Đa khoa Trung ương'),
(57, 'BS. Lê Quốc Huy', 'BS000000', '1990-01-13', '0954556677', 'huy.lq@email.com', NULL, NULL, 'hashed_pass_57', 13, 'L1057F6', 'Bác sĩ Phục Hồi Chức Năng uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Bác sĩ chuyên khoa II', 'Phòng khám Chuyên khoa Z, Quận 1'),
(58, 'BSCKI. Trần Văn Hiếu', 'BS000000', '1970-02-14', '0955667788', 'hieu.tv@email.com', NULL, NULL, 'hashed_pass_58', 14, 'L1058G7', 'Bác sĩ Gây Mê Hồi Sức uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 5, 'Thạc sĩ Y học', 'Cơ sở Y tế địa phương'),
(59, 'BS. Vũ Thị Thu Trang', 'BS000000', '1985-03-15', '0956778899', 'trang.vtt@email.com', NULL, NULL, 'hashed_pass_59', 15, 'L1059H8', 'Bác sĩ Ung Bướu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 18, 'Bác sĩ chuyên khoa II', 'Bệnh viện Đa khoa Trung ương'),
(60, 'BSCKII. Nguyễn Văn Toàn', 'BS000000', '1976-04-16', '0957889900', 'toan.nv@email.com', NULL, NULL, 'hashed_pass_60', 16, 'L1060I9', 'Bác sĩ Thận Tiết Niệu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 8, 'Thạc sĩ Y học', 'Phòng khám Chuyên khoa Z, Quận 1'),
(61, 'BS. Phan Thị Mai', 'BS000000', '1993-05-17', '0958990011', 'mai.pt@email.com', NULL, NULL, 'hashed_pass_61', 1, 'L1061J0', 'Bác sĩ Nội Tổng Quát uy tín hàng đầu. Có hơn 20 năm kinh nghiệm trong chẩn đoán và điều trị các bệnh lý mãn tính, ưu tiên phương pháp tiếp cận toàn diện cho từng bệnh nhân. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 19, 'Bác sĩ chuyên khoa II', 'Cơ sở Y tế địa phương'),
(62, 'BS. Trần Đình Khánh', 'BS000000', '1984-06-18', '0959001122', 'khanh.td@email.com', NULL, NULL, 'hashed_pass_62', 2, 'L1062K1', 'Bác sĩ Tim Mạch uy tín hàng đầu. Tiến sĩ Y khoa, chuyên sâu trong lĩnh vực tim mạch can thiệp, điều trị suy tim và các bệnh lý mạch vành phức tạp. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 19, 'Bác sĩ nội trú', 'Phòng khám Chuyên khoa Z, Quận 1'),
(63, 'BSCKI. Đỗ Quốc Hưng', 'BS000000', '1979-07-19', '0960112233', 'hung.dq@email.com', NULL, NULL, 'hashed_pass_63', 3, 'L1063L2', 'Bác sĩ Nhi Khoa uy tín hàng đầu. Thạc sĩ Y học, chuyên môn về các bệnh lý hô hấp, dinh dưỡng và phát triển trẻ em. Được phụ huynh tin tưởng bởi sự tận tâm và nhẹ nhàng. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 4, 'Bác sĩ chuyên khoa II', 'Phòng khám Chuyên khoa Z, Quận 1'),
(64, 'BS. Lê Văn Quang', 'BS000000', '1995-08-20', '0961223344', 'quang.lv@email.com', NULL, NULL, 'hashed_pass_64', 4, 'L1064M3', 'Bác sĩ Răng Hàm Mặt uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 19, 'Bác sĩ đa khoa', 'Phòng khám Chuyên khoa Z, Quận 1'),
(65, 'ThS. BS. Nguyễn Thị Thủy', 'BS000000', '1974-09-21', '0962334455', 'thuy.nt@email.com', NULL, NULL, 'hashed_pass_65', 5, 'L1065N4', 'Bác sĩ Da Liễu uy tín hàng đầu. Chuyên gia trong điều trị mụn trứng cá, các bệnh viêm da dị ứng, và các thủ thuật thẩm mỹ da liễu hiện đại. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 3, 'Thạc sĩ Y học', 'Khoa Nội, Bệnh viện Tỉnh A'),
(66, 'BS. Hồ Quốc Duy', 'BS000000', '1988-10-22', '0963445566', 'duy.hq@email.com', NULL, NULL, 'hashed_pass_66', 6, 'L1066O5', 'Bác sĩ Sản Phụ Khoa uy tín hàng đầu. Bác sĩ chuyên khoa II, cung cấp dịch vụ chăm sóc thai sản toàn diện, điều trị vô sinh và các bệnh lý phụ khoa khác. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 7, 'Bác sĩ chuyên khoa II', 'Khoa Nội, Bệnh viện Tỉnh A'),
(67, 'BSCKII. Phạm Minh Chính', 'BS000000', '1967-11-23', '0964556677', 'chinh.pm@email.com', NULL, NULL, 'hashed_pass_67', 7, 'L1067P6', 'Bác sĩ Tai Mũi Họng uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Bác sĩ chuyên khoa II', 'Bệnh viện Đa khoa Trung ương'),
(68, 'BS. Lương Thị Thảo', 'BS000000', '1992-12-24', '0965667788', 'thao.lt@email.com', NULL, NULL, 'hashed_pass_68', 8, 'L1068Q7', 'Bác sĩ Cơ Xương Khớp uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 12, 'Tiến sĩ Y khoa (MD)', 'Bệnh viện Đa khoa Trung ương'),
(69, 'BSCKI. Vũ Thị Hà Giang', 'BS000000', '1983-01-25', '0966778899', 'giang.vth@email.com', NULL, NULL, 'hashed_pass_69', 9, 'L1069R8', 'Bác sĩ Mắt uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 19, 'Bác sĩ nội trú', 'Bệnh viện Đa khoa Trung ương'),
(70, 'BS. Đinh Văn An', 'BS000000', '1990-02-26', '0967889900', 'an.dv@email.com', NULL, NULL, 'hashed_pass_70', 10, 'L1070S9', 'Bác sĩ Truyền Nhiễm uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 4, 'Bác sĩ nội trú', 'Cơ sở Y tế địa phương'),
(71, 'BSCKII. Lê Văn Nam', 'BS000000', '1975-03-27', '0968990011', 'nam.lv@email.com', NULL, NULL, 'hashed_pass_71', 11, 'L1071T0', 'Bác sĩ Hồi Sức Cấp Cứu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 4, 'Tiến sĩ Y khoa (MD)', 'Khoa Nội, Bệnh viện Tỉnh A'),
(72, 'ThS. BS. Nguyễn Thị Phương', 'BS000000', '1986-04-28', '0969001122', 'phuong.nt@email.com', NULL, NULL, 'hashed_pass_72', 12, 'L1072U1', 'Bác sĩ Y Học Cổ Truyền uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 19, 'Bác sĩ nội trú', 'Khoa Nội, Bệnh viện Tỉnh A'),
(73, 'BS. Cao Văn Thanh', 'BS000000', '1994-05-29', '0970112233', 'thanh.cv@email.com', NULL, NULL, 'hashed_pass_73', 13, 'L1073V2', 'Bác sĩ Phục Hồi Chức Năng uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 13, 'Bác sĩ chuyên khoa II', 'Bệnh viện Đa khoa Trung ương'),
(74, 'BSCKI. Trần Văn Phát', 'BS000000', '1971-06-30', '0971223344', 'phat.tv@email.com', NULL, NULL, 'hashed_pass_74', 15, 'L1074W3', 'Bác sĩ Ung Bướu uy tín hàng đầu. Bác sĩ chuyên khoa tại bệnh viện. Tận tâm và chuyên nghiệp, luôn cập nhật các phác đồ điều trị mới nhất trên thế giới. Bác sĩ luôn đặt sức khỏe và sự hài lòng của bệnh nhân lên hàng đầu.', 'ACTIVE', '2025-12-13 06:22:30', 6, 'Thạc sĩ Y học', 'Phòng khám Chuyên khoa Z, Quận 1'),
(75, 'Phạm Mạnh Hùng 123', 'BS092264', NULL, '0862822332', 'BS092264@benhvien.com', NULL, NULL, '$2y$10$KTzOUcjM.ieiCtNPqD6Pw.ycKj5N5aNfUjIVMn1mzu3S2e6xxqiW2', 12, 'L1074W4', NULL, 'ACTIVE', '2025-12-14 17:03:43', 0, '1231', NULL),
(76, 'Phạm Mạnh Hùng Nè', 'L7019I0', NULL, '1231231231', 'L7019I0@benhvien.com', NULL, NULL, '$2y$10$tNSq.fBpYRFceQIcj3XfCO1wxlISe38RLlaKEo6bVHKug828ypzCy', 2, NULL, NULL, 'ACTIVE', '2025-12-14 17:10:04', 0, 'TTTT', NULL),
(77, 'Phạm Mạnh Hùng 12313', 'BS000000', NULL, '0862822198', 'BS000000@benhvien.com', NULL, NULL, '$2y$10$Yh1sUxwiCLccWQJFYnUX8.Tx4pmi.YdZQ0753VW6pDTpFXRaa9gi2', 15, 'L4285V3', NULL, 'ACTIVE', '2025-12-14 17:13:19', 0, '123213', NULL),
(78, 'Phạm Mạnh Hùng', 'BS750139', NULL, '0862122098', 'hung.pm@benhvien.com', NULL, NULL, '$2y$10$3BFT3ELZdrYl7/fzaNG5J.lSJImvMVumZx3TBH3FCrS6BgNGhDuEK', 15, 'L5533W7', NULL, 'ACTIVE', '2025-12-14 17:15:34', 0, '1231', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `medicines`
--

CREATE TABLE `medicines` (
  `medicine_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `unit` varchar(50) DEFAULT 'Viên',
  `stock_quantity` int(11) DEFAULT 1000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `medicines`
--

INSERT INTO `medicines` (`medicine_id`, `medicine_name`, `unit`, `stock_quantity`) VALUES
(1, 'Paracetamol 500mg', 'Viên', 1000),
(2, 'Amoxicillin 500mg', 'Viên', 1000),
(3, 'Ibuprofen 400mg', 'Viên', 1000),
(4, 'Siro ho Prospan', 'Chai', 1000),
(5, 'Vitamin C', 'Viên', 1000),
(6, 'Berberin', 'Viên', 1000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `bhyt_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `patients`
--

INSERT INTO `patients` (`patient_id`, `full_name`, `email`, `phone_number`, `password_hash`, `gender`, `date_of_birth`, `address`, `bhyt_code`, `created_at`) VALUES
(1, 'Phạm Mạnh Hùng', 'hung@gmail.com', '0862822098', '$2y$10$/dbMjbJpDfpJT5UmQAKmM.7Ly09caR/Jx.nM6NGb/ctAvK4pvMhwO', '', '2003-04-24', '', '', '2025-12-13 14:55:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `prescription_details`
--

CREATE TABLE `prescription_details` (
  `prescription_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `dosage` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `prescription_details`
--

INSERT INTO `prescription_details` (`prescription_id`, `appointment_id`, `medicine_id`, `quantity`, `dosage`) VALUES
(1, 17, 5, 5, 'Sáng 1 tối 1');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `rating_score` decimal(2,1) NOT NULL,
  `review_text` text DEFAULT NULL,
  `rating_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ratings`
--

INSERT INTO `ratings` (`rating_id`, `doctor_id`, `patient_id`, `patient_name`, `rating_score`, `review_text`, `rating_date`) VALUES
(1, 1, 1, 'hung', 5.0, 'Good', '2025-12-14 01:40:42'),
(2, 13, 1, 'Phạm Mạnh Hùng', 4.0, '1321', '2025-12-14 01:40:49'),
(3, 13, 1, 'Phạm Mạnh Hùng', 5.0, 'Tốt', '2025-12-14 01:41:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `receptionists`
--

CREATE TABLE `receptionists` (
  `receptionist_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `hashed_pass` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `receptionists`
--

INSERT INTO `receptionists` (`receptionist_id`, `full_name`, `email`, `phone_number`, `hashed_pass`, `created_at`, `updated_at`) VALUES
(1, 'Phạm Mạnh Hùng', 'hung123@gmail.com', '0123345677', '$2y$10$7lVOT6LAZGkIvEwAfGE2Kusp001J4BDfMwyJ80I4rPyYrfXJLGDJC', '2025-12-14 02:36:20', '2025-12-14 02:36:20');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `short_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `department_id`, `price`, `short_description`) VALUES
(1, 'Khám Nội Tổng Quát Định Kỳ', 1, 300000.00, 'Kiểm tra sức khỏe toàn diện, tầm soát bệnh lý thông thường.'),
(2, 'Tư Vấn Dinh Dưỡng & Chuyển Hóa', 1, 450000.00, 'Tư vấn chuyên sâu về tiểu đường, béo phì và các rối loạn chuyển hóa.'),
(3, 'Siêu Âm Tim và Điện Tâm Đồ', 2, 800000.00, 'Đánh giá chức năng tim mạch và phát hiện rối loạn nhịp.'),
(4, 'Khám Sàng Lọc Tim Bẩm Sinh', 2, 750000.00, 'Sàng lọc và chẩn đoán các vấn đề tim bẩm sinh ở trẻ em và người lớn.'),
(5, 'Khám Nhi Khoa Tổng Quát', 3, 350000.00, 'Kiểm tra sức khỏe, tiêm chủng và tư vấn phát triển cho trẻ.'),
(6, 'Điều Trị Bệnh Hô Hấp Trẻ Em', 3, 500000.00, 'Chẩn đoán và điều trị viêm phổi, hen suyễn và các bệnh lý hô hấp khác.'),
(7, 'Khám và Điều trị Cao Huyết Áp', 1, 400000.00, 'Chẩn đoán, theo dõi và đưa ra phác đồ điều trị cho bệnh nhân tăng huyết áp.'),
(8, 'Kiểm tra chức năng Gan, Thận', 1, 700000.00, 'Bộ xét nghiệm chuyên sâu đánh giá chức năng lọc và chuyển hóa của gan, thận.'),
(9, 'Khám bệnh Tiêu hóa và Nội soi', 1, 1200000.00, 'Khám tổng quát bệnh lý dạ dày, đại tràng và đặt lịch nội soi tiêu hóa.'),
(10, 'Điện tâm đồ gắng sức (Stress Test)', 2, 1500000.00, 'Đánh giá khả năng hoạt động của tim dưới áp lực để phát hiện bệnh động mạch vành.'),
(11, 'Theo dõi Holter ECG 24 giờ', 2, 2200000.00, 'Ghi lại hoạt động điện tim liên tục để chẩn đoán rối loạn nhịp tim không thường xuyên.'),
(12, 'Tiêm chủng mở rộng và tư vấn vắc xin', 3, 300000.00, 'Tư vấn lịch tiêm chủng phù hợp và thực hiện các mũi tiêm theo quy định.'),
(13, 'Khám và Điều trị Sốt siêu vi, Tay chân miệng', 3, 480000.00, 'Chẩn đoán chính xác và theo dõi điều trị các bệnh truyền nhiễm thường gặp ở trẻ.'),
(14, 'Lấy nhân mụn Y khoa (Trọn gói)', 4, 350000.00, 'Quy trình lấy nhân mụn chuẩn y khoa, đảm bảo vô trùng và hạn chế tối đa sẹo.'),
(15, 'Điều trị Tăng sắc tố (Nám, Tàn nhang)', 4, 1500000.00, 'Sử dụng công nghệ tiên tiến hoặc phác đồ thuốc để làm mờ và loại bỏ nám, tàn nhang.'),
(16, 'Siêu âm 4D Thai kỳ', 5, 850000.00, 'Siêu âm chi tiết hình thái thai nhi, tầm soát dị tật và ghi lại hình ảnh 4D.'),
(17, 'Khám và Điều trị Viêm nhiễm Phụ khoa', 5, 400000.00, 'Chẩn đoán và điều trị các bệnh lý viêm nhiễm âm đạo, cổ tử cung.'),
(18, 'Chụp X-quang và Đánh giá tổn thương xương', 6, 650000.00, 'Thực hiện chụp X-quang kỹ thuật số và tư vấn chẩn đoán gãy xương, rạn xương.'),
(19, 'Tiêm huyết tương giàu tiểu cầu (PRP) Khớp gối', 6, 4500000.00, 'Điều trị tái tạo sụn khớp và giảm đau khớp gối bằng phương pháp tiêm PRP.'),
(20, 'Đo Thị lực và Kiểm tra mắt toàn diện', 7, 300000.00, 'Kiểm tra thị lực, khúc xạ, và khám tổng quát các bệnh lý về mắt.'),
(21, 'Phẫu thuật Phaco điều trị Đục thủy tinh thể', 7, 18000000.00, 'Phẫu thuật thay thủy tinh thể bằng phương pháp Phaco an toàn và hiệu quả.'),
(22, 'Tư vấn và Phẫu thuật Lasik', 7, 500000.00, 'Tư vấn chuyên sâu về điều kiện phẫu thuật Lasik và Relex Smile cho bệnh nhân cận thị.'),
(23, 'Nội soi Tai Mũi Họng Chẩn đoán', 8, 400000.00, 'Sử dụng thiết bị nội soi hiện đại để chẩn đoán viêm xoang, viêm họng mãn tính, amidan.'),
(24, 'Khám và Điều trị Viêm tai giữa', 8, 380000.00, 'Chẩn đoán và điều trị các vấn đề viêm tai giữa ở trẻ em và người lớn.'),
(25, 'Lấy cao răng và Đánh bóng', 9, 250000.00, 'Vệ sinh răng miệng chuyên nghiệp, loại bỏ mảng bám và cao răng.'),
(26, 'Trám răng thẩm mỹ bằng Composite', 9, 500000.00, 'Khắc phục sứt mẻ, sâu răng bằng vật liệu trám thẩm mỹ.'),
(27, 'Chụp X-quang toàn hàm (Panoramic)', 9, 450000.00, 'Chụp X-quang tổng thể để đánh giá tình trạng răng, xương hàm và các vấn đề ẩn.'),
(28, 'Khám sức khỏe tổng quát (Gói tiêu chuẩn)', 10, 1500000.00, 'Kiểm tra tổng thể, xét nghiệm máu/nước tiểu cơ bản, tư vấn sức khỏe.'),
(29, 'Cấp cứu 24/7 (Phí dịch vụ ban đầu)', 10, 800000.00, 'Đánh giá ban đầu và xử lý ổn định tình trạng cấp cứu.'),
(30, 'Gói khám tiền hôn nhân', 10, 3500000.00, 'Sàng lọc các bệnh lây truyền, đánh giá sức khỏe sinh sản cho cặp đôi.'),
(31, 'Siêu âm hệ Tiết niệu và Chẩn đoán', 11, 600000.00, 'Siêu âm bàng quang, thận, niệu quản để phát hiện sỏi hoặc khối u.'),
(32, 'Khám và Điều trị Sỏi thận/niệu quản', 11, 750000.00, 'Đánh giá phương án điều trị (nội khoa hoặc tán sỏi) cho bệnh nhân sỏi.'),
(33, 'Khám chuyên sâu về Phì đại tuyến tiền liệt', 11, 550000.00, 'Chẩn đoán và theo dõi điều trị các vấn đề liên quan đến tuyến tiền liệt ở nam giới.'),
(34, 'Khám và Điều trị Đau đầu/Đau nửa đầu', 12, 500000.00, 'Chẩn đoán loại đau đầu và đưa ra phác đồ điều trị, tư vấn lối sống.'),
(35, 'Đo điện não đồ (EEG)', 12, 900000.00, 'Ghi lại hoạt động điện não để chẩn đoán động kinh, rối loạn giấc ngủ.'),
(36, 'Tư vấn và Sàng lọc Đột quỵ', 12, 700000.00, 'Đánh giá nguy cơ đột quỵ và hướng dẫn các biện pháp phòng ngừa.'),
(37, 'Tư vấn Tâm lý cá nhân (50 phút)', 13, 1200000.00, 'Phiên tư vấn riêng với nhà tâm lý học về stress, lo âu, trầm cảm.'),
(38, 'Khám và Chẩn đoán Rối loạn Lo âu/Trầm cảm', 13, 800000.00, 'Bác sĩ Tâm thần chẩn đoán và đưa ra phác đồ điều trị y khoa.'),
(39, 'Liệu pháp Nhận thức Hành vi (CBT)', 13, 1500000.00, 'Sử dụng phương pháp CBT để điều trị các vấn đề tâm lý.'),
(40, 'Khám và Chỉ định Châm cứu/Bấm huyệt', 14, 400000.00, 'Khám tổng quát theo phương pháp YHCT và chỉ định liệu trình châm cứu, bấm huyệt.'),
(41, 'Phục hồi chức năng sau tai biến', 14, 600000.00, 'Xây dựng chương trình phục hồi vận động, ngôn ngữ cho bệnh nhân sau tai biến.'),
(42, 'Massage trị liệu toàn thân', 14, 500000.00, 'Sử dụng kỹ thuật massage y khoa để giảm đau cơ, xương khớp.'),
(43, 'Tư vấn và Xét nghiệm Mỡ máu', 1, 550000.00, 'Tư vấn chế độ ăn uống và xét nghiệm chuyên sâu về cholesterol, triglycerides.'),
(44, 'Khám bệnh Đái tháo đường và theo dõi chỉ số', 1, 450000.00, 'Chẩn đoán và quản lý bệnh tiểu đường type 1 và type 2.'),
(45, 'Chẩn đoán và Điều trị Viêm loét dạ dày tá tràng', 1, 400000.00, 'Khám và kê đơn điều trị viêm, loét, trào ngược dạ dày thực quản.'),
(46, 'Đánh giá nguy cơ Tim mạch toàn diện', 2, 950000.00, 'Sử dụng các chỉ số lâm sàng để xác định nguy cơ nhồi máu cơ tim, đột quỵ.'),
(47, 'Tư vấn Phục hồi chức năng tim sau can thiệp', 2, 700000.00, 'Xây dựng chương trình tập luyện và dinh dưỡng cho bệnh nhân sau phẫu thuật tim.'),
(48, 'Khám và tư vấn Suy dinh dưỡng/Biếng ăn', 3, 400000.00, 'Chẩn đoán nguyên nhân và đưa ra giải pháp dinh dưỡng cho trẻ biếng ăn, nhẹ cân.'),
(49, 'Khám sàng lọc và tư vấn Tự kỷ/Tăng động', 3, 600000.00, 'Đánh giá phát triển hành vi, tâm lý và tư vấn can thiệp sớm.'),
(50, 'Điều trị viêm da tiếp xúc và Chàm', 4, 450000.00, 'Xác định tác nhân gây viêm và điều trị các bệnh lý dị ứng mãn tính.'),
(51, 'Soi da kỹ thuật số và phân tích', 4, 350000.00, 'Sử dụng máy móc hiện đại để phân tích cấu trúc da, độ ẩm và sắc tố.'),
(52, 'Đốt điện/Laser loại bỏ mụn thịt, nốt ruồi', 4, 800000.00, 'Thực hiện thủ thuật ngoại khoa nhỏ để loại bỏ các tổn thương da lành tính.'),
(53, 'Tư vấn và Cấy/Tháo que tránh thai', 5, 1800000.00, 'Thực hiện thủ thuật cấy hoặc tháo que tránh thai dưới sự giám sát y tế.'),
(54, 'Khám và Điều trị Rối loạn kinh nguyệt', 5, 450000.00, 'Chẩn đoán các vấn đề về chu kỳ kinh nguyệt, đau bụng kinh.'),
(55, 'Chẩn đoán loãng xương bằng máy DEXA', 6, 900000.00, 'Đo mật độ xương để đánh giá nguy cơ loãng xương.'),
(56, 'Tiêm nội khớp giảm đau (Hyaluronic Acid)', 6, 2500000.00, 'Thực hiện tiêm chất nhờn nhân tạo vào khớp để giảm đau và cải thiện vận động.'),
(57, 'Khám và điều trị Khô mắt/Viêm kết mạc', 7, 300000.00, 'Chẩn đoán nguyên nhân và kê đơn thuốc nhỏ mắt chuyên dụng.'),
(58, 'Đo nhãn áp và Khám đáy mắt', 7, 450000.00, 'Tầm soát Glaucoma (Thiên đầu thống) và các bệnh lý võng mạc.'),
(59, 'Nội soi tầm soát Ung thư Vòm họng', 8, 550000.00, 'Thực hiện nội soi kỹ thuật cao để phát hiện sớm các tổn thương tiền ung thư.'),
(60, 'Đo thính lực và Tư vấn máy trợ thính', 8, 650000.00, 'Đánh giá mức độ nghe kém và tư vấn giải pháp hỗ trợ thính giác.'),
(61, 'Tẩy trắng răng bằng đèn Laser', 9, 3000000.00, 'Quy trình tẩy trắng chuyên nghiệp, mang lại hàm răng trắng sáng tức thì.'),
(62, 'Nhổ răng khôn (Răng số 8) phức tạp', 9, 1200000.00, 'Thủ thuật nhổ răng khôn mọc lệch, ngầm dưới sự gây tê cục bộ.'),
(63, 'Xét nghiệm máu tổng quát (24 chỉ số)', 10, 450000.00, 'Đánh giá chức năng cơ bản của các cơ quan và tầm soát thiếu máu.'),
(64, 'Siêu âm Bụng Tổng Quát', 10, 400000.00, 'Siêu âm gan, mật, tụy, lách, thận để phát hiện các bất thường về hình thái.'),
(65, 'Chụp X-quang Phổi Thẳng', 10, 350000.00, 'Chụp X-quang để kiểm tra phổi, tim và lồng ngực.'),
(66, 'Đo chức năng hô hấp', 10, 500000.00, 'Đánh giá dung tích phổi và khả năng lưu thông khí để chẩn đoán hen, COPD.'),
(67, 'Nội soi Dạ dày không đau (Gây mê nhẹ)', 1, 2500000.00, 'Nội soi dạ dày, tá tràng bằng phương pháp gây mê nhẹ, không đau.'),
(68, 'Test hơi thở C14 tìm vi khuẩn HP', 1, 700000.00, 'Phương pháp chẩn đoán không xâm lấn để phát hiện vi khuẩn Helicobacter Pylori.'),
(69, 'Phân tích nước tiểu 10 thông số', 11, 150000.00, 'Kiểm tra đường, protein, hồng cầu, bạch cầu trong nước tiểu.'),
(70, 'Điều trị và phòng ngừa Nhiễm trùng tiểu tái phát', 11, 400000.00, 'Xây dựng phác đồ điều trị và tư vấn phòng ngừa nhiễm trùng đường tiết niệu.'),
(71, 'Chụp MRI não (Có tiêm thuốc cản quang)', 12, 6000000.00, 'Hình ảnh cộng hưởng từ chi tiết để chẩn đoán khối u, đa xơ cứng, đột quỵ.'),
(72, 'Khám và tư vấn Rối loạn giấc ngủ', 12, 550000.00, 'Chẩn đoán nguyên nhân mất ngủ, ngưng thở khi ngủ và đưa ra giải pháp.'),
(73, 'Tư vấn Tâm lý Hôn nhân & Gia đình', 13, 1800000.00, 'Phiên tư vấn cho cặp đôi hoặc gia đình để giải quyết xung đột, cải thiện mối quan hệ.'),
(74, 'Đánh giá sức khỏe tâm thần toàn diện (DSM-5)', 13, 900000.00, 'Sử dụng công cụ tiêu chuẩn để chẩn đoán các vấn đề sức khỏe tâm thần.');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Chỉ mục cho bảng `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Chỉ mục cho bảng `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `license_number` (`license_number`),
  ADD UNIQUE KEY `login_id` (`login_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Chỉ mục cho bảng `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`medicine_id`);

--
-- Chỉ mục cho bảng `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `prescription_details`
--
ALTER TABLE `prescription_details`
  ADD PRIMARY KEY (`prescription_id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Chỉ mục cho bảng `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Chỉ mục cho bảng `receptionists`
--
ALTER TABLE `receptionists`
  ADD PRIMARY KEY (`receptionist_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT cho bảng `medicines`
--
ALTER TABLE `medicines`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `prescription_details`
--
ALTER TABLE `prescription_details`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `receptionists`
--
ALTER TABLE `receptionists`
  MODIFY `receptionist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`);

--
-- Các ràng buộc cho bảng `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Các ràng buộc cho bảng `prescription_details`
--
ALTER TABLE `prescription_details`
  ADD CONSTRAINT `prescription_details_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescription_details_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`);

--
-- Các ràng buộc cho bảng `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`);

--
-- Các ràng buộc cho bảng `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
