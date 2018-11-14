'use-strict'; // eslint-disable-line

const path = require('path');
const { argv } = require('yargs');
const merge = require('webpack-merge');

const desire = require('./util/desire');

const userConfig = merge(desire(`${__dirname}/../../config`), desire(`${__dirname}/../config`));

const isProduction = !!((argv.env && argv.env.production) || argv.p);
const rootPath = (userConfig.paths && userConfig.paths.root) ?
    userConfig.paths.root : process.cwd();

const config = merge({
    open: true,
    mode: isProduction ? 'production' : 'development',
    copy: 'images/**/*',
    cacheBusting: '[name]_[hash]',
    paths: {
        root: rootPath,
        assets: rootPath,
        dist: rootPath,
    },
    enabled: {
        sourceMaps: !isProduction,
        optimize: isProduction,
        cacheBusting: isProduction,
        watcher: !!argv.watch,
    },
    watch: [],
}, userConfig)

module.exports = merge(config, {
    env: Object.assign({ production: isProduction, development: !isProduction }, argv.env),
    publicPath: `${config.publicPath}/${path.basename(config.paths.dist)}`,
    manifest: {},
});

if (process.env.NODE_ENV === undefined) {
    process.env.NODE_ENV = isProduction ? 'production' : 'development';
}
