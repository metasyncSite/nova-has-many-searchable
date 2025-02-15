const mix = require('laravel-mix')
const tailwindcss = require("tailwindcss")
const path = require('path')

require('./nova.mix')

mix
    .setPublicPath('dist')
    .js('resources/js/field.js', 'js')
    .vue({version: 3})
    .postCss("resources/css/field.css", "css", [tailwindcss("tailwind.config.js")])
    .alias({
        'laravel-nova': path.join(__dirname, '../../laravel/nova/resources/js/mixins/packages.js'),
    })
