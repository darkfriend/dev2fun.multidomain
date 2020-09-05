const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
// const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

const VueLoaderPlugin = require('vue-loader/lib/plugin');
// const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
// const CopyWebpackPlugin = require('copy-webpack-plugin');
const destinationDir = process.env.PROJECT;
const isDev = process.env.NODE_ENV === 'development';

// const { StatsWriterPlugin } = require("webpack-stats-plugin")

const PATHS = {
    src: path.join(__dirname, '../src'),
    dist: path.join(__dirname, '../dist'),
    core: path.join(__dirname, '../'),
    svg: path.join(__dirname, '../src/assets/svg'),
    assets: 'assets/',
};


webpackConfig = {
    externals: {
        paths: PATHS
    },
    entry: {
        main: path.join(PATHS.src, './main.js'),
        polyfill: '@babel/polyfill',
    },
    stats: {
        children: false,
        chunks: false,
        chunkModules: false,
        modules: false,
        reasons: false,
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
                include: [PATHS.src],
                exclude: /node_modules/
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                include: [PATHS.src],
                options: {
                    loaders: {
                        'scss': [
                            'vue-style-loader',
                            'css-loader',
                            'sass-loader?indentedSyntax'
                        ],
                        'sass': [
                            'vue-style-loader',
                            'css-loader',
                            'sass-loader?indentedSyntax'
                        ],
                    }
                    // other vue-loader options go here
                }
            },
            {
                test: /\.scss$/,
                use: [
                    'vue-style-loader',
                    // isDev ? 'vue-style-loader' : {
                    //     loader: MiniCssExtractPlugin.loader,
                    //     options: {esModule: true}
                    // },
                    'style-loader',
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: {esModule: true}
                    },
                    'css-loader',
                    {
                        loader: "sass-loader?indentedSyntax",
                        options: {
                            sassOptions: {
                                indentedSyntax: true
                            }
                        //     data: `@import "@/styles/main.scss"`,
                            // implementation: require('scss'),
                        //         // ` @import "@/styles/_variables.scss";
                        //         //   @import "@/styles/_mixins.scss";
                        //         // `
                        }
                    },
                ],
            },
            {
                test: /\.css$/,
                use: [
                    'style-loader',
                    // 'vue-style-loader',
                    // {
                        // loader: MiniCssExtractPlugin.loader,
                        // options: {esModule: true}
                    // },
                    'css-loader'
                ]
            },
            {
                test: /\.(png|jpe?g|gif|svg)$/i,
                // include: path.join(__dirname, '../src/assets/images'),
                // includePath: './assets/images',
                use: [
                    // {
                    //     loader: 'url-loader',
                    //     options: {
                    //         limit: 10000,
                    //         name: 'images/[name].[ext]?[hash]',
                    //         publicPath: '/assets/private',
                    //     }
                    // },
                    {
                        loader: 'file-loader',
                        options: {
                            name: '[name].[ext]?[hash]',
                            outputPath: 'images/',
                            // publicPath: '/assets/private',
                            // outputPath: path.join(PATHS.dist, './images'),
                            // limit: 50000,
                        }
                    },
                ]
            },
            {
                test: /\.(woff(2)?|ttf|eot|svg|otf)(\?v=\d+\.\d+\.\d+)?$/,
                // test: /\.(woff|woff2|eot|ttf|otf)$/,
                include: path.resolve(__dirname, '../assets'),
                use: [
                    {
                        // loader: 'url-loader',
                        loader: 'file-loader',
                        // include: [/fonts/],
                        options: {
                            name: '[name].[ext]?[hash]',
                            outputPath: 'fonts/',
                            // publicPath: '/assets/private',
                            limit: 100000,
                        }
                    }
                ]
            },
            {
                test: /\.svg$/,
                loader: 'svg-sprite-loader',
                options: {}
            },
        ]
    },
    resolve: {
        alias: {
            // 'vue$': 'vue/dist/vue.runtime.js',
            'vue$': 'vue/dist/vue.runtime.esm.js',
            '@': PATHS.src,
            '@svg': PATHS.svg,
            // 'bootstrap-vue$': 'bootstrap-vue/src/index.js',
            // vue: 'vue/dist/vue.esm.js',
            // 'moment': 'moment/src/moment' // ->  (gzip 87kb | parsed 385) to (gzip 118kb | parsed 604)
        },
        extensions: ['*', '.js', '.vue', '.json']
    },
    // devServer: {
    //     historyApiFallback: true,
    //     noInfo: true,
    //     overlay: true
    // },
    performance: {
        hints: false
    },
    devtool: '#eval-source-map',
    plugins: [
        new CleanWebpackPlugin(),
        new VueLoaderPlugin(),
        new webpack.DefinePlugin({
            '__PROJECT': JSON.stringify(process.env.PROJECT),
            '__PROJECT_DIR': JSON.stringify(process.env.DIR),
            '__PROJECT_TYPE': JSON.stringify(process.env.TYPE),
        }),
        // new webpack.IgnorePlugin({
        //     resourceRegExp: /^\.\/locale$/,
        //     contextRegExp: /moment$/,
        // }),
        // new CopyWebpackPlugin([
        //     {
        //         from: path.join(PATHS.src, 'assets', 'images'),
        //         to: path.join(PATHS.dist, 'images'),
        //     },
        //     {
        //         from: path.join(PATHS.src, 'assets', 'fonts'),
        //         to: path.join(PATHS.dist, 'fonts'),
        //     },
        // ]),
        // new MiniCssExtractPlugin({
        //     filename: 'css/bundle.css',
        //     chunkFilename: 'css/[id].[hash].css'
        // }),
        // new StatsWriterPlugin({
        //   filename: "stats.json" // Default
        // })
    ]
};
module.exports = webpackConfig;
