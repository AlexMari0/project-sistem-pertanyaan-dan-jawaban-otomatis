document.addEventListener("DOMContentLoaded", function () {
    const alert = document.getElementById("success-alert");
    if (alert) {
        setTimeout(() => {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";

            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 3000);
    }
});
