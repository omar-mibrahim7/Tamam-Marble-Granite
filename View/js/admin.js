function confirmStatusChange(){return confirm('UI only preview: status changed visually only.');}
document.addEventListener('DOMContentLoaded', function () {
  const dateMode = document.getElementById('dateMode');
  if (!dateMode) return;
  function updateDateFields() {
    document.querySelectorAll('.export-date-option').forEach(el => el.style.display = 'none');
    if (dateMode.value === 'month') document.querySelectorAll('.export-month').forEach(el => el.style.display = 'block');
    else if (dateMode.value === 'last_months') document.querySelectorAll('.export-last-months').forEach(el => el.style.display = 'block');
    else if (dateMode.value === 'range') document.querySelectorAll('.export-range').forEach(el => el.style.display = 'block');
  }
  dateMode.addEventListener('change', updateDateFields);
  updateDateFields();
});