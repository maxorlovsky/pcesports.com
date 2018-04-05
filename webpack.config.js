const path = require('path');
const webpack = require('webpack');

const ExtractTextPlugin = require('extract-text-webpack-plugin');
const globImporter = require('node-sass-glob-importer');

const config = {
    mode: 'development',
    entry: {
        bundle: ['./fe/src/main.js'],
        combined: ['./fe/styles/global.scss']
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, './public/dist')
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['env']
                    }
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {
                    loaders: {

                    }
                }
            },
            {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract([
                    {
                        loader: 'raw-loader'
                    },
                    {
                        loader: 'postcss-loader'
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            importer: globImporter()
                        }
                    }
                ])
            }
        ]
    },
    resolve: {
        extensions: ['.vue', '.js', '.html'],
        modules: [
            'node_modules'
        ],
        alias: {
            vue: 'vue/dist/vue.js'
        }
    },
    plugins: [
        new ExtractTextPlugin({
            filename: '[name].css'
        })
    ],
    optimization: {
        splitChunks: {
            cacheGroups: {
                commons: {
                    test: /[\\/]node_modules[\\/]/,
                    name: "vendors",
                    chunks: "all"
                }
            }
        }
    },
    devtool: "source-map"
};

module.exports = function (env = {}, argv) {
    if (env.dashboard) {
        const Dashboard = require('webpack-dashboard');
        const DashboardPlugin = require('webpack-dashboard/plugin');
        const dashboard = new Dashboard({ port: 9000 });
        config.plugins.push(
            new DashboardPlugin(dashboard.setData)
        );
    }

    return config;
};