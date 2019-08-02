let mix = require('laravel-mix');

mix.styles([
    'public/css/style.css',
    'public/css/bootstrap.min.css',
    'public/css/nucleo-icons.css',
    'public/css/paper-kit.css'
], 'public/css/app.css');

mix.scripts([
    'public/js/jquery-3.2.1.js',
    'public/js/jquery-ui-1.12.1.custom.min.js',
    'public/js/tether.min.js',
    'public/js/bootstrap.min.js',
    'public/js/nouislider.js',
    'public/js/bootstrap-switch.min.js',
    'public/js/main.js'
], 'public/js/app.js');

if (mix.inProduction()){
    mix.version();
}
