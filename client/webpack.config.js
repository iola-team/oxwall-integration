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
  entry: {
      iola: [
        path.resolve(rootDir, './src/index.scss'),
        path.resolve(rootDir, './src/index.js'),
      ],

      vendor: [
        'whatwg-fetch',

        /**
         * Web Components polyfill
         */
        '@webcomponents/webcomponentsjs/custom-elements-es5-adapter',
        '@webcomponents/webcomponentsjs/webcomponents-loader',
      ],
  },

  devtool: devMode ? 'source-map' : 'none',

  output: {
    path: staticDir,
    filename: `[name].js`,
    library: 'IOLA',
    libraryTarget: 'umd',
    umdNamedDefine: true,
  },

  externals: {
    jquery: 'jQuery',
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
        use: [
          'file-loader'
        ],
      },
    ],
  },

  optimization: {
    minimize: !devMode,
  },

  plugins: [
    new MiniCssExtractPlugin({ filename: '[name].css' }),
    new ProgressBarPlugin(),
    new WebpackNotifierPlugin({
      title: name,
      skipFirstNotification: true,
      alwaysNotify: true,
    }),
    new CleanWebpackPlugin({
        cleanOnceBeforeBuildPatterns: ['**/*', '!.gitkeep'],
    }),
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify(process.env.NODE_ENV),
      }
    })
  ],
};
