// assets/js/script.js
document.addEventListener('DOMContentLoaded', function () {
  const studentInput = document.getElementById('student_no');
  const formMessage = document.getElementById('formMessage');

  // Convert any letter in the student number to uppercase on input
  if (studentInput) {
    studentInput.addEventListener('input', (e) => {
      const start = studentInput.selectionStart;
      const end = studentInput.selectionEnd;
      studentInput.value = studentInput.value.toUpperCase();
      studentInput.setSelectionRange(start, end);
    });

    // Client-side validation before submit (gives immediate feedback)
    studentInput.closest('form')?.addEventListener('submit', (ev) => {
      const pattern = /^([0-9]{8}|[0-9]{3}[A-Z][0-9]{4})$/;
      const val = (studentInput.value || '').trim();
      if (!pattern.test(val)) {
        ev.preventDefault();
        showMessage('Please enter a valid student number (e.g., 21100058 or 251S0000).', true);
        studentInput.focus();
      }
    });
  }

  // Function to show message in aria-live region
  function showMessage(text, isError = false) {
    if (!formMessage) return;
    formMessage.textContent = text;
    formMessage.classList.toggle('error', !!isError);
    // Remove error after a few seconds for UX
    setTimeout(() => {
      if (formMessage) {
        formMessage.textContent = '';
        formMessage.classList.remove('error');
      }
    }, 6000);
  }

  // Auto-focus to first visible input for faster flow
  const firstInput = document.querySelector('main input:not([type=hidden]), main select, main textarea');
  if (firstInput) firstInput.focus();
});
