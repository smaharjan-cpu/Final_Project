function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function isValidPassword(password) {
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
  return passwordRegex.test(password);
}

function isValidUsername(username) {
  const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
  return usernameRegex.test(username);
}

function showError(elementId, message) {
  const element = document.getElementById(elementId);
  if (element) {
    element.classList.add("error");
    element.setAttribute("aria-invalid", "true");
    let errorMsg = element.nextElementSibling;
    if (!errorMsg || !errorMsg.classList.contains("error-message")) {
      errorMsg = document.createElement("small");
      errorMsg.classList.add("error-message");
      errorMsg.style.color = "#dc3545";
      element.parentNode.insertBefore(errorMsg, element.nextSibling);
    }
    errorMsg.textContent = message;
  }
}

function clearError(elementId) {
  const element = document.getElementById(elementId);
  if (element) {
    element.classList.remove("error");
    element.setAttribute("aria-invalid", "false");

    let errorMsg = element.nextElementSibling;
    if (errorMsg && errorMsg.classList.contains("error-message")) {
      errorMsg.remove();
    }
  }
}

function validateRegistrationForm() {
  const name = document.getElementById("name")?.value.trim();
  const email = document.getElementById("email")?.value.trim();
  const password = document.getElementById("password")?.value;
  const confirmPassword = document.getElementById("confirm_password")?.value;

  let isValid = true;

  if (!name || name.length < 2) {
    showError("name", "Name must be at least 2 characters");
    isValid = false;
  } else {
    clearError("name");
  }

  if (!email || !isValidEmail(email)) {
    showError("email", "Please enter a valid email address");
    isValid = false;
  } else {
    clearError("email");
  }

  // Validate password
  if (!password || !isValidPassword(password)) {
    showError(
      "password",
      "Password must be at least 8 characters with uppercase, lowercase, and number"
    );
    isValid = false;
  } else {
    clearError("password");
  }

  // Validate password confirmation
  if (password !== confirmPassword) {
    showError("confirm_password", "Passwords do not match");
    isValid = false;
  } else {
    clearError("confirm_password");
  }

  return isValid;
}

// ================================================================
// Login Form Validation
// ================================================================

function validateLoginForm() {
  const email = document.getElementById("email")?.value.trim();
  const password = document.getElementById("password")?.value;

  let isValid = true;

  if (!email || !isValidEmail(email)) {
    showError("email", "Please enter a valid email address");
    isValid = false;
  } else {
    clearError("email");
  }

  if (!password || password.length < 6) {
    showError("password", "Please enter your password");
    isValid = false;
  } else {
    clearError("password");
  }

  return isValid;
}

function addToCart(productId) {
  if (typeof window.IS_LOGGED_IN !== "undefined" && !window.IS_LOGGED_IN) {
    window.location.href = "login.php";
    return;
  }

  fetch("includes/add_to_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "product_id=" + productId + "&quantity=1",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Product added to cart!", "success");
        updateCartCount();
      } else {
        showNotification(data.message || "Failed to add to cart", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("An error occurred", "error");
    });
}

function addToWishlist(productId) {
  if (typeof window.IS_LOGGED_IN !== "undefined" && !window.IS_LOGGED_IN) {
    window.location.href = "login.php";
    return;
  }

  fetch("includes/add_to_wishlist.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "product_id=" + productId,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        showNotification("Added to wishlist", "success");
      } else {
        showNotification(data.message || "Failed to add to wishlist", "error");
      }
    })
    .catch((err) => {
      console.error(err);
      showNotification("An error occurred", "error");
    });
}

function removeFromCart(cartId) {
  if (confirm("Are you sure you want to remove this item?")) {
    fetch("includes/remove_from_cart.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "cart_id=" + cartId,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          location.reload();
        } else {
          showNotification(data.message || "Failed to remove item", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification("An error occurred", "error");
      });
  }
}

/**
 * Update cart item quantity
 */
function updateCartQuantity(cartId, quantity) {
  if (quantity < 1) {
    removeFromCart(cartId);
    return;
  }

  fetch("includes/update_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "cart_id=" + cartId + "&quantity=" + quantity,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        showNotification(data.message || "Failed to update quantity", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("An error occurred", "error");
    });
}

function updateCartCount() {
  fetch("includes/get_cart_count.php")
    .then((response) => response.json())
    .then((data) => {
      const cartLink = document.querySelector('a[href*="cart.php"]');
      if (cartLink && data.count) {
        cartLink.textContent = "Cart (" + data.count + ")";
      }
    })
    .catch((error) => console.error("Error updating cart count:", error));
}

function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = "alert alert-" + type;
  notification.setAttribute("role", "alert");
  notification.textContent = message;
  notification.style.position = "fixed";
  notification.style.top = "20px";
  notification.style.right = "20px";
  notification.style.zIndex = "9999";
  notification.style.maxWidth = "400px";

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 5000);
}

function filterByCategory(category) {
  const url = new URL(window.location);
  if (category === "all") {
    url.searchParams.delete("category");
  } else {
    url.searchParams.set("category", category);
  }
  window.location.href = url.toString();
}

function searchProducts(searchTerm) {
  const url = new URL(window.location);
  if (searchTerm.trim() === "") {
    url.searchParams.delete("search");
  } else {
    url.searchParams.set("search", searchTerm);
  }
  window.location.href = url.toString();
}

function formatCurrency(amount) {
  return "$" + parseFloat(amount).toFixed(2);
}

/**
 * Confirm action
 */
function confirmAction(message = "Are you sure?") {
  return confirm(message);
}

/**
 * Disable form during submission
 */
function disableFormSubmit(formId) {
  const form = document.getElementById(formId);
  if (form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Processing...";
    }
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const registerForm = document.querySelector('form[name="register"]');
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      if (!validateRegistrationForm()) {
        e.preventDefault();
      }
    });
  }

  const loginForm = document.querySelector('form[name="login"]');
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      if (!validateLoginForm()) {
        e.preventDefault();
      }
    });
  }

  updateCartCount();

  const inputs = document.querySelectorAll("input, textarea, select");
  inputs.forEach((input) => {
    input.addEventListener("input", function () {
      clearError(this.id);
    });
  });
});

window.StoreApp = {
  addToCart,
  addToWishlist,
  removeFromCart,
  updateCartQuantity,
  filterByCategory,
  searchProducts,
  formatCurrency,
  confirmAction,
  validateRegistrationForm,
  validateLoginForm,
};
