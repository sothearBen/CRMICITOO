// css

import '../css/back_app.scss';

// js
const $ = require('jquery');
window.Popper = require('popper.js');
global.$ = global.jQuery = $;
require('bootstrap');


// global

// sidebar

$(document).ready(function () {
    $('#sidebar_collapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });

    if($(window).width() < 1092){
        $('#sidebar').toggleClass('active');
    } 
});
