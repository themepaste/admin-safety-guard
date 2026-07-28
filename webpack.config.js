const path = require('path');

module.exports = (env, argv) => {
  // Default to a production build. The previous config defaulted to
  // 'development', which shipped unminified, eval()-wrapped bundles (~1.2 MB
  // each, 9.4 MB total) to every admin page.
  const mode = argv.mode || 'production';
  const isProduction = mode === 'production';

  return {
    mode,
    // eval-based source maps inline the whole source and trip security
    // scanners looking for eval() in distributed code. Never ship them.
    devtool: isProduction ? false : 'source-map',
    entry: {
      loginTemplate: path.resolve(
        __dirname,
        './spa/admin/login-template/Main.jsx',
      ),
      loginLogActivity: path.resolve(
        __dirname,
        './spa/admin/login-logs-activity/Main.jsx',
      ),
      analytics: path.resolve(__dirname, './spa/admin/analytics/Main.jsx'),
      securityCore: path.resolve(
        __dirname,
        './spa/admin/security-core/Main.jsx',
      ),
      firewallMalware: path.resolve(
        __dirname,
        './spa/admin/firewall-malware/Main.jsx',
      ),
      privacyHardening: path.resolve(
        __dirname,
        './spa/admin/privacy-hardening/Main.jsx',
      ),
      twoFAUsingMobileApp: path.resolve(
        __dirname,
        './spa/admin/2fa-using-mobile-app/Main.jsx',
      ),
    },
    output: {
      filename: '[name].bundle.js',
      path: path.resolve(__dirname, './assets/admin/build'),
      // Drop stale bundles from previous builds instead of leaving them to be
      // shipped in the release zip.
      clean: true,
    },
    optimization: {
      // React + ReactDOM were previously duplicated into all seven bundles.
      // Hoisting them into one shared file means the browser downloads and
      // parses the framework once for the whole plugin.
      runtimeChunk: 'single',
      splitChunks: {
        cacheGroups: {
          framework: {
            test: /[\\/]node_modules[\\/](react|react-dom|scheduler)[\\/]/,
            name: 'framework',
            chunks: 'all',
            enforce: true,
          },
        },
      },
    },
    module: {
      rules: [
        {
          test: /\.jsx?$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-react', '@babel/preset-env'],
              cacheDirectory: true,
            },
          },
        },
        {
          test: /\.css$/i,
          use: ['style-loader', 'css-loader'],
        },
      ],
    },
    resolve: {
      extensions: ['.js', '.jsx'],
    },
    // Page-specific chart/icon libraries legitimately exceed the default
    // 244 KB advice; the warning is noise on every build.
    performance: { hints: false },
  };
};
