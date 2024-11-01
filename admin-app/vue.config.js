const { defineConfig } = require('@vue/cli-service');
module.exports = defineConfig({
  transpileDependencies: true,
  filenameHashing: false,
  productionSourceMap: false,

  publicPath:
    process.env.NODE_ENV === 'production'
      ? './' // Serve assets relative to the index.html
      : '/',

  devServer: {
    proxy: {
      '/wp-': {
        target: 'http://scd.localdev',
        changeOrigin: true,
      },
      '/getApi.php': {
        target: 'http://scd.localdev/wp-content/plugins/webmaster-user-role',
        changeOrigin: true,
      },
    },
  },
  assetsDir: 'static',
});
