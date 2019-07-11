/* global __dirname, require, module, process */

import webpack from 'webpack';
import ProgressBarPlugin from 'progress-bar-webpack-plugin';
import WebpackNotifierPlugin from 'webpack-notifier';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import { CleanWebpackPlugin } from 'clean-webpack-plugin';
import path from 'path';

import { name } from './package.json';

const rootDir = __dirname;
const pluginDir = path.resolve(rootDir, '..');
const staticDir = path.resolve(pluginDir, 'static');
const devMode = process.env.NODE_ENV !== 'production';

export default {
  mode: process.env.NODE_ENV || 'development',
  entry: [
    'whatwg-fetch',
    path.resolve(rootDir, './src/index.scss'),
    path.resolve(rootDir, './src/index.js'),
  ],

  devtool: devMode ? 'source-map' : false,

  output: {
    path: staticDir,
    filename: `iola.js`,
    library: 'IOLA',
    libraryTarget: 'umd',
    umdNamedDefine: true,
  },

  externals: {
    "jquery": "jQuery",
  },

  module: {
    rules: [
      {
        test: /(\.js)$/,
        loader: 'babel-loader',
        exclude: /(node_modules)/,
      },
      {
        test: /\.(sa|sc|c)ss$/,
        use: [
          { loader: MiniCssExtractPlugin.loader },
          'css-loader',
          'sass-loader',
        ],
      },
      {
        test: /\.(png|gif|jpg|svg|eot|svg|ttf|woff|woff2)$/,
        use: 'url-loader?limit=20480&name=assets/[name]-[hash].[ext]',
      },
    ],
  },

  optimization: {
    minimize: !devMode,
  },

  plugins: [
    new MiniCssExtractPlugin({ filename: 'iola.css' }),
    new ProgressBarPlugin(),
    new WebpackNotifierPlugin({
      title: name,
      skipFirstNotification: true,
      alwaysNotify: true,
    }),
    new CleanWebpackPlugin(),
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify(process.env.NODE_ENV),
      }
    })
  ],
};
