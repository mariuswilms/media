 MEDIA PLUGIN for CAKEPHP
==============================================================================

 * Introduction
 * Features
 * Depend
 * Download
 * Install

 * TransferBehavior
   * Setup
   * Usage
   * Validation
 * MediaBehavior
   * Setup
   * Usage
 * Chaining TransferBehavior and MediaBehavior
 * MediumHelper
 * ManageShell
 * Medium
   * Adapters
 * MimeType

 * Credits
 * Known bugs
 * Bug reporting


 Introduction
-------------------------------------------------------------------------------
  Media plugin for CakePHP is a toolbox for media handling.

  Until 0.42 it was part of the original Attachments package. 
  Since 0.50 it is one out of two plugins from the attm project.
  
  "attm" refers to the components used throughout the project:
  
  a    attachment
   t   transfer
    t  tautology
     m medium

  All components of this plugin may be more or less independently used from
  each other.
  

 Features
-------------------------------------------------------------------------------
 * File Transfer with support for HTTP, local files or data from a HTML file form
 
 * Mime type detection by extension and content signatures 
 
 * Transparent retrieval of file metadata
 
 * File manipulation with flexible backends (e.g. Imagick and GD)
 
 * Optional coupling of records of database table with files in a filesystem 
 
 * A shell task for table/filesystem synchronization 
 
 * A shell task for generating multiple versions of multiple files
 
 * A helper to embed media files easily into your markup 


 Depend
-------------------------------------------------------------------------------
  Until CakePHP 1.2 stable isn't released this plugin is intended to run with 
  the latest cake 1.2 version from the 1.2.x svn branch.
 
  Required
  --------
 * OS
   tested with GNU/Linux

 * Webserver
   tested with Apache 2.2.8
   
 * PHP >=5.2.x
   tested with 5.2.6RC6
   
 * ctype extension
    
 * CakePHP 1.2

  Optional
  --------
 * fileinfo extension OR mime_magic extension 
   tested with 1.0.4
     
 * Imagick extension or Gd extension
   
 * ffmpeg php extension
   tested with 0.5.1
   
 * File_Ogg pear package
   tested with 0.3.0beta
   
 * MP3_Id pear package
   1.2.0stable
   
 * Text_Statistics pear package
   tested with 1.0stable
   
 * CssTidy
 
 * JsMin


 Download
-------------------------------------------------------------------------------
  Download the package from http://cakeforge.org/frs/?group_id=227
  Make sure you select the latest release.
  
  You may also want to do an anonymous checkout from svn:
  "svn co https://svn.cakeforge.org/svn/attm/trunk/0.5x/plugins/media /path/to/save"


 Install
