

function showHelloPopup() {
    alert("Hello JS");
}


document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('showPopupBtn');
    if(btn) {
        btn.addEventListener('click', showHelloPopup);
    }
});
