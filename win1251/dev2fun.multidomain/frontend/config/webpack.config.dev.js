'use strict';

const webpack = require('webpack');
const {merge} = require('webpack-merge');
const FriendlyErrorsPlugin = require('friendly-errors-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

const commonConfig = require('./webpack.config.common');
const destinationDir = process.env.PROJECT;
const environment = require('./env/dev.env');
const publicPath = ((dir) => {
    let publicPath;
    // publicPath = '/bitrix/modules/dev2fun.multidomain/frontend/dist/';
    // publicPath = '/bitrix/js/dev2fun.multidomain/vue/';
    switch (dir) {
        // case 'dev':
            // publicPath = '/';
            // break;
        case 'server':
            publicPath = '/bitrix/js/dev2fun.multidomain/vue/';
            break;
        default:
            publicPath = '/bitrix/modules/dev2fun.multidomain/frontend/dist/';
            break;
    }
    return publicPath
})(process.env.DIR);

const path = require('path');
const distPath = ((dir) => {
    let distPath;
    switch (dir) {
        case 'server':
            distPath = path.join(__dirname, '../../../../js/dev2fun.multidomain/vue');
            break;
        default:
            distPath = path.join(__dirname, '../dist');
            break;
    }
    return distPath
})(process.env.DIR);

const PATHS = {
    src: path.join(__dirname, '../src'),
    dist: distPath,
    // dist: path.join(__dirname, '../../../../js/dev2fun.multidomain/vue'),
    // dist: path.join(__dirname, '../dist'),
    assets: 'assets/'
};

let staticVersion = require('./version');

const webpackConfig = merge(commonConfig, {
    mode: 'development',
    watch: true,
    devtool: 'cheap-module-eval-source-map',
    output: {
        path: PATHS.dist,
        publicPath: publicPath,
        filename: `js/[name].${staticVersion}.bundle.js`,
        chunkFilename: 'js/[name].[contenthash].chunk.js'
        // chunkFilename: 'js/[name].[hash:8].chunk.js'
    },
    optimization: {
        minimizer: [
            new TerserPlugin({
                cache: true,
                parallel: true,
                terserOptions: {
                    warnings: false,
                    ie8: false,
                    output: {
                        comments: false,
                    },
                },
                extractComments: false,
            })
        ]
    },
    plugins: [
        new webpack.EnvironmentPlugin(environment),
        new webpack.HotModuleReplacementPlugin(),
        new FriendlyErrorsPlugin(),
        // new BundleAnalyzerPlugin()
    ],
    // devServer: {
    //     compress: true,
    //     historyApiFallback: true,
    //     hot: true,
    //     open: true,
    //     overlay: true,
    //     port: 8000,
    //     stats: {
    //         normal: true
    //     }
    // }
});

// webpackConfig.devtool = 'source-map';

// if (process.env.npm_config_report) {
//     const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
//     webpackConfig.plugins.push(new BundleAnalyzerPlugin());
// }

module.exports = webpackConfig;
