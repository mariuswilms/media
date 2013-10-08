<?php
/**
 * Plugin Configuration File
 *
 * In order to make the plugin work you must include this file
 * within either your app’s `media.php` or `bootstrap.php`.
 *
 * To overwrite defaults you'll define constants before including this file,
 * and overwrite other settings selectively with `Configure::write()`
 * calls after including it.
 *
 * {{{
 *     define('MEDIA', ROOT . DS . 'media' . DS);
 *     require APP . 'plugins/media/config/media.php';
 *
 *     Configure::write('Media.filter.document.xs', array(
 *         'convert' => 'image/png',  'compress' => 9.6, 'zoomCrop' => array(16,16)
 *     ));
 * }}}
 *
 * Copyright (c) 2007-2013 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.config
 * @copyright  2007-2013 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 * @since      media 0.50
 */

/**
 * Directory paths
 *
 * You can also customize the directory locations or the URLs their contents
 * are served under. Each one of the basic three directory types can be
 * configured by defining constants.
 *
 * Example:
 * 	`'/var/www/example.org/htdocs/app/webroot/media/'`
 *
 * Directory Structure
 * -------------------
 * The plugin's components are capable of adapting to custom directory
 * structures by modifying the MEDIA_* path and URL constants. It's
 * recommended (but not required) to use the following structure in combination
 * with the plugin. Components of the plugin are by default expecting it to
 * be organized this way.
 *
 * - webroot/media: The base directory.
 * -- static: Files required by the application.
 * --- doc: Documents i.e. PDF.
 * --- gen: Everything else.
 * --- img: Images.
 * -- filter: Directory holding generated file versions from _static_ or _transfer_.
 * --- xs
 * --- ...other version names...
 * -- transfer; User uploaded files.
 * --- ...
 *
 * The _static_ directory and all it's content must be *readable* by the
 * effective user.  The _filter_ and the _transfer_ directory must be
 * *read/writable* by the effective user.
 *
 * You can initialize the directory structure from the command line with:
 * $ cake media init
 *
 * Transfer Directory
 * ------------------
 * To protect transferred files from becoming a security issue (most exploits
 * don't affect i.e. resized images) or for being able to password protect those
 * files you can use the following methods.
 *
 * 1. Put a .htaccess file in the directory (in case you're using Apache).
 *    {{{
 *      # webroot/media/transfer/.htaccess
 *      Order deny,allow
 *      Deny from all
 *    }}}
 *
 * 2. Create the .htaccess file from the shell with:
 *    $ cake media protect
 *
 * 3. Relocate the transfer directory by defining the following constants.
 *    The first definition points to the new location of the directory, the second
 *    disables generation of URLs for files below the transfer directory.
 *    {{{
 *        define('MEDIA_TRANSFER', APP . 'transfer' . DS);
 *        define('MEDIA_TRANSFER_URL', false);
 *    }}}
 *
 * @see MediaHelper::webroot()
 */
if (!defined('MEDIA')) {
	define('MEDIA', WWW_ROOT . 'media' . DS);
}
if (!defined('MEDIA_STATIC')) {
	define('MEDIA_STATIC', MEDIA . 'static' . DS);
}
if (!defined('MEDIA_FILTER')) {
	define('MEDIA_FILTER', MEDIA . 'filter' . DS);
}
if (!defined('MEDIA_TRANSFER')) {
	define('MEDIA_TRANSFER', MEDIA . 'transfer' . DS);
}

/**
 * URL paths
 *
 * Each constant is defined with a value which is an (slash terminated) URL
 * path fragment relative to your webroot. In case the corresponding directory
 * isn't served use `false` as a value.
 *
 * To get arround limited browser pipelining of media you can use the special
 * placeholder `%d` in the definition of the URL. The placeholder gets replaced
 * within the media helper.
 *
 * @see MediaHelper::webroot()
 */
if (!defined('MEDIA_URL')) {
	define('MEDIA_URL', 'media/');
}
if (!defined('MEDIA_STATIC_URL')) {
	define('MEDIA_STATIC_URL', MEDIA_URL . 'static/');
}
if (!defined('MEDIA_FILTER_URL')) {
	define('MEDIA_FILTER_URL', MEDIA_URL . 'filter/');
}
if (!defined('MEDIA_TRANSFER_URL')) {
	define('MEDIA_TRANSFER_URL', MEDIA_URL . 'transfer/');
}

/**
 * Bootstrap the `mm` library.
 */
require dirname(dirname(__FILE__)) . '/libs/mm/bootstrap.php';
