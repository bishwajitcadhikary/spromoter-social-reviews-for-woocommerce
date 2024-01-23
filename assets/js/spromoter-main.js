document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        let fadeElements = document.querySelectorAll(".fade");

        fadeElements.forEach((element) => {
            if (element.classList.contains("show")) {
                element.classList.remove("show");
            }
        });
    }, 2000);
});