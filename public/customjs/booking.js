// function to update price and total days
const minus = document.getElementById("Minus");
const plus = document.getElementById("Plus");
const count = document.getElementById("CountDays");
const days = document.getElementById("Days");
const duration = document.getElementById("duration");
const totalPrice = document.getElementById("Total");

const productPrice = document.getElementById("productPrice");
const defaultPrice = productPrice.value;

// Fungsi untuk memastikan jumlah hari selalu kelipatan 3
function adjustDaysToMultipleOfThree(currentDays) {
    // Minimal 3 hari, dan kelipatan 3
    return Math.max(3, Math.ceil(currentDays / 3) * 3);
}

function updateTotalPrice() {
    // Pastikan hari adalah kelipatan 3
    let dayValue = parseInt(days.value);
    let adjustedDays = adjustDaysToMultipleOfThree(dayValue);

    if (dayValue !== adjustedDays) {
        days.value = adjustedDays;
        count.innerText = adjustedDays;
    }

    // Hitung berapa periode 3 hari
    let periods = Math.ceil(adjustedDays / 3);
    let subTotal = periods * defaultPrice;

    totalPrice.innerText = "Rp " + subTotal.toLocaleString("id-ID");
}

minus.addEventListener("click", function () {
    let currentCount = parseInt(count.innerText);
    if (currentCount > 3) {
        // Kurangi 3 hari
        currentCount -= 3;
        count.innerText = currentCount;
        days.value = currentCount;
        duration.value = currentCount;
        updateTotalPrice();
    }
});

plus.addEventListener("click", function () {
    let currentCount = parseInt(count.innerText);
    // Tambah 3 hari
    currentCount += 3;
    count.innerText = currentCount;
    days.value = currentCount;
    duration.value = currentCount;
    updateTotalPrice();
});

// Inisialisasi dengan minimal 3 hari
window.addEventListener("DOMContentLoaded", function () {
    let minDays = 3;
    count.innerText = minDays;
    days.value = minDays;
    duration.value = minDays;
    updateTotalPrice();
});

days.addEventListener("change", function () {
    count.innerText = days.value;
    updateTotalPrice();
});

updateTotalPrice();

// funtion date
const datePicker = document.getElementById("date");
const btnText = document.getElementById("DateTriggerBtn");

datePicker.addEventListener("change", function () {
    if (datePicker.value) {
        btnText.innerText = datePicker.value;
        btnText.classList.add("font-semibold");
    } else {
        btnText.innerText = "Select date";
        btnText.classList.remove("font-semibold");
    }
});

// funtion nav & tabs like bootstrap
document.addEventListener("DOMContentLoaded", function () {
    window.openPage = function (pageName, elmnt) {
        let i, tabcontent, tablinks;

        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.add("hidden");
        }

        tablinks = document.getElementsByClassName("tablink");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active", "ring-2", "ring-[#FCCF2F]");
        }

        document.getElementById(pageName).classList.remove("hidden");
        elmnt.classList.add("active", "ring-2", "ring-[#FCCF2F]");
    };

    // Get the element with id="defaultOpen" and click on it
    document.getElementById("defaultOpen").click();
});

// funtion for changing required atribute
function toggleRequiredOptions() {
    const pickupRadio = document.getElementById("Pickup");
    const deliveryRadio = document.getElementById("Delivery");
    const deliveryType = document.getElementById("deliveryType");
    const storeRadios = document.getElementsByName("store");
    const addressTextarea = document.getElementsByName("address")[0];

    if (pickupRadio.checked) {
        storeRadios.forEach((radio) => {
            radio.required = true;
        });
        // addressTextarea.required = false;
        addressTextarea.value = "Diambil ditoko saja";
        deliveryType.value = "pickup";
    } else if (deliveryRadio.checked) {
        storeRadios.forEach((radio) => {
            radio.required = false;
        });
        // addressTextarea.required = true;
        addressTextarea.value = "";
        deliveryType.value = "home_delivery";
        document.querySelector('input[name="store_id"]').value = 1;
    }
}

// Pastikan store_id terisi sebelum submit
document
    .getElementById("booking-form")
    .addEventListener("submit", function (e) {
        const storeRadios = document.querySelectorAll('input[name="store_id"]');
        let storeSelected = false;

        storeRadios.forEach((radio) => {
            if (radio.checked) {
                document.getElementById("storeId").value = radio.value;
                storeSelected = true;
            }
        });

        if (!storeSelected) {
            e.preventDefault();
            alert("Silakan pilih lokasi pengambilan barang terlebih dahulu");
        }
    });
