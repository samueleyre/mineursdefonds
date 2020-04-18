function rotate() {
    document.getElementById('nav-menu-icon').classList.toggle('nav-menu-icon--open')
}

function activateMenu() {
    rotate();
    document.getElementById('nav-menu-items-wrapper').classList.toggle('nav-menu-items-wrapper--open')
}

function toggleSubMenu() {
    document.getElementById('nav-menu-main-items').classList.toggle('nav-menu-main-items--open')
    document.getElementById('nav-menu-sub-items').classList.toggle('nav-menu-sub-items--open')
}
