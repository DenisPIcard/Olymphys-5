

     modal = document.getElementById('modaldiapo')
     modal.addEventListener("show.bs.modal", function (event) {

     console.log('Ok');
// Button that triggered the modal
     button = event.relatedTarget
// Extract info from data-bs-* attributes
     recipient = button.getAttribute("data-bs-iddiapo")
     console.log(recipient)
     modalFooterInput = modal.querySelector(".modal-footer input")
     modalFooterInput.value = recipient
    })

