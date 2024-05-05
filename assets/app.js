import './bootstrap.js';

const jQuery = require('jquery');
global.$ = global.jQuery = jQuery;
import './templateSite/vendor2/bootstrap/js/bootstrap.min.js';
// import './templateSite/assets/js/isotope.min.js';
import './templateSite/assets/js/owl-carousel.js';
import './templateSite/assets/js/popup.js';
import './templateSite/assets/js/popper.min.js';
import './templateSite/assets/js/tabs.js';
import './templateSite/assets/js/custom.js';


import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';
const slider = document.getElementById('year-slider');
if (slider) {
    const min = document.getElementById('min');
    const max = document.getElementById('max');

    const range = noUiSlider.create(slider, {
        start: [min?.value || 1940, max?.value || 2025],
        step: 1,
        connect: true,
        range: {
            'min': 1940,
            'max': 2025
        }
    });
    range.on('slide', function(values, handle) {
        if ( handle === 0) {
            min.value = Math.round(values[0])
        }
        if ( handle === 1) {
            max.value = Math.round(values[1])
        }
    })
    min.addEventListener('change', function () {
        range.set([this.value || 1940, null]);
    })
    max.addEventListener('change', function() {
        range.set([null, this.value || 2025]); // Mettez à jour la valeur maximale du slider
    });
    
}

/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './templateSite/assets/css/animate.css';
import './templateSite/assets/css/flex-slider.css';
import './templateSite/assets/css/fontawesome.css';
import './templateSite/assets/css/owl.css';
import './templateSite/assets/css/templatemo-cyborg-gaming.css';