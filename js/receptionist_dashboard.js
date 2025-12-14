window.calculateFee = function () {
  const selectService = document.getElementById("service_id_edit");
  const paidAmountInput = document.getElementById("paid_amount_edit");
  const totalDisplay = document.getElementById("total_to_pay_display");
  const bhytRateInput = document.getElementById("bhyt_rate_edit");
  const saveButton = document.getElementById("btn-save-update");

  if (!selectService || !totalDisplay) return;

  const BHYT_RATE = parseFloat(bhytRateInput.value || 0.0);
  const formatVND = (n) =>
    new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(n);

  function run() {
    const option = selectService.options[selectService.selectedIndex];
    const price = parseFloat(option.getAttribute("data-price")) || 0;
    const serviceId = selectService.value;

    let finalPrice = price;
    if (BHYT_RATE > 0) finalPrice = price * (1.0 - BHYT_RATE);

    totalDisplay.value = formatVND(finalPrice);
    paidAmountInput.value = finalPrice;

    // LOGIC ĐỔI TÊN NÚT
    if (saveButton) {
      if (serviceId && serviceId !== "0") {
        saveButton.innerHTML =
          '<i class="fas fa-paper-plane me-2"></i> Gửi cho Bác sĩ';
        saveButton.classList.remove("btn-primary");
        saveButton.classList.add("btn-success");
      } else {
        saveButton.innerHTML = "Lưu Thay Đổi";
        saveButton.classList.add("btn-primary");
        saveButton.classList.remove("btn-success");
      }
    }
  }
  selectService.addEventListener("change", run);
  run();
};

function openPatientProfile(patientId) {
  const content = document.getElementById("patientProfileContent");
  content.innerHTML = '<div class="text-center p-4">Đang tải...</div>';
  fetch(`receptionist_dashboard.php?action=load_profile&id=${patientId}`)
    .then((res) => res.text())
    .then((html) => {
      content.innerHTML = html;
      window.calculateFee();
    });
}

document.addEventListener("DOMContentLoaded", () => {
  const cancelModal = document.getElementById("cancelModal");
  if (cancelModal) {
    cancelModal.addEventListener("show.bs.modal", (e) => {
      const btn = e.relatedTarget;
      document.getElementById("modal-appointment-id").value = btn.getAttribute(
        "data-appointment-id"
      );
      document.getElementById("modal-patient-name").textContent =
        btn.getAttribute("data-patient-name");
    });
  }

  const profileModal = document.getElementById("patientProfileModal");
  if (profileModal) {
    profileModal.addEventListener("show.bs.modal", (e) => {
      const btn = e.relatedTarget;
      openPatientProfile(btn.getAttribute("data-patient-id"));
    });
  }
});

// PUSHER
Pusher.logToConsole = true;
var pusher = new Pusher("18b40fb67053da5ad353", { cluster: "ap1" });
var channel = pusher.subscribe("phong-kham");

channel.bind("don-hang-moi", (data) => showToast(data.message, "success"));
channel.bind("huy-lich", (data) => showToast(data.message, "danger"));

function showToast(msg, type) {
  let bg = type === "danger" ? "#dc3545" : "#198754";
  let div = document.createElement("div");
  div.style.cssText = `position:fixed; top:20px; right:20px; background:${bg}; color:#fff; padding:15px; border-radius:5px; z-index:9999; box-shadow:0 4px 10px rgba(0,0,0,0.2);`;
  div.innerHTML = `<i class="fas fa-bell"></i> ${msg}`;
  document.body.appendChild(div);
  setTimeout(() => {
    div.remove();
    location.reload();
  }, 3000);
}
