let exploration = false;

/**
 * Special cursor for exploration
 */
function mouseMove(event) {

    var x = event.clientX;
    var y = event.clientY;

    var circle = document.getElementById("circle-mask");
    var shadowCircle = document.getElementById("circle-shadow");

    var top = document.body.offsetTop;
    var left = document.body.offsetLeft;

    var mX = (x - left);
    var mY = (y - top);

    circle.setAttribute("cx", mX)
    circle.setAttribute("cy", mY)
    shadowCircle.setAttribute("cx", mX)
    shadowCircle.setAttribute("cy", mY);

}

function explore() {
    exploration = true;
    document.getElementsByClassName('homepage')[0].style.display = 'none';
    document.getElementsByClassName('nav-title')[0].style.display = 'none';
    document.getElementsByTagName('body')[0].style.backgroundImage = 'none';
    document.getElementsByTagName('body')[0].style.background = 'black';
    const elements = document.getElementsByClassName('exploration');
    for (const e of elements) {
        e.classList.add('exploration--mode')
    }
}

jQuery( document ).ready( function( $ ) {

    const parent = document.getElementById('exploration');
    const parallax = new Parallax(parent, {
        invertX: true,
        invertY: true,
        limitX: 26,
        limitY: 5,
    });

});
