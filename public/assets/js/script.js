// Auto-focus on student number input
document.addEventListener('DOMContentLoaded', function() {
    const studentNoInput = document.getElementById('student_no');
    if (studentNoInput) {
        studentNoInput.focus();
    }
    
    // Purpose dropdown change handler
    const purposeSelect = document.getElementById('purpose');
    const notesField = document.getElementById('notes');
    
    if (purposeSelect && notesField) {
        purposeSelect.addEventListener('change', function() {
            if (this.value === 'Others') {
                notesField.placeholder = 'Please specify purpose...';
                notesField.style.display = 'block';
            } else {
                notesField.placeholder = 'Notes (optional)';
            }
        });
    }
});

// Simple form validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = 'red';
        } else {
            input.style.borderColor = '';
        }
    });
    
    return isValid;
}