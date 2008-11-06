 
 ATTACHMENTS PLUGIN for CAKEPHP
==============================================================================

 * Introduction
 * Features
 * Depend
 * Download
 * Install
 
 * Quickstart
 * Model
 * Controller
 * Element

 * Credits
 * Known bugs
 * Bug reporting


 Introduction
-------------------------------------------------------------------------------
  Attachments plugin for CakePHP consists of a model and an element to deal
  with attaching files to records of a model.

  Until 0.42 it was part of the original Attachments package. 
  Since 0.50 it is one out of two plugins from the attm project.
  
  "attm" refers to the components used throughout the project:
  
  a    attachment
   t   transfer
    t  tautology
     m medium


 Features
-------------------------------------------------------------------------------
 * Association of any number of files with any record of any model
 
 * Convenient Element to include into your forms
 
 * Ready to use model

 * Controller to get an overview of attached files 
 
 
 Depend
-------------------------------------------------------------------------------
  Until CakePHP 1.2 stable isn't released this plugin is intended to run with 
  the latest cake 1.2 version from 1.2 svn branch.
 
  Required
  --------
 * OS
   tested with GNU/Linux

 * Webserver
   tested with Apache 2.2.8
   
 * PHP >=5.2.x
   tested with 5.2.6RC6
   
 * CakePHP 1.2
   
 * media plugin for CakePHP


 Download
