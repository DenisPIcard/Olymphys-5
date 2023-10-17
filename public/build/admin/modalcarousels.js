
    $("#modaldiapo").on("submit", function (e) {
    var formURL = $(this).attr("action");
    console.log(formURL);
    $.ajax({
    url: formURL,
    type: "GET",
    data: {
    idDiapo: $("#diapoId").val(),

},
    console.log(data);
});
});
