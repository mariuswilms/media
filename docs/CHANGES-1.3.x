
Changes & Migration Instructions for 1.3.x
==========================================

This page lists all changes necessary for migrating to a new version of the
plugin. Please note that instructions for migrations to non-stable versions are
still in-flux.

What used to be Medium is now Media
-----------------------------------
Medium Helper has been renamed to Media Helper. Please update all occurrences
of @$medium->...@ in your templates to @$media->...@ and change the name of the
helper in your helpers property to @$helpers = array('Media.Media', ...);@.

Media Behavior has been split up
--------------------------------
The behavior has been split into 3 behaviors to allow for independent
development of each and a clear separation of concerns. 

The *generator behavior* is responsible of generating different version of an
incoming file it inherits the @filterDirectory@, @baseDirectory@ and
@createDirectory@ settings from Media Behavior.

The *coupler behavior* couples files with records. 

The *meta behavior* allows for retrieving metadata of files and inherits the
@metadataLevel@ setting which is now being called @level@.

Generator Behavior
-------------------
The generator behavior uses a callback named @makeVersion()@ in favor of the
old @beforeMake()@ from the media behavior.

Transfer Behavior
-----------------
Several methods have been renamed to not clash with other model methods and to
communicate their purpose better. The transfer method has been revamped to also
handle preparation of transfers. 

@perform()@ has been deprecated. Its successor is @transfer()@ which takes a
second parameter to handle preparation and returns the destination of the
transfer on success.

@prepare()@ has been deprecated in favor of the updated @transfer()@.

@getLastTransferredFile()@ has been renamed to @transferred()@.

The generation of a custom transfer destination path is now being handled by
the new @transferTo()@ method. Implement the method in your model to control
path generation.

The @baseDirectory@ setting has been renamed to @transferDirectory@ to provide
consistency and less confusion; see http://github.com/davidpersson/media/issues#issue/28.

Media Helper
------------
The helper learned HTML5 and now supports audio and video elements.

With the introduction of HTML5 support the @embed()@ method has largely been
rewritten from scratch. The method now produces object tag free markup; for
embeding media using the object syntax use the @embedAsObject()@ method.
 
While removing support for assets the @link()@ method has been removed. Use the
built-in one from the html helper.

The @file()@ method has been totally simplified and its path-expanding magic
mostly removed. The new docblock reads as follows.

  >> Resolves partial path to an absolute path by trying to find an existing file matching the
     pattern `{<base path 1>, <base path 2>, [...]}/<provided partial path without ext>.*`.
     The base paths are coming from the `_paths` property.
     
     img/cern                 >>> MEDIA_STATIC/img/cern.png
     img/mit.jpg              >>> MEDIA_TRANSFER/img/mit.jpg
     s/<...>/img/hbk.jpg      >>> MEDIA_FILTER/s/<...>/img/hbk.png

Libraries moved 
---------------
With the addition of the _libs_ directory to CakePHP all non-vendor libraries
included in the plugin have been moved from the _vendors_ directory into the
_libs_ directory. If you've been loading i.e. the @MediaValidation@ class via
@App::import('Vendor', 'Media.MediaValidation'`);@ you now would use
@App::import('Lib', 'Media.MediaValidation'`);@.

New Media Processing Library
----------------------------
The processing library has been renamed, entirely overhauled and is packaged
separately with the release of 1.3. It will by default be included in the
plugin distribution. The classes now part of the *mm* library have always been
coupled loosely to CakePHP and it is a logical step to provide them in their own
package. This way other frameworks or apps which don't utilize Cake can make
use of the provided functionality more easily.

@Media@ has been split into @Media_Process@ and @Media_Info@. Load them via
@App::import('Lib', 'Media_Process', array('file' =>
'mm/src/Media/Process.php'));@ and @App::import('Lib', 'Media_Info,
array('file' => 'mm/src/Media/Info.php'));@. Both classes _must_ be configured
prior to using them (see the plugin core.php config for defaults). 

@MimeType@ has been renamed to @Mime_Type@. Load it via @App::import('Lib',
'Mime_Type', array('file' => 'mm/src/Mime/Type.php'));@. The class must be
configured prior to using it (see the plugin core.php config for defaults).

For more information visit the "wiki pages of the _mm_
library":http://wiki.github.com/davidpersson/mm/.

Default Configuration 
---------------------
The provided default configuration has been simplified in order to be less
confusing on the first look. If you've been relying on the default
configuration (i.e. the filters) you need to check if the changes affect you.

Asset support removed
---------------------
As I've learned about the difference between assets and media, support for the
former has been removed. This means the helper and generator won't i.e.
process icons anymore.

