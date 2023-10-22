function changejure(j) {//j est l'objet input qui a lancé la fonction

    var data_value = j.value;
    var data_type = j.name;
    var id_jure = j.id.split(data_type)[1];

    var formURL = document.getElementsByTagName('form')[0].action;

    $.ajax({
        url: formURL,
        type: "POST",
        data: {value: data_value, type: data_type, idjure: id_jure},

        success: function () {
            document.querySelector('#gestionjures').click()
        },

        error: function (data) {
            alert("Error while submitting Data");
        },
    });


}

function changeequipe(e, i, j) {
    var data_value = e.value;
    var id_equipe = i;
    var id_jure = j;
    console.log(e.value);
    console.log(i);
    console.log(j);
    var formURL = document.getElementsByTagName('form')[0].action;
    $.ajax({
        url: formURL,
        type: "POST",
        data: {value: data_value, idequipe: id_equipe, idjure: id_jure},

        success: function () {
            document.querySelector('#gestionjures').click()

        },

        error: function (data) {
            alert("Error while submitting Data");
        },
    });


}

$('#modalconfirmjure').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var recipient = button.data('idjure');

    var modal = $(this)
    modal.find('.modal-title').text('Attention!!!!')
    modal.find('.modal-body input').val(recipient)
});