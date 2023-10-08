function supprimerFeedy(event) {
    let button = event.target;
    let feedy = button.closest(".feedy");

    let xhr = new XMLHttpRequest();
    let URL = Routing.generate('deletePublication ', {"id": button.dataset.publicationId});

    xhr.open("DELETE", URL, true);
    xhr.onload = function () {
        feedy.remove();
    };
    xhr.send(null);
}

let buttons = document.getElementsByClassName("delete-feedy");
Array.from(buttons).forEach(function (button) {
    button.addEventListener("click", supprimerFeedy);
});