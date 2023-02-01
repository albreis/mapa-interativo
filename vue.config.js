const { defineConfig } = require('@vue/cli-service')
const chunkPrefix = '[name]'
module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: process.env.NODE_ENV === 'production'
    ? '/wp-content/plugins/mapa-interativo/dist/'
    : '/',
  chainWebpack: (config) => {
    config.module
      .rule('images')
      .use('url-loader')
      .tap(options => Object.assign({}, options, { name: `${chunkPrefix}.[ext]` }));
  },
  css: {
    extract: {
      filename: `${chunkPrefix}.css`,
      chunkFilename: `${chunkPrefix}.css`,
    },
  },
  configureWebpack: {
    output: {
      filename: `${chunkPrefix}.js`,
      chunkFilename: `${chunkPrefix}.js`,
    }
  },
})
