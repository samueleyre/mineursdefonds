let menuActivated = false;

function rotate() {
    document.getElementById('nav-menu-icon').classList.toggle('nav-menu-icon--open')
}

function activateMenu() {
    rotate();
    menuActivated = !menuActivated;
    if (menuActivated) {
        document.getElementById('nav-menu-items').classList.toggle('nav-menu-items--open')
    } else {
        document.getElementById('nav-menu-items').classList.toggle('nav-menu-items--open')
    }
}
