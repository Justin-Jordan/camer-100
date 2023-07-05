const path = require('path');
const Encore = require('@symfony/webpack-encore');

const [bitbagCmsShop, bitbagCmsAdmin] = require('./vendor/bitbag/cms-plugin/webpack.config.js');
const [bitbagWishlistShop, bitbagWishlistAdmin] = require('./vendor/bitbag/wishlist-plugin/webpack.config.js');

const syliusBundles = path.resolve(__dirname, 'vendor/sylius/sylius/src/Sylius/Bundle/');
const uiBundleScripts = path.resolve(syliusBundles, 'UiBundle/Resources/private/js/');
const uiBundleResources = path.resolve(syliusBundles, 'UiBundle/Resources/private/');

// Shop config
Encore
  .setOutputPath('public/build/shop/')
  .setPublicPath('/build/shop')
  .addEntry('shop-entry', './assets/shop/entry.js')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .copyFiles({
    from: 'vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private/img',
    to: '../../assets/shop/img/[path][name].[ext]',
    includeSubdirectories: true,
    pattern: /.*/,
  })
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader();

const shopConfig = Encore.getWebpackConfig();

shopConfig.resolve.alias['sylius/ui'] = uiBundleScripts;
shopConfig.resolve.alias['sylius/ui-resources'] = uiBundleResources;
shopConfig.resolve.alias['sylius/bundle'] = syliusBundles;
shopConfig.name = 'shop';

Encore.reset();

// Molla Shop config
Encore
  .setOutputPath('public/_themes/camer100/molla/build/shop/')
  .setPublicPath('/_themes/camer100/molla/build/shop/')
  .addEntry('molla-shop-entry', './assets/molla/shop/entry.js')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .copyFiles({
    from: 'themes/Molla/public/assets/images',
    to: '../../assets/molla/shop/img/[path][name].[ext]',
    includeSubdirectories: true,
    pattern: /.*/,
  })
  .copyFiles([
    {from: './node_modules/ckeditor4/', to: 'ckeditor/[path][name].[ext]', pattern: /\.(js|css)$/, includeSubdirectories: false},
    {from: './node_modules/ckeditor4/adapters', to: 'ckeditor/adapters/[path][name].[ext]'},
    {from: './node_modules/ckeditor4/lang', to: 'ckeditor/lang/[path][name].[ext]'},
    {from: './node_modules/ckeditor4/plugins', to: 'ckeditor/plugins/[path][name].[ext]'},
    {from: './node_modules/ckeditor4/skins', to: 'ckeditor/skins/[path][name].[ext]'},
    {from: './node_modules/ckeditor4/vendor', to: 'ckeditor/vendor/[path][name].[ext]'}
])

  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader();

const mollaShopConfig = Encore.getWebpackConfig();

mollaShopConfig.resolve.alias['sylius/ui'] = uiBundleScripts;
mollaShopConfig.resolve.alias['sylius/ui-resources'] = uiBundleResources;
mollaShopConfig.resolve.alias['sylius/bundle'] = syliusBundles;
mollaShopConfig.name = 'molla_shop';

Encore.reset();

// Admin config
Encore
  .setOutputPath('public/build/admin/')
  .setPublicPath('/build/admin')
  .addEntry('admin-entry', './assets/admin/entry.js')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader();

const adminConfig = Encore.getWebpackConfig();

adminConfig.resolve.alias['sylius/ui'] = uiBundleScripts;
adminConfig.resolve.alias['sylius/ui-resources'] = uiBundleResources;
adminConfig.resolve.alias['sylius/bundle'] = syliusBundles;
adminConfig.externals = Object.assign({}, adminConfig.externals, { window: 'window', document: 'document' });
adminConfig.name = 'admin';

module.exports = [shopConfig, adminConfig, bitbagCmsShop, bitbagCmsAdmin, bitbagWishlistShop, bitbagWishlistAdmin, mollaShopConfig];
