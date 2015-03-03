## Required software

- Install NPM and Grunt
- Install xgettext ([Installer for Windows](http://mlocati.github.io/gettext-iconv-windows/))

## First time setup

- Run `npm install`
- Create the .transifex file with your credentials to wp-translations.org (if you need to update translation files only)

## Grunt tasks

### `grunt tx-push`

Use this when you have finished developing something and need to update the translation repository for translators.

- Checks that the proper text domain is used in the plugin.
- Create POT file from source code
- Push POT file to wp-translations.org
 
### `grunt tx-pull` 

Use this when translators have finished working and you need to update the PO/MO files in the plugin folder. 

- Push latest PO files from wp-translations.org
- Compile PO files to MO files

