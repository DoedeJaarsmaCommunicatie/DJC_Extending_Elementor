'use strict'; // eslint-disable-line

const webpack = require('webpack');
const merge = require('webpack-merge');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const FriendlyErrorsWebpackPlugin = require('friendly-errors-webpack4-plugin');

const config = require('./config');

const assetsFilenames = (config.enabled.cacheBusting) ? config.cacheBusting : '[name]';

let webpackConfig = {
    context: config.paths.assets,
    entry: config.entry,
    devtool: (config.enabled.sourceMaps ? '#source-map' : undefined),
    mode: config.mode,
    output: {
        path: config.paths.dist,
        publicPath: config.publicPath,
        filename: `${assetsFilenames}.js`,
    },
    stats: {
        hash: false,
        version: false,
        timings: false,
        children: false,
        errors: false,
        errorDetails: false,
        warnings: false,
        chunks: false,
        modules: false,
        reasons: false,
        source: false,
        publicPath: false,
    },
    module: {
        rules: [
            {
                enforce: "pre",
                test: /\.(js|vue)$/,
                include: config.paths.assets,
                use: 'eslint',
            },
            {
                enforce: "pre",
                test: /\.(js|s[ca]ss)$/,
                include: config.paths.assets,
                loader: 'import-glob',
            },
            {
                test: /\.js$/,
                exclude: [/node_modules/],
                use: [
                    { loader: "cache" },
                    { loader: "babel" }
                ]
            },
            {
                test: /\.css$/,
                include: config.paths.assets,
                use: ExtractTextPlugin.extract({
                    fallback: 'style',
                    use: [
                        { loader: 'cache' },
                        { loader: 'css', options: { sourceMap: config.enabled.sourceMaps } },
                        {
                            loader: 'postcss', options: {
                                config: { path: __dirname, ctx: config },
                                sourceMap: config.enabled.sourceMaps,
                            },
                        },
                    ],
                }),
            },
            {
                test: /\.scss$/,
                include: config.paths.assets,
                use: ExtractTextPlugin.extract({
                    fallback: 'style',
                    use: [
                        { loader: 'cache' },
                        { loader: 'css', options: { sourceMap: config.enabled.sourceMaps } },
                        {
                            loader: 'postcss', options: {
                                config: { path: __dirname, ctx: config },
                                sourceMap: config.enabled.sourceMaps,
                            },
                        },
                        { loader: 'resolve-url', options: { sourceMap: config.enabled.sourceMaps } },
                        { loader: 'sass', options: { sourceMap: config.enabled.sourceMaps } },
                    ],
                }),
            },
            {
                test: /\.(ttf|otf|eot|woff2?|png|jpe?g|gif|svg|ico)$/,
                include: config.paths.assets,
                loader: 'url',
                options: {
                    limit: 4096,
                    name: `[path]${assetsFilenames}.[ext]`,
                },
            },

        ],
    },
    resolve: {
        modules: [
            config.paths.assets,
            'node_modules',
        ],
        enforceExtension: false,
        alias: {
            vue: 'vue/dist/vue.js'
        },
    },
    resolveLoader: {
        moduleExtensions: ['-loader'],
    },
    externals: {
        jquery: 'jQuery',
    },
    plugins: [
        new StyleLintPlugin({
            failOnError: !config.enabled.watcher,
            syntax: 'scss',
        }),
        new ExtractTextPlugin({
            filename: `${assetsFilenames}.css`,
            allChunks: true,
            disable: (config.enabled.watcher),
        }),
        new FriendlyErrorsWebpackPlugin(),
    ],
}

module.exports = webpackConfig;
