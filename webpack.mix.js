const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .css('resources/css/app.css', 'public/css')
   .postCss('resources/css/app.css', 'public/css', [
       require('tailwindcss'),
   ])
   .version();
