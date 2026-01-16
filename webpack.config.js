const path = require('path');

module.exports = (env, argv) => {
  return {
    mode: argv.mode || 'development',
    entry: {
      loginTemplate: path.resolve(
        __dirname,
        './spa/admin/login-template/Main.jsx'
      ),
      loginLogActivity: path.resolve(
        __dirname,
        './spa/admin/login-logs-activity/Main.jsx'
      ),
      analytics: path.resolve(__dirname, './spa/admin/analytics/Main.jsx'),
      securityCore: path.resolve(
        __dirname,
        './spa/admin/security-core/Main.jsx'
      ),
      firewallMalware: path.resolve(
        __dirname,
        './spa/admin/firewall-malware/Main.jsx'
      ),
    },
    output: {
      filename: '[name].bundle.js',
      path: path.resolve(__dirname, './assets/admin/build'),
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
  };
};
