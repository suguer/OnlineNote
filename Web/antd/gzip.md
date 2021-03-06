若编译后的文件过大,导致页面加载慢,可考虑插件compression-webpack-plugin,能使页面使用gzip引用资源,效率大大降低,
在config/plugin.config.js上
```php
const CompressionWebpackPlugin = require('compression-webpack-plugin');


if (process.env.NODE_ENV === 'production') {
    // 生产模式开启 20201204
    config.plugin('compression-webpack-plugin').use(
      new CompressionWebpackPlugin({
        // filename: 文件名称，这里我们不设置，让它保持和未压缩的文件同一个名称
        algorithm: 'gzip', // 指定生成gzip格式
        test: new RegExp('\\.(' + prodGzipList.join('|') + ')$'), // 匹配哪些格式文件需要压缩
        threshold: 10240, //对超过10k的数据进行压缩
        minRatio: 0.6, // 压缩比例，值为0 ~ 1
      })
    );
  }
```