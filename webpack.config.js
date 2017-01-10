var webpack = require('webpack');
var path = require('path');

var build = path.resolve(__dirname, 'web/assets/build');
var assets = path.resolve(__dirname, 'web/assets');

var config = {
	entry: assets + '/js/webpack.jsx',
	output: {
		path: build+'/js',
		filename: 'bundle.js'
	},
	module: {
		loaders : [{
			test : /\.jsx?/,
			include : assets,
			loader : 'babel'
		}]
	}
};

module.exports = config;