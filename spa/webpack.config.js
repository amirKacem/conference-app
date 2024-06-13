const Encore = require('@symfony/webpack-encore');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const { DefinePlugin } = require('webpack');

Encore.setOutputPath('public/')
    .setPublicPath('/')
    .cleanupOutputBeforeBuild()
    .addEntry('app', './src/index.js')
    .enableSassLoader()
    .enableReactPreset()
    .enableSingleRuntimeChunk()
    .addPlugin(
        new HtmlWebpackPlugin({
            template: 'src/index.html',
            alwaysWriteToDisk: true,
        })
    )
    .addPlugin(
        new DefinePlugin({
            ENV_API_ENDPOINT: JSON.stringify(process.env.API_ENDPOINT),
        })
    );

module.exports = Encore.getWebpackConfig();