-------------------------------------------------------------------------------
  Download the package from http://cakeforge.org/frs/?group_id=227
  Make sure you select the latest release.
  
  You may also want to do an anonymous checkout from svn:
  "svn co https://svn.cakeforge.org/svn/attm/trunk/0.5x/plugins/attachments /path/to/save"


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
  Create tables by using the schema or sql provided in config/sql

  Step 4
  ------
  Add default media plugin configuration by appending this line to your app's core.php:
  	...
  	require_once(APP.'plugins'.DS.'media'.DS.'config'.DS.'core.php');
  
  Step 5
  ------
  Define MEDIA and MEDIA_URL in your config/bootstrap.php or config/core.php:
    ...   
  		define('MEDIA','/path/to/your/app/webroot/media/');
  		define('MEDIA_URL','media/');
    ...
  
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
    
  Step 9
  ------
  Init attachments table

  Step 10
  -------
  In a model of your app (here: "Movie") link the attachments model:
 
	  class Movie extends AppModel {
		...
	 	var $hasMany = array(
	 						...
							'Attachment' => array(
										'className' => 'Attachments.Attachment',
										'foreignKey' => 'foreign_key',
										'conditions' => array('model' => 'Movie'),
										),
							...
						 	);
		...
  
  Step 11
  -------
  Edit the corresponding Movies controller and change save calls in 
  the edit and add methods as well as the helper property:
  
  	class MoviesController extends AppController {
  		...
  		var $helpers = array('Form', 'Media.Media');
  		...
  		function add() {
			...
			if ($this->Movie->saveAll($this->data, array('validate' => 'first')) {
			...
 
	 	function edit($id = null) {
	 		...
				if ($this->Movie->saveAll($this->data, array('validate' => 'first')) {
 			...

  Step 12
  -------
  Edit the corresponding add and edit views:
  
 	<div class="movies form">
		<?php echo $form->create('Movie', array('type' => 'file'));?>
			...  
			
			...
	 		<?php echo $this->element('attachments', array('plugin' => 'attachments'));?>
		<?php echo $form->end('Submit');?>
	</div>
	
  You should now be able to attach files 
  through the add/edit movies views to and from your model!
  
  PITFALLS
  --------
  If you suffer from mime type validation errors, comment out the validation rule
  in Attachments Model.	
	
	
 Model
-------------------------------------------------------------------------------
  This plugin includes a model which is prepared to work as a central
  place for attached files and is composed of three behaviors. 

  It manages associations with other models by using PolymorphicBehavior by 
  Andy Dawson. It deals with file transfers (thru TransferBehavior) as well 
  as validation and finally enables coupling of files in the filesystem with records
  of the model.
  
  You can link to this model directly without modifying it:  
  
  	  class Movie extends AppModel {
		...
	 	var $hasMany = array(
	 						...
							'Attachment' => array(
										'className' => 'Attachments.Attachment',
										'foreignKey' => 'foreign_key',
										'conditions' => array('model' => 'Movie'),
										'dependent' => true,
										),
							...
						 	);
		... 
		
  Or use it as a blueprint and copy it over to your apps model directory 
  and link this then instead.
  
  If you like to group attachments of a certain model use this:

  	  class Movie extends AppModel {
		...
	 	var $hasMany = array(
	 						'Poster' => array(
								'className' => 'Attachments.Attachment',
								'foreignKey' => 'foreign_key',
								'conditions' => array('model' => 'Movie','group' => 'poster'),
								'dependent' => true,
								),
	 						'Photo' => array(
								'className' => 'Attachments.Attachment',
								'foreignKey' => 'foreign_key',
								'conditions' => array('model' => 'Movie','group' => 'photo'),
								'dependent' => true,
								),
							'Trailer' => array(
								'className' => 'Attachments.Attachment',
								'foreignKey' => 'foreign_key',
								'conditions' => array('model' => 'Movie','group' => 'trailer'),
								'dependent' => true,
								)
						 	); 
		...
  
  On configuring validation rules see "TransferBehavior/Validation" section 
  in the README.txt of media plugin.
  

 Element 
-------------------------------------------------------------------------------
  An element is included which helps dealing with attaching/releasing files
  in forms.
  
  Edit the add and edit views of the controller make sure the form type 
  is set to "file".
 
 	<div class="examples form">
		<?php echo $form->create('Example',array('type' => 'file'));?>
			...
			
  Still in the view add the statement for rendering the attachments element
 
 			...
	 		<?php echo $this->element('attachments',array('plugin' => 'attachments'));?>
		<?php echo $form->end('Submit');?>
	</div>

  Please make sure that you included:

  * FormHelper
  * MediumHelper

  in the helpers property of your controller. The element needs them to work.

  The element renders widgets according to data found in $this->data.
  You aid identifying the correct data by passing parameters to the element.
  
 			...
	 		<?php echo $this->element('attachments',array('plugin' => 'attachments', 'model' => 'Example', 'assocAlias' => 'Attachment'));?>
		<?php echo $form->end('Submit');?>
	</div>  
  
  * "model": The name of the model files are attached to. Defaults to current model in form context.
  * "assocAlias": Defaults to "Attachment" 
  
  "model" and "assocAlias" parameters must be specified when using the multiple
  attachments elements in one form:

 			...
	 		<?php echo $this->element('attachments',array('plugin' => 'attachments', 'model' => 'Example', 'assocAlias' => 'Poster'));?>
	 		<?php echo $this->element('attachments',array('plugin' => 'attachments', 'model' => 'Example', 'assocAlias' => 'Trailer'));?>
	 		<?php echo $this->element('attachments',array('plugin' => 'attachments', 'model' => 'Example', 'assocAlias' => 'Photo'));?>
	 		...
		<?php echo $form->end('Submit');?>
	</div>  

  
 Credits 
-------------------------------------------------------------------------------
  Parts of this project were/are directly or indirectly 
  based upon/is or inspired by/incorporates code of:

 * CakePHP Core Library (especially File, Folder and MediaView Class)
 	by the Cake Software Foundation, Inc. 
 	<http://www.cakephp.org>

 * Attach This! 
 	by Alex Fadyen
 	<http://cakeforge.org/projects/attachments>
 	 
 * Generic File Upload Behavior
 	by Andy Dawson
 	<http://www.ad7six.com/MiBlog/GenericFileUploadBehavior>
 

 Known Bugs
-------------------------------------------------------------------------------
  CakePHP open tickets affecting this plugin:
  https://trac.cakephp.org/ticket/4769 (saveAll + multiple hasMany Records does not show validation errors)
  https://trac.cakephp.org/ticket/1855 (locale translation per plugin) 
  https://trac.cakephp.org/ticket/2056 (afterFind not called on associated Model Behaviours - diff attached)
  https://trac.cakephp.org/ticket/4471 (saveAll() should pass entire data to __save() function of models)


 Bug reporting
-------------------------------------------------------------------------------
  You're encouraged to report bugs to the tracker
  http://cakeforge.org/tracker/?atid=809&group_id=227&func=browse
  We'll fix them ;)