-------------------------------------------------------------------------------
  The components of the plugin rely on certain configuration values and a 
  modified webroot layout.
  
  Step 1
  ------
  Extract the contents of the archive (if you haven't already done so)  
 
  Step 2
  ------
  Put the extracted directory into your app's plugin directory  
 
  Step 3
  ------
  Add default plugin configuration by appending this line to your app's core.php:
  	...
  	require_once(APP.'plugins'.DS.'media'.DS.'config'.DS.'core.php');
  
  Step 4
  ------
  Define MEDIA and MEDIA_URL in your config/bootstrap.php or config/core.php:
    
  		define('MEDIA','/path/to/your/app/webroot/media/');
  		define('MEDIA_URL','media/');
   
  
  Step 5
  ------
  Create this directory structure below your webroot:
  
  - webroot
  	- media
      - filter
      - static
        - css
        - doc
        - gen
        - ico
        - img
        - js
      - transfer
  
   The static directory and all it's content must be readable by the webserver user.
   The filter and the transfer directory must be read/writable by the webserver user.    

  Step 6
  ------
  To speedup the loading of media it's recommended that you move media below 
  your webroot and optionally below your vendors and plugins directories 
  to the corresponding ones in webroot/media/static/ :
  
  webroot/js/*          >>> webroot/media/static/js/
  webroot/css/*         >>> webroot/media/static/css/
  webroot/img/*         >>> webroot/media/static/img/ 
  webroot/vendors/js/*  >>> webroot/media/static/js/
  webroot/vendors/css/* >>> webroot/media/static/css/
  webroot/vendors/img/* >>> webroot/media/static/img/ 

  The files can later easily be referenced/embeded/linked with the 
  Medium Helper. 
  
  Step 7
  ------
  Include MediumHelper in your AppController's helpers property:
  
	  class AppController extends Controller {
		...
	 	var $helpers = array( ..., 'Media.Medium');
		...  
  
  Step 8
  ------
  Replace any occurences of $html->css() and $html->js() in your views
  by it's MediumHelper counterparts:
  	
  	$html->css('cake.generic')        >>> $medium->link('css/cake.generic');
  	$javascript->link('jquery')       >>> $medium->link('js/jquery');
  	$javascript->link('proto', false) >>> $medium->link('js/proto', false);
    $html->img('meme.png')            >>> $medium->embed('img/meme');  
 
 
 TransferBehavior
-------------------------------------------------------------------------------

  Setup
  -----
  TransferBehavior needs to be told where to store transferred files.
  This is done by specifying "destinationFile" like this:
  
	var $actsAs = array(
		'Media.Transfer' => array(
								'destinationFile' => ':MEDIA:transfer:DS::Medium.short::DS::Source.basename:',
								'createDirectories' => true,
								...
								),
		...
							); 
							
  The destination directory must be existant and writable by the server.
  As you see markers are used in this configuration option.
  Please see the section on markers below to get more info.

  Usage
  -----
  The behavior is triggered on save operations only by values submitted in 
  a field named "file": 
  
	$data =	array(
				'Example' => array(
									...
									'file' => ...
									...
									)
			);

  The contents of the field are the source from which we transfer to
  "destinationFile".
  Where valid values of this field are:
  
  a) Array generated by an upload through a html form:
	
		...
		'file' => array(
						'name' => 'cern.jpg',
						'type' => 'image/jpeg',
						'tmp_name' => '/tmp/32ljsdf',
						'error' => 0,
						'size' => 49160
						),
		...

  b) String containing an absolute path to a file in the filesystem: 
		
		...
		'file' => '/var/www/tmp/cern.jpg'
		...  

  c) String containing a full url:

		...
		'file' => 'http://www.cakephp.org/imgs/logo.png'
		...

  Validation [draft]
  ----------
  You may add more paths (to temp directories) to the allowPaths option 
  depending on how your system is setup.
  
  Markers
  ------------------------------------------------------------------------------
  Where stated these markers can be used in configuration options.
  They get replaced during runtime by the marker class. 
 
	:DS:                    Directory seperator "/" or "\"
	:WWW_ROOT:              Path to webroot of this app
	:APP:                   Path to your app
	:TMP:                   Path to app's tmp directory
	:MEDIA:                 Path to your media root
	:uuid:            	    An uuid generated by String::uuid()
	:day:					The current day
	:month:					The current month
	:year:					The current year
	:Model.name:
	:Model.alias:
	:Model.xyz:             Where xyz is a field of the submitted record
	:Source.basename:       e.g. logo.png
	:Source.filename:       e.g. logo
	:Source.extension:      e.g. png
	:Source.mimeType:       e.g. image_png
	:Medium.name:           Medium name of the source file (e.g. image)
	:Medium.short:          Short medium name of the source file (e.g. img)
 
   If you need more markers add them in the behaviors prepare method.
  
  
 MediaBehavior
-------------------------------------------------------------------------------

  Setup
  -----
  MediaBehavior doesn't need any configuration in the model it uses the 
  "MEDIA" constant value internally.
  
  This Behavior needs a specific table schema which you'll find in the config/sql
  directory. Init your table with this schema to utilize the behavior.

	var $actsAs = array(
		'Media.Media' => array(
								'makeVersions' => true,
								'metadataLevel' => 2,
								'createDirectory' => true,
								...
								),
		...
							); 

  To configure the behavior to make versions of a file (creating different versions of a file)
  set "makeVersions" to "true". Versions are only made if the save operation creates a new 
  record.
  The "metdataLevel" options specifies how much information about a file should be retrieved.
  Set it to 0 if you want to disable this feature.
  If "createDirectories" is set to "true" missing directories will be created on the fly.  
  
  Usage
  -----  
  On save operations the behavior acts upon values submitted in field named "file":
  
  	array(
			'Example' => array(
								...
								'file' => ...
								...
								)
			);
				

  The field must contain an absolute path to a file in the filesystem: 

		...
		'file' => '/var/www/tmp/cern.jpg'
		...  

  On save the file is checksummed once using an md5 hash of the files contents. 

  On find operations the behavior acts upon "dirname" and "basename" fields.
  Where "dirname" must be a path relative to MEDIA and "basename" a 
  filename including extension of a file existent in that directory.

  Assumed some files have already been linked to rows of a table, 
  a find may result in:
   
  	array(
			'Example' => array(
								'id' => 2,
								'dirname' => 'files/science',
								'basename' => 'cern.jpg',
								'checksum' => '9e496bcf9f601a7501b3efaf2b19da15'
			                    'created' => '2008-01-21 16:28:33'
			                    'modified' => '2008-01-21 16:28:33'								
		            			'size' => 49160,
			                    'mime_type' => 'image/jpeg',
			                    'width' => 640,
			                    'height' => 480,
			                    'ratio' => '4:3',
			                    'megapixel' => 0,
			                    'quality' => 0,
								)
			);
			
  The type and number of virtual fields added by the behavior depends on the
  type of the referenced file as well as installed libraries/extensions.
  Virtual fields are being cached preventing reading in each file on every find.
  
   
 Chaining TransferBehavior and MediaBehavior
-------------------------------------------------------------------------------
  To connect TransferBehavior and MediaBehavior with each other it is 
  important to specify TransferBehavior before MediaBehavior in the $actsAs
  property of your model: 
  
	var $actsAs = array(
					...
					'Media.Transfer',
					'Media.Media',
					...
					);
								  
  "destinationFile" of TransferBehavior should then point to a file below 
  MEDIA. This is already the case if you're using the default plugin 
  configuration.
  

 MediumHelper
-------------------------------------------------------------------------------
  This helper class includes some methods to deal with the new directory layout.
  The methods are thought as replacements for some of the methods found in
  HtmlHelper and JsHelper. 
    
  Additionally the helper is able to render widgets to embed video and audio files.
  
  Methods take a path to a file as a parameter. This may be either
  a path relative to MEDIA or an absolute path.
  
  file(string $path)
  ------------------
  Takes a path and finds the corresponding file.

  Given files and directory layout:
  your-media-root/
     - static
       - img 
         - test.jpg
         ...
     - transfer
       - vid
         - mymovie.avi
         ...                   
     - filter
       - s
         - static
           - img
             - test.png
         - transfer
           - vid
             - mymovie.png
       ...
       - xl
         - static
           - img          
             - test.png
 
 
   Path                     Finds
   ------------------------------------------------------
   img/test                 static/img/test.jpg
   static/img/test.jpg      static/img/test.jpg
   image/test               static/img/test.jpg
   transfer/vid/mymovie     transfer/vid/mymovie.avi
   s/img/test               filter/s/static/img/test.png
   s/transfer/vid/mymovie   filter/s/transfer/vid/mymovie
   css/backend              static/css/backend.css
  
  url(mixed $url, boolean $full)
  ------------------------------
  Takes a path and finds the corresponding file then maps it to the correct
  url.
   
  embed(string $path, array $options)
  -----------------------------------
  Used to render tags needed to display a medium.
  Currently images and video are supported. See file() for path usage.
  
  link(string $path, array $options)
  -----------------------------------
  Used to render tags needed to link to a medium.
  Currently css, javascript and rss are supported. See file() for path usage.
  Specify "false" for options and the output will be added to 
  $scripts_for_layout variable.


 ManageShell 
-------------------------------------------------------------------------------
  The ManageShell provides a task to synchronize files in filesystem with 
  (related) rows in tables. And a task to make file versions. 

  Invoke the MediaShell from the commandline by executing:
  	./cake media.manage
  or
  	./cake media.manage sync
  	
  For help execute:
  	./cake media.manage help

  To get more verbose output:
  	./cake media.manage sync -verbose
  	
  ...or no ouput except errors:
  	./cake media.manage sync -quiet
  	
  A sample cronjob utilizing the shell's sync task is included 
  in the examples dir.


 MimeType
-------------------------------------------------------------------------------
  This class enables you to detect the mime type of a file and provides you
  with magic lookups even if no magic extension is available. 

  All methods of this class should be called statically.
  
  config(string $name, array $settings)
  -------------------------------------
  Configures the glob and magic lookup methods of the class.
  
  MimeType::config('magic');
  	Chooses the first magic method available:
  	1. fileinfo extension
  	2. mime_magic extension
  	3. MimeMagic class
  
  MimeType::config('magic', array('engine' => 'core', 'file' => '/path/to/magic.db');
	MimeMagic class will be used with the file provided in 'file'
	Where 'file' can be a magic file in in apache mod_mime_magic or freedesktop format.

  MimeType::config('glob');
  	MimeType::config('glob');
  	Chooses the first glob method available:
  	1. MimeGlob class
   
  MimeType::config('glob', array('engine' => 'core', 'file' => '/path/to/magic.db');
	MimeGlob class will be used with the file provided in 'file'
	Where 'file' can be a glob file in in apache mod_mime or freedesktop format.

  guessExtension(string $file, array $options)
  --------------------------------------------
  Guesses the extension for a file or mime type

  guessType(string $file, array $options)
  ---------------------------------------
  Guesses the mime type of a file
  Valid options are: 'fast' and 'simplify'
  
  simplify(string $mimeType)
  --------------------------
  Removes experimental indicators from a mime type.
  e.g. application/x-javascript becomes application/javascript.
  
 
 Medium
-------------------------------------------------------------------------------
  forthcoming
  
  Adapters
  --------
  Adapters like the PearTextMediumAdapter will try to import vendor php classes. 
  Make sure that either the directory where your PEAR classes are within 
  your include or vendors paths: 
  
  app/config/bootstrap.php
   ...
   $vendorPaths[] = '/usr/share/php/PEAR/';
   ...
 
 
 Credits 
-------------------------------------------------------------------------------
  Parts of this project were/are directly or indirectly 
  based upon/is or inspired by/incorporates code of:

 * CakePHP
 	by the Cake Software Foundation, Inc. 
 	<http://www.cakephp.org>

 * Attach This! 
 	by Alex Fadyen
 	<http://cakeforge.org/projects/attachments>

 * Generic File Upload Behavior
 	by Andy Dawson
 	<http://www.ad7six.com/MiBlog/GenericFileUploadBehavior>
 
 * ImageHelper
 	by Jon Bennet
 	<http://cakeforge.org/snippet/detail.php?type=snippet&id=188>
 
 * Improved Upload Behaviour 
 	by Tane Piper
 	<http://bin.cakephp.org/view/82605077>
 	
    
 Known Bugs
-------------------------------------------------------------------------------
  CakePHP open tickets affecting this plugin:
  https://trac.cakephp.org/ticket/1855 (locale translation per plugin) 
  https://trac.cakephp.org/ticket/2056 (afterFind not called on associated Model Behaviours - diff attached)
  https://trac.cakephp.org/ticket/4471 (saveAll() should pass entire data to __save() function of models)


 Bug reporting
-------------------------------------------------------------------------------
  You're encouraged to report bugs to the tracker
  http://cakeforge.org/tracker/?atid=809&group_id=227&func=browse
  We'll fix them.
