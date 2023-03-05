$(document).ready(function() {
    $("#search-button").click(function() {
        var gouv = $("#gouv").val();

        $.ajax({
            url: "/evenement/event/search",
            type: "GET",
        })})})