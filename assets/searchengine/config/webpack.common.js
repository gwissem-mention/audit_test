var webpack = require('webpack');
var HtmlWebpackPlugin = require('html-webpack-plugin');
var ExtractTextPlugin = require('extract-text-webpack-plugin');
var helpers = require('./helpers');
var autoprefixer = require('autoprefixer');
var parameters = require('./parameters.json');

module.exports = {
    entry: {
        'polyfills': './src/polyfills.ts',
        'vendor': './src/vendor.ts',
        'app': [
            './../../web/bundles/fosjsrouting/js/router.js',
            './../../web/js/fos_js_routes.js',
            './src/main.ts'
        ]
    },

    resolve: {
        extensions: ['.ts', '.js']
    },

    module: {
        rules: [
            {
                test: /\.ts$/,
                loaders: [
                    '@angularclass/hmr-loader',
                    {
                        loader: 'awesome-typescript-loader',
                        options: { configFileName: helpers.root('src', 'tsconfig.json') }
                    } , 'angular2-template-loader'
                ]
            },
            {
                test: /\.html$/,
                loader: 'html-loader'
            },
            {
                test: /\.(png|jpe?g|gif|svg|woff|woff2|ttf|eot|ico)$/,
                loader: 'file-loader?name=assets/[name].[hash].[ext]'
            },
            {
                test: /\.css$/,
                exclude: helpers.root('src', 'app'),
                loader: ExtractTextPlugin.extract({ fallbackLoader: 'style-loader', loader: ['css-loader?sourceMap', 'postcss-loader'] })
            },
            {
                test: /\.css$/,
                include: helpers.root('src', 'app'),
                loader: 'raw-loader!postcss-loader'
            },

            // support for .scss files
            // use 'null' loader in test mode (https://github.com/webpack/null-loader)
            // all css in src/style will be bundled in an external css file
            {
                test: /\.(scss|sass)$/,
                exclude: helpers.root('src', 'app'),
                loader: ExtractTextPlugin.extract({ fallbackLoader: 'style-loader', loader: ['css-loader', 'postcss-loader', 'sass-loader']})
            },
            // all css required in src/app files will be merged in js files
            {
                test: /\.(scss|sass)$/,
                exclude: helpers.root('src', 'assets'),
                loader: 'raw-loader!postcss-loader!sass-loader'
            },
        ]
    },

    plugins: [
        // Workaround for angular/angular#11580
        new webpack.ContextReplacementPlugin(
            // The (\\|\/) piece accounts for path separators in *nix and Windows
            /angular(\\|\/)core(\\|\/)(esm(\\|\/)src|src)(\\|\/)linker/,
            helpers.root('./src'), // location of your src
            {} // a map of your routes
        ),

        new webpack.optimize.CommonsChunkPlugin({
            name: ['app', 'vendor', 'polyfills']
        }),

        new HtmlWebpackPlugin({
            template: 'src/index.html'
        }),

        new webpack.LoaderOptionsPlugin({
            options: {
                postcss: [
                    autoprefixer({
                        browsers: ['last 2 version']
                    })
                ]
            }
        }),

        new webpack.DefinePlugin({
            'process.env': {
                'ENGINE_HOST': JSON.stringify(parameters.engineHost)
            }
        }),
    ]
};
