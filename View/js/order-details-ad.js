function updateOrderStatus(orderId, newStatus) {
  fetch("../../Controller/update_order.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      orderId: orderId,
      status: newStatus
    })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
        return;
      }

      alert(data.message || "Order status could not be updated.");
    })
    .catch(() => {
      alert("Order status could not be updated.");
    });
}

document.querySelectorAll(".status-dropdown").forEach(dropdown => {
  const selected = dropdown.querySelector(".selected");
  const options = dropdown.querySelector(".options");

  if (!selected || !options) {
    return;
  }

  selected.addEventListener("click", event => {
    event.stopPropagation();
    options.style.display = options.style.display === "block" ? "none" : "block";
  });

  dropdown.querySelectorAll(".option").forEach(option => {
    option.addEventListener("click", event => {
      event.stopPropagation();
      options.style.display = "none";
    });
  });
});

document.addEventListener("click", () => {
  document.querySelectorAll(".status-dropdown .options").forEach(options => {
    options.style.display = "none";
  });
});

const subtotalNode = document.querySelector(".total[data-subtotal]");
const discountInput = document.querySelector('input[name="discount"]');
const deliveryInput = document.querySelector('input[name="delivery"]');
const totalDisplay = document.getElementById("totalDisplay");

function updateTotalPreview() {
  if (!subtotalNode || !totalDisplay) {
    return;
  }

  const subtotal = Number(subtotalNode.dataset.subtotal || 0);
  const discount = Math.max(0, Number(discountInput ? discountInput.value : 0));
  const delivery = Math.max(0, Number(deliveryInput ? deliveryInput.value : 0));
  const discounted = subtotal - (subtotal * Math.min(discount, 100) / 100);
  const total = discounted + delivery;

  totalDisplay.textContent = total.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }) + " EGP";
}

if (discountInput) {
  discountInput.addEventListener("input", updateTotalPreview);
}

if (deliveryInput) {
  deliveryInput.addEventListener("input", updateTotalPreview);
}
