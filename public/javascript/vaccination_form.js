window.onload = function() {
    // Check if there's a success modal element on the page
    if (document.getElementById('successModal')) {
        // Show the modal if it exists
        document.getElementById('successModal').style.display = 'flex';
    }

    // Set up the event listener for the "OK!" button to hide the modal
    document.getElementById('okButton')?.addEventListener('click', function() {
        // Hide the modal when the "OK!" button is clicked
        document.getElementById('successModal').style.display = 'none';
    });
};

// document.getElementById("editButton").addEventListener("click", function() {
//     // Enable form fields for editing
//     let inputs = document.querySelectorAll("input[readonly]");
//     inputs.forEach(input => {
//         input.removeAttribute("readonly");
//     });
// });

