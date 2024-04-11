const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .addEntry('loginAndRegister', './assets/loginAndRegister.js')
    // templateSite
    // js
    // .addEntry('customjs', './assets/templateSite/assets/js/custom.js')
    // .addEntry('isotope', './assets/templateSite/assets/js/isotope.min.js')
    // .addEntry('owl-carousel', './assets/templateSite/assets/js/owl-carousel.js')
    // .addEntry('popup', './assets/templateSite/assets/js/popup.js')
    // .addEntry('tabs', './assets/templateSite/assets/js/tabs.js')
    // .addEntry('bootstrapjs', './assets/templateSite/assets/bootstrap/js/bootstrap.min.js')
    // .addEntry('jquery', './assets/templateSite/assets/jquery/jquery.min.js')
    // .addEntry('jqueryslim', './assets/templateSite/assets/jquery/jquery.slim.min.js')
    // css
    // .addStyleEntry('animate', '/assets/templateSite/assets/css/animate.css')
    // .addStyleEntry('flex-slider', '/assets/templateSite/assets/css/flex-slider.css')
    // .addStyleEntry('fontawesome', '/assets/templateSite/assets/css/fontawesome.css')
    // .addStyleEntry('owl', '/assets/templateSite/assets/css/owl.css')
    // .addStyleEntry('templatemo-cyborg-gaming', '/assets/templateSite/assets/css/templatemo-cyborg-gaming.css')
    // .addStyleEntry('bootstrapcss', '/assets/templateSite/assets/css/bootstrap.min.css')

    // template Login
    // // js
    // .addEntry('mainjs', './assets/templateLogin/js/main.js')
    // .addEntry('animsition', './assets/templateLogin/vendor/animsition/js/animsition.min.js')
    // .addEntry('bootstrapjsLogin', './assets/templateLogin/vendor/bootstrap/js/bootstrap.min.js') // modifier les chemins ici
    // .addEntry('popper', './assets/templateLogin/vendor/bootstrap/js/popper.min.js')
    // .addEntry('tooltip', './assets/templateLogin/vendor/bootstrap/js/tooltip.js')
    // .addEntry('countdowntime', './assets/templateLogin/vendor/countdowntime/countdowntime.js')
    // .addEntry('daterangepicker', './assets/templateLogin/vendor/daterangepicker/daterangepicker.js')
    // .addEntry('moment', './assets/templateLogin/vendor/daterangepicker/moment.min.js')
    // .addEntry('jquery321', './assets/templateLogin/vendor/jquery/jquery-3.2.1.min.js')
    // .addEntry('jquerySlimLogin', './assets/templateLogin/vendor/jquery/jquery.slim.min.js')
    // .addEntry('jqueryLogin', './assets/templateLogin/vendor/jquery/jquery.min.js')
    // .addEntry('perfet-scrollbar', './assets/templateLogin/vendor/perfect-scrollbar/perfect-scrollbar.min.js')
    // .addEntry('select2', './assets/templateLogin/vendor/select2/select2.min.js')
    // // css
    // .addStyleEntry('maincss', './assets/templateLogin/css/main.css')
    // .addStyleEntry('utilcss', './assets/templateLogin/css/util.css')
    // .addStyleEntry('animateLogin', './assets/templateLogin/vendor/animate/animate.css')
    // .addStyleEntry('animsitioncss', './assets/templateLogin/vendor/animsition/animsition.min.css')
    // .addStyleEntry('boostrap-grid', './assets/templateLogin/vendor/bootstrap/bootstrap-grid.min.css')
    // .addStyleEntry('boostrap-reboot', './assets/templateLogin/vendor/bootstrap/bootstrap-reboot.min.css')
    // .addStyleEntry('boostrapcssLogin', './assets/templateLogin/vendor/bootstrap/bootstrap-reboot.min.css')
    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning()

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
