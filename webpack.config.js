//SET "NODE_ENV=production" && webpack
const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
//const CompressionPlugin = require("compression-webpack-plugin");

const NODE_ENV = process.env.NODE_ENV || 'development';
const dirname = NODE_ENV === 'development' ? 'dev' : 'prod';
const entry = './src/frontend/';
const WebpackConfigConst = {
   /* PathToUXAssessmentConfig: 'https://api.vigo.ru/',
    url2: 'https://api.vigo.ru/' */
};


module.exports = {
/*    devServer: {
        inline: true,
        contentBase: './src',
        port: 3000
    },*/
    devtool: NODE_ENV === 'development' ? 'cheap-module-eval-source-map' : 'nosources-source-map',
    entry: entry + 'index.js',
    output: {
        path: __dirname+'/'+dirname+'/',
        filename: 'js/bundle.min.js',
        library: NODE_ENV === 'development' ? 'DEV' : 'PROD',
    },
    /* watch: NODE_ENV === 'development',
    watchOptions: {
        aggregateTimeout: 100
    },  */
    module: {
        rules: [
            {
                test: /\.js$/,
                use: ['babel-loader'],
                exclude: /node_modules/
            },
        /*  {
                test: /\.css/,
                loader: 'style-loader!css-loader!autoprefixer-loader'
            },*/
            {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract({
                  fallback: 'style-loader',
                  //resolve-url-loader may be chained before sass-loader if necessary
                  use: ['css-loader', "autoprefixer-loader", 'sass-loader']
                }),
                exclude: /node_modules/
            },
         /*   {
                test: /\.gif$/,
                loader: "url-loader?limit=10000&mimetype=image/gif"
            },
            {
                test: /\.jpg$/,
                loader: "url-loader?limit=10000&mimetype=image/jpg"
            },
            {
                test: /\.png$/,
                loader: "url-loader?limit=10000&mimetype=image/png"
            },
            {
                test: /\.svg/,
                loader: "url-loader?limit=26000&mimetype=image/svg+xml"
            }, */
            {
                test: /\.jsx$/,
                use: [
                    "react-hot-loader",
                    "babel-loader"
                ],
                exclude: [/node_modules/, /public/]
            },


        ]
    },
    plugins: [
        new webpack.NoEmitOnErrorsPlugin(),
        new webpack.EnvironmentPlugin({
          NODE_ENV: 'development', // use 'development' unless process.env.NODE_ENV is defined
          DEBUG: false
        }),
        new webpack.DefinePlugin({
          cutCode: JSON.stringify(true),
          WebpackConfigConst: JSON.stringify(WebpackConfigConst),
        }),
        new webpack.optimize.UglifyJsPlugin({
            sourceMap: true,
            beautify : false,
            comments : false,
            compress : {
                sequences   : true,
                booleans    : true,
                loops       : true,
                unused      : true,
                warnings    : false,
                drop_console: true,
                unsafe      : true
            }
        }),

        new ExtractTextPlugin("css/styles.css", {allChuncks: true}),

      /*  new CompressionPlugin({
            asset: "[path].gz[query]",
            algorithm: "gzip",
            test: /\.js$|\.html$/,
            threshold: 10240,
            minRatio: 0.8
        }), */
    ]
};