var fs = require('fs');

module.exports = {
  publicPath: "/engine/shopitemlist/",
  runtimeCompiler: true,

  css: {
    extract: false
  },
  outputDir: "../../engine/shopitemlist",
  assetsDir: undefined,
  productionSourceMap: undefined,
  parallel: true,
  devServer: {
    https: {
      key: fs.readFileSync(
        "/Users/manfred/Documents/certs/localhost/private.key"
      ),
      cert: fs.readFileSync(
        "/Users/manfred/Documents/certs/localhost/private.pem"
      )
    },
    host: "localhost",
    allowedHosts: [".localhost"],
    headers: {
      "Access-Control-Allow-Origin": "*",
      "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
      "Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
    }
    // use requestly redirect:
    // /^(http://.*\.localhost)(.*\.hot-update\.(js|json))$/ ➜ http://localhost:8080$2
  }
};
