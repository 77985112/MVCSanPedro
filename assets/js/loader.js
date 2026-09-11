document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        document.getElementById('loader').classList.add('move-to-top-right');
    }, 2500);

    setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
        document.getElementById('content').style.display = 'block';
    }, 4000);
});