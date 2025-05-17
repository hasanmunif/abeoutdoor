document.addEventListener("DOMContentLoaded", function () {
    // File upload handling
    const fileInput = document.getElementById("Proof");
    const fileBtn = document.getElementById("Upload-btn");

    if (fileInput && fileBtn) {
        fileInput.addEventListener("change", function () {
            const file = this.files[0];

            if (file) {
                // Validasi tipe file
                const validTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/jpg",
                    "image/gif",
                ];
                if (!validTypes.includes(file.type)) {
                    Swal.fire({
                        title: "Error!",
                        text: "Hanya file gambar (JPG, PNG, GIF) yang diperbolehkan",
                        icon: "error",
                        confirmButtonColor: "#FCCF2F",
                    });
                    this.value = ""; // Reset input
                    fileBtn.innerText = "Add an attachment";
                    fileBtn.classList.remove("font-semibold");
                    return;
                }

                // Validasi ukuran (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        title: "Error!",
                        text: "Ukuran file maksimal 5MB",
                        icon: "error",
                        confirmButtonColor: "#FCCF2F",
                    });
                    this.value = ""; // Reset input
                    fileBtn.innerText = "Add an attachment";
                    fileBtn.classList.remove("font-semibold");
                    return;
                }

                fileBtn.innerText = file.name;
                fileBtn.classList.add("font-semibold");
            } else {
                fileBtn.innerText = "Add an attachment";
                fileBtn.classList.remove("font-semibold");
            }
        });
    }

    // Script untuk menangani tampilan alamat saat memilih metode pengiriman
    const deliveryRadios = document.querySelectorAll(
        'input[name="delivery_type"]'
    );
    const addressContainer = document.getElementById("address-container");
    const addressField = document.getElementById("address");

    if (deliveryRadios.length > 0 && addressContainer && addressField) {
        // Fungsi untuk mengatur visibilitas field alamat
        function toggleAddressField() {
            if (
                document.querySelector('input[name="delivery_type"]:checked')
                    .value === "delivery"
            ) {
                addressContainer.classList.remove("hidden");
                addressField.required = true;
            } else {
                addressContainer.classList.add("hidden");
                addressField.required = false;
                addressField.value = "Pickup di toko";
            }
        }

        // Set nilai awal
        toggleAddressField();

        // Event listener untuk perubahan radio button
        deliveryRadios.forEach((radio) => {
            radio.addEventListener("change", toggleAddressField);
        });
    }

    // Payment Method Handling
    const paymentOptions = document.querySelectorAll(
        'input[name="payment_method"]'
    );
    const manualPayment = document.getElementById("Manual-payment");
    const midtransPayment = document.getElementById("Midtrans-payment");
    const proofUpload = document.getElementById("Proof");
    const manualConfirm = document.getElementById("manual_confirm");
    const midtransConfirm = document.getElementById("midtrans_confirm");

    if (paymentOptions.length > 0 && manualPayment && midtransPayment) {
        // Fungsi untuk mengatur tampilan metode pembayaran
        function togglePaymentMethod() {
            const selectedMethod = document.querySelector(
                'input[name="payment_method"]:checked'
            ).value;

            if (selectedMethod === "manual") {
                manualPayment.classList.remove("hidden");
                midtransPayment.classList.add("hidden");

                // Update atribut required
                if (proofUpload) proofUpload.required = true;
                if (manualConfirm) manualConfirm.required = true;
                if (midtransConfirm) midtransConfirm.required = false;
            } else {
                manualPayment.classList.add("hidden");
                midtransPayment.classList.remove("hidden");

                // Update atribut required
                if (proofUpload) proofUpload.required = false;
                if (manualConfirm) manualConfirm.required = false;
                if (midtransConfirm) midtransConfirm.required = true;
            }

            // Update visual selection
            document
                .querySelectorAll(".payment-method-option")
                .forEach((option) => {
                    option.classList.remove("ring-2", "ring-[#FCCF2F]");
                    const check = option.querySelector(".payment-check");
                    if (check) check.classList.add("opacity-0");
                });

            const selected = document
                .querySelector(
                    `input[name="payment_method"][value="${selectedMethod}"]`
                )
                .closest(".payment-method-option");
            if (selected) {
                selected.classList.add("ring-2", "ring-[#FCCF2F]");
                const check = selected.querySelector(".payment-check");
                if (check) check.classList.remove("opacity-0");
            }
        }

        // Set default state
        togglePaymentMethod();

        // Add event listeners to payment method radios
        paymentOptions.forEach((option) => {
            option.addEventListener("change", togglePaymentMethod);
        });
    }

    // Form submission handling
    const checkoutForm = document.querySelector("form");
    if (checkoutForm) {
        checkoutForm.addEventListener("submit", function (e) {
            const selectedMethod = document.querySelector(
                'input[name="payment_method"]:checked'
            )?.value;

            if (selectedMethod === "manual") {
                // Periksa apakah bukti pembayaran sudah diupload
                const proofFile = document.getElementById("Proof").files[0];
                if (!proofFile) {
                    e.preventDefault();
                    Swal.fire({
                        title: "Perhatian!",
                        text: "Silakan upload bukti transfer terlebih dahulu",
                        icon: "warning",
                        confirmButtonColor: "#FCCF2F",
                    });
                    return false;
                }

                // Periksa juga checkbox konfirmasi
                const manualConfirm = document.getElementById("manual_confirm");
                if (manualConfirm && !manualConfirm.checked) {
                    e.preventDefault();
                    Swal.fire({
                        title: "Perhatian!",
                        text: "Harap setujui bahwa Anda telah melakukan transfer",
                        icon: "warning",
                        confirmButtonColor: "#FCCF2F",
                    });
                    return false;
                }
            }

            if (selectedMethod === "midtrans") {
                e.preventDefault();
                console.log("Midtrans payment method selected");

                // Validasi checkbox
                if (midtransConfirm && !midtransConfirm.checked) {
                    Swal.fire({
                        title: "Perhatian!",
                        text: "Harap setujui persyaratan pembayaran Midtrans",
                        icon: "warning",
                        confirmButtonColor: "#FCCF2F",
                    });
                    return;
                }

                // Tampilkan loading
                const paymentButton = document.getElementById("payment-button");
                if (paymentButton) {
                    paymentButton.disabled = true;
                    paymentButton.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    `;
                }

                // Kirim data untuk mendapatkan snap token
                const formData = new FormData(checkoutForm);
                formData.append("_token", window.csrfToken);

                console.log(
                    "Sending request to Midtrans endpoint:",
                    window.routes.checkout.midtrans
                );

                fetch(window.routes.checkout.midtrans, {
                    method: "POST",
                    body: formData,
                })
                    .then((response) => {
                        console.log("Response status:", response.status);
                        if (!response.ok) {
                            throw new Error(
                                `HTTP error! Status: ${response.status}`
                            );
                        }
                        return response.json();
                    })
                    .then((data) => {
                        console.log("Response data:", data);
                        if (data.success) {
                            // Check if snap is available
                            if (typeof window.snap === "undefined") {
                                Swal.fire({
                                    title: "Error!",
                                    text: "Midtrans Snap tidak tersedia. Mohon refresh halaman atau gunakan metode pembayaran lain.",
                                    icon: "error",
                                    confirmButtonColor: "#FCCF2F",
                                });
                                if (paymentButton) {
                                    paymentButton.disabled = false;
                                    paymentButton.innerHTML =
                                        "Proses Pembayaran";
                                }
                                return;
                            }

                            // Buka snap popup
                            console.log(
                                "Opening snap with token:",
                                data.snap_token
                            );
                            window.snap.pay(data.snap_token, {
                                onSuccess: function (result) {
                                    console.log("Payment success:", result);
                                    window.location.href = data.success_url;
                                },
                                onPending: function (result) {
                                    console.log("Payment pending:", result);
                                    window.location.href = data.pending_url;
                                },
                                onError: function (result) {
                                    console.error("Payment error:", result);
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Pembayaran gagal",
                                        icon: "error",
                                        confirmButtonColor: "#FCCF2F",
                                    });
                                    if (paymentButton) {
                                        paymentButton.disabled = false;
                                        paymentButton.innerHTML =
                                            "Proses Pembayaran";
                                    }
                                },
                                onClose: function () {
                                    console.log("Snap popup closed");
                                    Swal.fire({
                                        title: "Informasi",
                                        text: "Anda menutup halaman pembayaran sebelum menyelesaikan transaksi",
                                        icon: "info",
                                        confirmButtonColor: "#FCCF2F",
                                    });
                                    if (paymentButton) {
                                        paymentButton.disabled = false;
                                        paymentButton.innerHTML =
                                            "Proses Pembayaran";
                                    }
                                },
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: data.message || "Terjadi kesalahan",
                                icon: "error",
                                confirmButtonColor: "#FCCF2F",
                            });
                            if (paymentButton) {
                                paymentButton.disabled = false;
                                paymentButton.innerHTML = "Proses Pembayaran";
                            }
                        }
                    })
                    .catch((error) => {
                        console.error("Error:", error);
                        Swal.fire({
                            title: "Error!",
                            text:
                                "Terjadi kesalahan saat memproses pembayaran: " +
                                error.message,
                            icon: "error",
                            confirmButtonColor: "#FCCF2F",
                        });
                        if (paymentButton) {
                            paymentButton.disabled = false;
                            paymentButton.innerHTML = "Proses Pembayaran";
                        }
                    });
            }
        });
    }

    // Fungsi untuk menangani perubahan kuantitas
    const quantityControls = document.querySelectorAll(".quantity-control");
    quantityControls.forEach((control) => {
        const productId = control.dataset.productId;
        const decrementBtn = control.querySelector(".decrement-btn");
        const incrementBtn = control.querySelector(".increment-btn");
        const quantityDisplay = control.querySelector(".quantity-display");

        decrementBtn.addEventListener("click", function () {
            updateQuantity(
                productId,
                parseInt(quantityDisplay.textContent) - 1
            );
        });

        incrementBtn.addEventListener("click", function () {
            updateQuantity(
                productId,
                parseInt(quantityDisplay.textContent) + 1
            );
        });
    });

    // Fungsi untuk menangani perubahan durasi
    const durationControls = document.querySelectorAll(".duration-control");
    durationControls.forEach((control) => {
        const productId = control.dataset.productId;
        const decrementBtn = control.querySelector(".duration-decrement-btn");
        const incrementBtn = control.querySelector(".duration-increment-btn");
        const durationValue = control.querySelector(".duration-value");

        decrementBtn.addEventListener("click", function () {
            const currentDays = parseInt(durationValue.textContent);
            if (currentDays > 3) {
                updateDuration(productId, currentDays - 3);
            }
        });

        incrementBtn.addEventListener("click", function () {
            const currentDays = parseInt(durationValue.textContent);
            if (currentDays < 30) {
                updateDuration(productId, currentDays + 3);
            }
        });
    });

    // Fungsi untuk menangani tombol hapus item
    const removeButtons = document.querySelectorAll(".remove-item-btn");
    removeButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const productId = this.dataset.productId;
            removeCheckoutItem(productId);
        });
    });

    // Fungsi untuk update kuantitas item
    function updateQuantity(productId, newQuantity) {
        if (newQuantity < 1) return;

        // Tampilkan loading state
        const checkoutItem = document.getElementById(
            `checkout-item-${productId}`
        );
        checkoutItem.classList.add("opacity-50");

        fetch(`/cart/update/${productId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                quantity: newQuantity,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Update UI
                    updateCheckoutUI(data);
                    showTinyToast("Jumlah berhasil diperbarui");
                } else {
                    showSweetAlert(
                        "Error",
                        data.message || "Terjadi kesalahan",
                        "error"
                    );
                }
                checkoutItem.classList.remove("opacity-50");
            })
            .catch((error) => {
                console.error("Error updating quantity:", error);
                checkoutItem.classList.remove("opacity-50");
                showSweetAlert(
                    "Error",
                    "Terjadi kesalahan saat memperbarui jumlah",
                    "error"
                );
            });
    }

    // Fungsi untuk update durasi
    function updateDuration(productId, days) {
        // Tampilkan loading state
        const checkoutItem = document.getElementById(
            `checkout-item-${productId}`
        );
        checkoutItem.classList.add("opacity-50");

        fetch(`/cart/update/${productId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                days: days,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Update UI
                    updateCheckoutUI(data);

                    // Tambahkan efek visual untuk feedback
                    checkoutItem.classList.add(
                        "transition-all",
                        "duration-300"
                    );
                    checkoutItem.style.boxShadow = "0 0 0 2px #FCCF2F";
                    setTimeout(() => {
                        checkoutItem.style.boxShadow = "";
                    }, 800);

                    showTinyToast("Durasi berhasil diperbarui");
                } else {
                    showSweetAlert(
                        "Error",
                        data.message || "Terjadi kesalahan",
                        "error"
                    );
                }
                checkoutItem.classList.remove("opacity-50");
            })
            .catch((error) => {
                console.error("Error updating duration:", error);
                checkoutItem.classList.remove("opacity-50");
                showSweetAlert(
                    "Error",
                    "Terjadi kesalahan saat memperbarui durasi",
                    "error"
                );
            });
    }

    // Fungsi untuk menghapus item
    function removeCheckoutItem(productId) {
        Swal.fire({
            title: "Konfirmasi",
            text: "Apakah Anda yakin ingin menghapus item ini dari checkout?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#FCCF2F",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading state
                const checkoutItem = document.getElementById(
                    `checkout-item-${productId}`
                );
                checkoutItem.classList.add("opacity-50");

                fetch(`/checkout/remove/${productId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            // Animasi menghapus item
                            checkoutItem.style.height = `${checkoutItem.offsetHeight}px`;
                            checkoutItem.style.overflow = "hidden";

                            setTimeout(() => {
                                checkoutItem.style.height = "0";
                                checkoutItem.style.padding = "0";
                                checkoutItem.style.margin = "0";
                                checkoutItem.style.opacity = "0";
                                checkoutItem.style.transition =
                                    "all 0.3s ease-out";

                                setTimeout(() => {
                                    checkoutItem.remove();

                                    // Update total
                                    document.getElementById(
                                        "checkout-total"
                                    ).textContent = `Rp ${new Intl.NumberFormat(
                                        "id-ID"
                                    ).format(data.total)}`;

                                    // Update judul section
                                    const productListTitle =
                                        document.querySelector(
                                            "#Product-list h2"
                                        );
                                    if (productListTitle) {
                                        productListTitle.textContent = `Produk (${data.itemCount})`;
                                    }

                                    // Jika tidak ada item lagi, redirect ke cart
                                    if (data.itemCount === 0) {
                                        Swal.fire({
                                            title: "Checkout Kosong",
                                            text: "Tidak ada item tersisa dalam checkout. Kembali ke keranjang.",
                                            icon: "info",
                                            confirmButtonColor: "#FCCF2F",
                                        }).then(() => {
                                            window.location.href =
                                                window.routes.checkout.cart;
                                        });
                                    } else {
                                        showSweetAlert(
                                            "Berhasil!",
                                            "Item telah dihapus dari checkout",
                                            "success"
                                        );
                                    }
                                }, 300);
                            }, 10);
                        } else {
                            showSweetAlert(
                                "Error",
                                data.message || "Terjadi kesalahan",
                                "error"
                            );
                            checkoutItem.classList.remove("opacity-50");
                        }
                    })
                    .catch((error) => {
                        console.error("Error removing item:", error);
                        checkoutItem.classList.remove("opacity-50");
                        showSweetAlert(
                            "Error",
                            "Terjadi kesalahan saat menghapus item",
                            "error"
                        );
                    });
            }
        });
    }

    // Fungsi untuk memperbarui UI checkout
    function updateCheckoutUI(data) {
        if (data.item) {
            const productId = data.item.id;

            // Update quantity display
            const quantityDisplay = document.querySelector(
                `#checkout-item-${productId} .quantity-display`
            );
            if (quantityDisplay) {
                quantityDisplay.textContent = data.item.quantity;
            }

            // Update hidden input quantity
            const quantityInput = document.querySelector(
                `#checkout-item-${productId} .quantity-input`
            );
            if (quantityInput) {
                quantityInput.value = data.item.quantity;
            }

            // Update duration display
            const durationDisplay = document.querySelector(
                `#checkout-item-${productId} .duration-display`
            );
            if (durationDisplay) {
                durationDisplay.textContent = `${data.item.days} hari`;
            }

            // Update hidden input duration
            const durationInput = document.querySelector(
                `#checkout-item-${productId} .duration-input`
            );
            if (durationInput) {
                durationInput.value = data.item.days;
            }

            // Update duration value
            const durationValue = document.querySelector(
                `#checkout-item-${productId} .duration-value`
            );
            if (durationValue) {
                durationValue.textContent = `${data.item.days} hari`;
            }

            // Update subtotal
            const subtotalDisplay = document.querySelector(
                `#checkout-item-${productId} .subtotal-display`
            );
            if (subtotalDisplay) {
                subtotalDisplay.textContent = `Rp ${new Intl.NumberFormat(
                    "id-ID"
                ).format(data.item.subtotal)}`;
            }

            // Update estimasi harga
            const priceEstimation = document.querySelector(
                `#checkout-item-${productId} .price-estimation`
            );
            if (priceEstimation) {
                const periods = Math.ceil(data.item.days / 3);
                const productPrice =
                    data.item.subtotal / data.item.quantity / periods;

                priceEstimation.textContent = `Rp ${new Intl.NumberFormat(
                    "id-ID"
                ).format(periods * productPrice)}`;

                // Update teks penjelasan
                const explanation = priceEstimation
                    .closest(".bg-gray-50")
                    .querySelector(".text-xs.text-gray-500");
                if (explanation) {
                    explanation.textContent = `${periods} periode × Rp ${new Intl.NumberFormat(
                        "id-ID"
                    ).format(productPrice)}`;
                }
            }

            // Update status tombol
            const decrementBtn = document.querySelector(
                `#checkout-item-${productId} .duration-decrement-btn`
            );
            if (decrementBtn) {
                decrementBtn.disabled = data.item.days <= 3;
            }

            const incrementBtn = document.querySelector(
                `#checkout-item-${productId} .duration-increment-btn`
            );
            if (incrementBtn) {
                incrementBtn.disabled = data.item.days >= 30;
            }
        }

        // Update total checkout
        if (data.total !== undefined) {
            // Update subtotal
            const subtotal = data.total;
            const subtotalElement =
                document.getElementById("checkout-subtotal");
            if (subtotalElement) {
                subtotalElement.textContent = `Rp ${new Intl.NumberFormat(
                    "id-ID"
                ).format(subtotal)}`;
            }

            // Langsung update grand total dengan subtotal:
            document.getElementById("checkout-grand-total").textContent =
                formatRupiah(subtotal);
        }
    }

    // Fungsi untuk menampilkan SweetAlert
    function showSweetAlert(title, text, icon) {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            toast: false,
            position: "center",
            showConfirmButton: true,
            confirmButtonColor: "#FCCF2F",
            timer: 3000,
            timerProgressBar: true,
        });
    }

    // Fungsi untuk menampilkan toast notification kecil
    function showTinyToast(message) {
        Swal.fire({
            text: message,
            icon: "success",
            toast: true,
            position: "bottom-end",
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {
                popup: "colored-toast",
            },
            iconColor: "#FCCF2F",
            background: "#333",
            color: "#fff",
        });
    }

    // Tambahkan style untuk toast
    const style = document.createElement("style");
    style.innerHTML = `
        .colored-toast {
            border-radius: 10px !important;
            padding: 8px 16px !important;
            font-size: 14px !important;
        }
    `;
    document.head.appendChild(style);
});
