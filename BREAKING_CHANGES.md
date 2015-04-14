# Breaking changes  

## Introduced in 6.0

- "Themes" have been renamed "skins" to avoid confusion with WordPress themes. If you have any custom skin, in 
  `wp-content/customer-area/themes` or in `wp-content/themes/my-theme/customer-area/themes` please rename the last 
  folder to `skins` instead of `themes`
- PHP source code is now in `customer-area/src/php` instead of `customer-area/includes`
- Javascript source code is now in `customer-area/src/js` instead of `customer-area/js`
