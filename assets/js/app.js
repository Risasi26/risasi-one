// Simple client-side form validation helpers
function confirmAction(message) {
  return confirm(message);
}

// Add confirmation for delete buttons if present
document.addEventListener("click", function (event) {
  if (event.target.matches(".confirm-delete")) {
    if (!confirmAction("Are you sure you want to delete this item?")) {
      event.preventDefault();
    }
  }
});
