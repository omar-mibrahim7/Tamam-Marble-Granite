function updateOrderStatus(orderId, newStatus) {
  fetch('../../Controller/update_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ orderId, status: newStatus })
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || 'Status could not be updated.');
      }
    })
    .catch(() => alert('Status could not be updated.'));
}

document.querySelectorAll(".status-dropdown").forEach(drop => {
  const selected = drop.querySelector(".selected");

  selected.addEventListener("click", (e) => {
    e.stopPropagation();
    document.querySelectorAll(".status-dropdown").forEach(d => {
      if (d !== drop) d.classList.remove("active");
    });
    drop.classList.toggle("active");
  });
});

document.addEventListener("click", () => {
  document.querySelectorAll(".status-dropdown").forEach(d => d.classList.remove("active"));
});
