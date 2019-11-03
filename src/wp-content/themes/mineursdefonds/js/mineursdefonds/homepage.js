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

function testClick() {
    console.log("click")
}

function explore() {
    exploration = true;
    const elements = document.getElementsByClassName('exploration');
    for (const e of elements) {
        e.classList.add('exploration--mode')
    }
}

const parent = document.getElementById('exploration');
const parallax = new Parallax(parent, {
    invertX: true,
    invertY: true,
    limitX: 26,
    limitY: 5,
});

