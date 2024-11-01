const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .disableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    
    .enableVersioning(Encore.isProduction())

    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    .enableSassLoader()

    //O CSS VAI SER LIDO A PARTIR DE APP.JS
    .addEntries({
        'app': './assets/scripts/app.js',
    })
    .configureFilenames({
        css: 'css/[name].css', //  css/[name]-[contenthash].css
        js: 'js/[name].js' //  js/[name]-[chunkhash].js
    })
    .configureImageRule({
        filename: 'images/[name][ext]'
    })
    .configureFontRule({
        filename: 'fonts/[name][ext]'
    })
    
;

module.exports = Encore.getWebpackConfig();
