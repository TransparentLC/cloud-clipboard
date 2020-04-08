module.exports = {
    publicPath: '',
    integrity: true,
    transpileDependencies: [
        "vuetify",
    ],
    pluginOptions: {
        webpackBundleAnalyzer: {
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