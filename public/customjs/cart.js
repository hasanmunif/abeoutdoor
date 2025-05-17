document.addEventListener("DOMContentLoaded", function () {
    // Handle quantity increment/decrement
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

    // Handle duration increment/decrement
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

    // Handle remove item buttons
    const removeButtons = document.querySelectorAll(".remove-item-btn");
    removeButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const productId = this.dataset.productId;
            removeItem(productId);
        });
    });

    // Handle clear cart button
    const clearCartBtn = document.getElementById("clear-cart-btn");
    if (clearCartBtn) {
        clearCartBtn.addEventListener("click", function () {
            clearCart();
        });
    }

    // Tambahkan kode validasi checkout
    const checkoutForm = document.getElementById("checkout-form");
    if (checkoutForm) {
        checkoutForm.addEventListener("submit", function (e) {
            const checkedItems = document.querySelectorAll(
                'input[name="items[]"]:checked'
            );

            if (checkedItems.length === 0) {
                e.preventDefault();

                // Gunakan SweetAlert untuk menampilkan pesan error
                Swal.fire({
                    title: "Pilih Produk",
                    text: "Silakan pilih minimal satu produk untuk checkout",
                    icon: "warning",
                    confirmButtonColor: "#FCCF2F",
                });
                return false;
            }

            // Tambahkan loading state saat form disubmit
            const checkoutButton = document.getElementById("checkout-button");
            if (checkoutButton) {
                checkoutButton.disabled = true;
                checkoutButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                `;
            }

            return true;
        });
    }

    // Function to remove item via AJAX with SweetAlert
    function removeItem(productId) {
        Swal.fire({
            title: "Konfirmasi",
            text: "Apakah Anda yakin ingin menghapus item ini dari keranjang?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#FCCF2F",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                const cartItem = document.getElementById(
                    `cart-item-${productId}`
                );
                cartItem.classList.add("opacity-50");

                fetch(`/cart/remove/${productId}`, {
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
                            // Remove item from UI with animation
                            cartItem.style.height = `${cartItem.offsetHeight}px`;
                            cartItem.style.overflow = "hidden";

                            setTimeout(() => {
                                cartItem.style.height = "0";
                                cartItem.style.padding = "0";
                                cartItem.style.margin = "0";
                                cartItem.style.opacity = "0";
                                cartItem.style.transition = "all 0.3s ease-out";

                                setTimeout(() => {
                                    cartItem.remove();

                                    // Update total and item count
                                    document.getElementById(
                                        "cart-total"
                                    ).textContent = `Rp ${new Intl.NumberFormat(
                                        "id-ID"
                                    ).format(data.total)}`;
                                    document.getElementById(
                                        "cart-count"
                                    ).textContent = `Total (${data.itemCount} produk)`;

                                    // If cart is empty, refresh the page to show empty cart message
                                    if (data.itemCount === 0) {
                                        location.reload();
                                    }

                                    // Show success message
                                    showSweetAlert(
                                        "Berhasil!",
                                        "Item telah dihapus dari keranjang",
                                        "success"
                                    );
                                }, 300);
                            }, 10);
                        } else {
                            showSweetAlert(
                                "Error",
                                data.message || "Terjadi kesalahan",
                                "error"
                            );
                            cartItem.classList.remove("opacity-50");
                        }
                    })
                    .catch((error) => {
                        console.error("Error removing item:", error);
                        cartItem.classList.remove("opacity-50");
                        showSweetAlert(
                            "Error",
                            "Terjadi kesalahan saat menghapus item",
                            "error"
                        );
                    });
            }
        });
    }

    // Function to clear cart via AJAX with SweetAlert
    function clearCart() {
        Swal.fire({
            title: "Konfirmasi",
            text: "Apakah Anda yakin ingin mengosongkan keranjang?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#FCCF2F",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Kosongkan!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Memproses...",
                    text: "Sedang mengosongkan keranjang",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                fetch("/cart/clear", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: "Keranjang telah dikosongkan",
                                icon: "success",
                                confirmButtonColor: "#FCCF2F",
                            }).then(() => {
                                // Refresh page to show empty cart
                                location.reload();
                            });
                        } else {
                            showSweetAlert(
                                "Error",
                                data.message || "Terjadi kesalahan",
                                "error"
                            );
                        }
                    })
                    .catch((error) => {
                        console.error("Error clearing cart:", error);
                        showSweetAlert(
                            "Error",
                            "Terjadi kesalahan saat mengosongkan keranjang",
                            "error"
                        );
                    });
            }
        });
    }

    // Function to update quantity via AJAX
    function updateQuantity(productId, newQuantity) {
        if (newQuantity < 1) return;

        // Show loading state
        const cartItem = document.getElementById(`cart-item-${productId}`);
        cartItem.classList.add("opacity-50");

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
                    updateCartUI(data);
                    // Show tiny toast-like notification
                    showTinyToast("Jumlah berhasil diperbarui");
                } else {
                    showSweetAlert(
                        "Error",
                        data.message || "Terjadi kesalahan",
                        "error"
                    );
                }
                cartItem.classList.remove("opacity-50");
            })
            .catch((error) => {
                console.error("Error updating quantity:", error);
                cartItem.classList.remove("opacity-50");
                showSweetAlert(
                    "Error",
                    "Terjadi kesalahan saat memperbarui jumlah",
                    "error"
                );
            });
    }

    // Function to update duration via AJAX
    function updateDuration(productId, days) {
        // Show loading effect
        const cartItem = document.getElementById(`cart-item-${productId}`);
        cartItem.classList.add("opacity-50");

        // Send AJAX request
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
                    updateCartUI(data);

                    // Add success feedback animation
                    cartItem.classList.add("transition-all", "duration-300");
                    cartItem.style.boxShadow = "0 0 0 2px #FCCF2F";
                    setTimeout(() => {
                        cartItem.style.boxShadow = "";
                    }, 800);

                    // Show tiny toast-like notification
                    showTinyToast("Durasi berhasil diperbarui");
                } else {
                    showSweetAlert(
                        "Error",
                        data.message || "Terjadi kesalahan",
                        "error"
                    );
                }
                cartItem.classList.remove("opacity-50");
            })
            .catch((error) => {
                console.error("Error updating duration:", error);
                cartItem.classList.remove("opacity-50");
                showSweetAlert(
                    "Error",
                    "Terjadi kesalahan saat memperbarui durasi",
                    "error"
                );
            });
    }

    // Function to update cart UI after AJAX operations
    function updateCartUI(data) {
        if (data.item) {
            // Update quantity display
            const quantityDisplay = document.querySelector(
                `#cart-item-${data.item.id} .quantity-display`
            );
            if (quantityDisplay) {
                quantityDisplay.textContent = data.item.quantity;
            }

            // Update duration display
            const durationDisplay = document.querySelector(
                `#cart-item-${data.item.id} .duration-display`
            );
            if (durationDisplay) {
                durationDisplay.textContent = `${data.item.days} hari`;
            }

            // Update duration value in the control
            const durationValue = document.querySelector(
                `#cart-item-${data.item.id} .duration-value`
            );
            if (durationValue) {
                durationValue.textContent = `${data.item.days} hari`;
            }

            // Update subtotal display
            const subtotalDisplay = document.querySelector(
                `#cart-item-${data.item.id} .subtotal-display`
            );
            if (subtotalDisplay) {
                subtotalDisplay.textContent = `Rp ${new Intl.NumberFormat(
                    "id-ID"
                ).format(data.item.subtotal)}`;
            }

            // Update price estimation
            const priceEstimation = document.querySelector(
                `#cart-item-${data.item.id} .price-estimation`
            );
            if (priceEstimation) {
                const periods = Math.ceil(data.item.days / 3);
                const productPrice =
                    data.item.subtotal / data.item.quantity / periods;
                priceEstimation.textContent = `Rp ${new Intl.NumberFormat(
                    "id-ID"
                ).format(periods * productPrice)}`;

                // Update explanation text
                const explanation = priceEstimation
                    .closest(".bg-gray-50")
                    .querySelector(".text-xs.text-gray-500");
                if (explanation) {
                    explanation.textContent = `${periods} periode × Rp ${new Intl.NumberFormat(
                        "id-ID"
                    ).format(productPrice)}`;
                }
            }

            // Update buttons enabled/disabled state
            const decrementBtn = document.querySelector(
                `#cart-item-${data.item.id} .duration-decrement-btn`
            );
            if (decrementBtn) {
                decrementBtn.disabled = data.item.days <= 3;
            }

            const incrementBtn = document.querySelector(
                `#cart-item-${data.item.id} .duration-increment-btn`
            );
            if (incrementBtn) {
                incrementBtn.disabled = data.item.days >= 30;
            }
        }

        // Update cart total
        if (data.total !== undefined) {
            document.getElementById(
                "cart-total"
            ).textContent = `Rp ${new Intl.NumberFormat("id-ID").format(
                data.total
            )}`;
        }

        // Show notification
        if (data.message) {
            showSweetAlert("Berhasil!", data.message, "success");
        }
    }

    // Function to show SweetAlert notification
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

    // Function to show tiny toast-like notification for minor updates
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

    // Add a custom style for the toast
    const style = document.createElement("style");
    style.innerHTML = `
        .colored-toast {
            border-radius: 10px !important;
            padding: 8px 16px !important;
            font-size: 14px !important;
        }
    `;
    document.head.appendChild(style);

    // Initialize simple checkboxes
    document
        .querySelectorAll('input[type="checkbox"][name="items[]"]')
        .forEach((checkbox) => {
            // Set initial state correctly
            const checkboxVisual = checkbox.nextElementSibling;

            // No need for complex event handling, CSS transitions will handle it
            checkbox.addEventListener("change", function () {
                // Optional: add a subtle ripple effect on change
                if (this.checked) {
                    const ripple = document.createElement("span");
                    ripple.className =
                        "absolute inset-0 bg-yellow-300 rounded-lg animate-ping opacity-75";
                    ripple.style.animationDuration = "0.5s";

                    checkboxVisual.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 500);
                }
            });
        });
});
