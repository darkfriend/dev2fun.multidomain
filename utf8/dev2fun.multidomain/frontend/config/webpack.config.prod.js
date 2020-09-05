'use strict';
const path = require('path');

const webpack = require('webpack');
const {merge} = require('webpack-merge');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

const TerserPlugin = require('terser-webpack-plugin');
// const CompressionPlugin = require('compression-webpack-plugin');
const commonConfig = require('./webpack.config.common');
const isProd = process.env.NODE_ENV === 'production';
// const destinationDir = process.env.PROJECT ?? '';
const destinationDir = '';
const environment = isProd ? require('./env/prod.env') : require('./env/staging.env');

const publicPath = ((dir) => {
    let publicPath;
    // publicPath = '/bitrix/modules/dev2fun.multidomain/frontend/dist/';
    publicPath = '/bitrix/js/dev2fun.multidomain/vue/';
    // switch (dir) {
    //     case 'dev':
    //     //     publicPath = '/';
    //     //     break;
    //     case 'devserver':
    //         publicPath = '/assets/private/';
    //         break;
    //     case 'server':
    //         publicPath = '/assets/private/';
    //         break;
    //     default:
    //         publicPath = '/assets/private/';
    //         break;
    // }
    return publicPath
})(process.env.DIR);

const PATHS = {
    src: path.join(__dirname, '../src'),
    dist: path.join(__dirname, '../dist', destinationDir),
    assets: 'assets/'
};

const webpackConfig = merge(commonConfig, {
    mode: 'production',
    devtool: false,
    output: {
        path: PATHS.dist,
        publicPath: publicPath,
        filename: '[name].bundle.js',
        chunkFilename: '[name].[hash:8].chunk.js',
        // filename: 'js/[name].bundle.js',
        // chunkFilename: 'js/[name].[hash:8].chunk.js'
    },
    optimization: {
        minimize: true,
        minimizer: [
            // new OptimizeCSSAssetsPlugin({}),
            new TerserPlugin({
                cache: true,
                parallel: true,
                terserOptions: {
                    ecma: 6,
                    ie8: false,
                    compress: {
                        passes: 2,
                        drop_console: true,
                        warnings: false,
                    },
                    output: {
                        comments: false,
                    },
                },
                extractComments: false,
            })
        ],
        // flagIncludedChunks: true,
        splitChunks: {
            chunks: "async",
            minSize: 20000,
            minChunks: 2,
            maxAsyncRequests: 5,
            maxInitialRequests: 3,
            automaticNameDelimiter: '~',
            name: true,
            cacheGroups: {
                vendor: {
                    test: /[\\/]node_modules[\\/]/,
                    name(module) {
                        const packageName = module.context.match(/[\\/]node_modules[\\/](.*?)([\\/]|$)/)[1];
                        return `npm.${packageName.replace('@', '')}`;
                    },
                    minSize: 20000,
                    reuseExistingChunk: true
                },
                // styles: {
                //     chunks: 'all',
                //     test: /\.css$/,
                //     name: 'styles',
                //     enforce: true,
                //     minSize: 20000,
                //     priority: 2,
                //     reuseExistingChunk: true
                // },
            }
        },
    },
    plugins: [
        new webpack.EnvironmentPlugin(environment),
        // new CompressionPlugin({
        //     filename: '[path].gz[query]',
        //     algorithm: 'gzip',
        //     test: new RegExp('\\.(js|css)$'),
        //     threshold: 10240,
        //     minRatio: 0.8
        // }),
        new webpack.HashedModuleIdsPlugin()
    ],
    // devServer: {
    //     proxy: {
    //       "/api": {
    //         target: "http://localhost:8080"
    //       }
    //     }
    //   }

});

if (!isProd) {
    webpackConfig.devtool = 'source-map';
    // if (process.env.npm_config_report) {
        // const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
        // webpackConfig.plugins.push(new BundleAnalyzerPlugin());
    // }
}

module.exports = webpackConfig;
