function updateMessageStatus(messageId, newStatus) {
  fetch("../../Controller/update_message_status.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      id: messageId,
      status: newStatus
    })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
        return;
      }

      alert(data.message || "Message status could not be updated.");
    })
    .catch(() => {
      alert("Message status could not be updated.");
    });
}

document.querySelectorAll(".status-dropdown").forEach(drop => {
  const selected = drop.querySelector(".selected");
  const row = drop.closest(".row");

  if (!selected) {
    return;
  }

  selected.addEventListener("click", event => {
    event.stopPropagation();

    document.querySelectorAll(".status-dropdown").forEach(otherDrop => {
      if (otherDrop !== drop) {
        otherDrop.classList.remove("active");
        const otherRow = otherDrop.closest(".row");
        if (otherRow) {
          otherRow.classList.remove("active");
        }
      }
    });

    drop.classList.toggle("active");
    if (row) {
      row.classList.toggle("active");
    }
  });

  drop.querySelectorAll(".option").forEach(option => {
    option.addEventListener("click", event => {
      event.stopPropagation();
      drop.classList.remove("active");
      if (row) {
        row.classList.remove("active");
      }
    });
  });
});
document.addEventListener("click", (event) => {
  if (!event.target.closest(".status-dropdown") && !event.target.closest(".escalate-form")) {
    document.querySelectorAll(".status-dropdown").forEach(drop => {
      drop.classList.remove("active");
      const row = drop.closest(".row");
      if (row) {
        row.classList.remove("active");
      }
    });
  }
});