"use strict";

const webpack = require("webpack");
const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const Dotenv = require('dotenv-webpack');
const TerserPlugin = require("terser-webpack-plugin");

let config = {
  entry: {
    main: ["./resources/app.js", './resources/stylesheets/app.css', './resources/stylesheets/app.scss'],
  },
  output: {
    path: path.resolve(__dirname, "public", "assets", "bundle"),
    filename: "[name].bundle.js",
  },
  resolve: {
    extensions: [".js", ".jsx", ".json", ".ts", ".tsx"],
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx|tsx|ts)$/,
        exclude: path.resolve(__dirname, "node_modules"),
        use: {
          loader: "babel-loader",
          options: {
            presets: [
              "@babel/preset-env",
              "@babel/preset-react",
              "@babel/preset-typescript",
            ],
            plugins: [
              "@babel/plugin-transform-runtime",
              ["@babel/plugin-proposal-decorators", { legacy: true }],
              [
                "@babel/plugin-proposal-private-property-in-object",
                { loose: true },
              ],
              "@babel/plugin-syntax-dynamic-import",
              ["@babel/plugin-proposal-private-methods", { loose: true }],
              ["@babel/plugin-proposal-class-properties", { loose: true }],
            ],
          },
        },
      },
      {
        test: /\.s[ac]ss$/i,
        use: [MiniCssExtractPlugin.loader, "sass-loader"],
        use: [
          // fallback to style-loader in development
          process.env.NODE_ENV !== "production"
            ? "style-loader"
            : MiniCssExtractPlugin.loader,
          // Translates CSS into CommonJS
          "css-loader",
          // Compiles Sass to CSS
          "sass-loader",
        ],
      },
      {
        test: /\.css$/i,
        use: [MiniCssExtractPlugin.loader, "css-loader"],
      },
      {
        test: /.(png|woff(2)?|eot|ttf|svg|gif)(\?[a-z0-9=\.]+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '../css/[hash].[ext]'
            }
          }
        ]
      }
    ],
  },
  plugins: [
    new Dotenv(),
    new MiniCssExtractPlugin(),
    new webpack.DefinePlugin({
      __DEV__: JSON.stringify(true),
      __API_HOST__: JSON.stringify("http://localhost/api/"),
    }),
  ],
  optimization: {
    minimize: true,
    minimizer: [new TerserPlugin()]
  }
};

module.exports = config;
