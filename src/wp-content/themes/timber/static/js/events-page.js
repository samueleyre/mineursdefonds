jQuery(document).ready(function ($) {
    const elements = document.getElementsByClassName('event');
    for (let i=0; i < elements.length; i++) {
        elements[i].onclick = (event)=> {
            if (!('link' in elements[i].dataset)) {
                console.error(' You need to add data-link !')
            }
            const url = elements[i].dataset['link'];
            window.open(url, '_blank');
        }
    }
});
