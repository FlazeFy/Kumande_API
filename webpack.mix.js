const mix = require('laravel-mix');

mix 
    .js('resources/js/app.js', 'public/js')
    .js('resources/js/swagger.js', 'public/js')
    .version();