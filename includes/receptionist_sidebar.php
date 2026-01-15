<div class="sidebar shadow">
    <div class="brand p-4"><i class="fas fa-hospital-alt me-2"></i> MEDI-CARE</div>
    <nav class="flex-grow-1 px-3">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="receptionist_dashboard.php"
            class="nav-item py-3 <?= $current_page == 'receptionist_dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check me-3"></i>Lịch hẹn & Tiếp đón
        </a>
        <a href="receptionist_patients.php"
            class="nav-item <?= $current_page == 'receptionist_patients.php' ? 'active' : '' ?> py-3">
            <i class="fas fa-users me-3"></i>Hồ sơ Bệnh nhân
        </a>
        <hr class="text-secondary opacity-25">
        <a href="receptionist_patients.php#revenueSection" class="nav-item py-3 opacity-75 small">
            <i class="fas fa-chart-line me-3"></i>Thống kê doanh thu
        </a>
        <a href="receptionist_patients.php#reexamSection" class="nav-item py-3 opacity-75 small">
            <i class="fas fa-phone-volume me-3"></i>Gọi tái khám
        </a>
    </nav>
    <div class="user-profile m-3 p-3 bg-dark rounded-4 shadow-sm">
        <div class="d-flex align-items-center">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($receptionist_name) ?>&background=4361ee&color=fff"
                class="rounded-circle me-3" width="35">
            <div class="flex-grow-1">
                <div class="fw-bold text-white small"
                    style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 80px;">
                    <?= htmlspecialchars($receptionist_name) ?>
                </div>
                <div class="text-secondary" style="font-size: 0.7rem;">Tiếp đón</div>
            </div>
            <a href="logout.php" class="text-danger ms-2"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</div>