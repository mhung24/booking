<?php
require_once 'config/connect.php';

global $pdo;

function generateMonthlySchedule($pdo)
{
    try {
        $pdo->beginTransaction();

        // 1. Lấy danh sách tất cả các khoa
        $sql_depts = "SELECT department_id FROM Departments";
        $stmt_depts = $pdo->query($sql_depts);
        $departments = $stmt_depts->fetchAll(PDO::FETCH_COLUMN);

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');

        foreach ($departments as $dept_id) {
            // Lấy danh sách bác sĩ thuộc khoa này
            $stmt_docs = $pdo->prepare("SELECT doctor_id FROM Doctors WHERE department_id = ?");
            $stmt_docs->execute([$dept_id]);
            $doctors = $stmt_docs->fetchAll(PDO::FETCH_COLUMN);

            $num_docs = count($doctors);
            if ($num_docs == 0)
                continue; // Khoa không có bác sĩ thì bỏ qua

            $current = new DateTime($start_date);
            $end = new DateTime($end_date);

            while ($current <= $end) {
                $work_date = $current->format('Y-m-d');
                $slots = ['Sáng', 'Chiều'];

                foreach ($slots as $slot) {
                    // Kiểm tra xem ca này của khoa này đã được sắp lịch chưa
                    $stmt_check = $pdo->prepare("
                        SELECT COUNT(*) FROM doctor_schedules 
                        WHERE work_date = ? AND slot_name = ? 
                        AND doctor_id IN (SELECT doctor_id FROM Doctors WHERE department_id = ?)
                    ");
                    $stmt_check->execute([$work_date, $slot, $dept_id]);

                    if ($stmt_check->fetchColumn() == 0) {
                        $selected_doctor_id = null;

                        if ($num_docs == 1) {
                            // TRƯỜNG HỢP 1 BÁC SĨ: Auto chọn bác sĩ duy nhất đó
                            $selected_doctor_id = $doctors[0];
                        } else {
                            // TRƯỜNG HỢP >= 2 BÁC SĨ: Bốc ngẫu nhiên
                            $selected_doctor_id = $doctors[array_rand($doctors)];
                        }

                        if ($selected_doctor_id) {
                            $stmt_ins = $pdo->prepare("INSERT INTO doctor_schedules (doctor_id, work_date, slot_name, status) VALUES (?, ?, ?, 'Active')");
                            $stmt_ins->execute([$selected_doctor_id, $work_date, $slot]);
                        }
                    }
                }
                $current->modify('+1 day');
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        return false;
    }
}

// LOGIC TỰ ĐỘNG CHẠY
$first_day_of_month = date('Y-m-01');
$stmt_check_month = $pdo->prepare("SELECT COUNT(*) FROM doctor_schedules WHERE work_date = ?");
$stmt_check_month->execute([$first_day_of_month]);
if ($stmt_check_month->fetchColumn() == 0) {
    generateMonthlySchedule($pdo);
}