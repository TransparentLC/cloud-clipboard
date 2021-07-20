module.exports = {
    outputDir: 'dist',
    publicPath: '',
    integrity: true,
    transpileDependencies: [
        'vuetify',
    ],
    pluginOptions: {
        webpackBundleAnalyzer: {
            analyzerMode: 'disabled',
            openAnalyzer: false,
        },
    },
    devServer: {
        port: 1210,
        proxy: {
            '/*': {
                target: 'http://localhost:9501/',
                changeOrigin: true,
            },
        },
    },
    productionSourceMap: false,
}