<?php
/**
 * Transfer Behavior File
 *
 * Copyright (c) 2007-2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.models.behaviors
 * @copyright  2007-2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.MimeType');
App::import('Vendor', 'Media.Medium');
App::import('Vendor', 'Media.MediaValidation');
App::import('Vendor', 'Media.TransferValidation');
/**
 * Transfer Behavior Class
 *
 * Takes care of transferring local and remote (via HTTP)
 * files or handling uploads received through a HTML form.
 *
 * @package    media
 * @subpackage media.models.behaviors
 */
class TransferBehavior extends ModelBehavior {
/**
 * Holds data between function calls keyed by model alias
 *
 * @var array
 */
	var $runtime = array();
/**
 * Settings keyed by model alias
 *
 * @var array
 */
	var $settings = array();
/**
 * Default settings
 *
 * trustClient
 * 	false -
 * 	true  - Trust the MIME type submitted together with an upload
 *
 * baseDirectory
 * 	string - An absolute path (with trailing slash) to a directory
 *
 * createDirectory
 * 	false - Fail on missing directories
 * 	true  - Recursively create missing directories
 *
 * alternativeFileTries
 * 	integer - Specifies the maximum number of tries for finding an alternative destination file name
 *
 * @var array
 */
	var $_defaultSettings = array(
		'trustClient'     => false,
		'baseDirectory'   => MEDIA_TRANSFER,
		'createDirectory' => true,
		'alternativeFile' => 100
	);
/**
 * Default runtime
 *
 * @var array
 */
	var $_defaultRuntime = array(
		'source'       => null,
		'temporary'    => null,
		'destination'  => null,
		'isReady'      => false,
		'hasPerformed' => false
	);
/**
 * Setup
 *
 * Merges default settings with provided config and sets default validation options
 *
 * @param Model $Model
 * @param array $config @see _defaultSettings for configuration options
 * @return void
 */
	function setup(&$Model, $config = null) {
		if (!is_array($config)) {
			$config = array();
		}

		/* If present validation rules get some sane default values */
		if (isset($Model->validate['file'])) {
			$default = array('allowEmpty' => true, 'required' => false, 'last' => true);

			foreach ($Model->validate['file'] as &$rule) {
				$rule = array_merge($default, $rule);
			}
		}

		$this->settings[$Model->alias] = $config + $this->_defaultSettings;
		$this->runtime[$Model->alias] = $this->_defaultRuntime;
	}
/**
 * Run before any or if validation occurs
 *
 * Triggers `prepare()` setting source, temporary and destination to
 * enable validation rules to check the transfer. If that fails
 * invalidates the model.
 *
 * @param Model $Model
 * @return boolean
 */
	function beforeValidate(&$Model) {
		if ($this->prepare($Model) === false) {
			$Model->invalidate('file', 'error');
			return false;
		}
		return true;
	}
/**
 * Triggers `prepare()` and performs transfer
 *
 * If transfer is unsuccessful save operation will abort.
 *
 * @param Model $Model
 * @return boolean
 */
	function beforeSave(&$Model) {
		$preparation = $this->prepare($Model);

		if ($preparation === false) {
			return false;
		}
		if ($preparation === null) {
			if (array_key_exists('file', $Model->data[$Model->alias])) {
				unset($Model->data[$Model->alias]['file']);
			}
 			return true;
		}
		extract($this->runtime[$Model->alias], EXTR_SKIP);
		extract($this->settings[$Model->alias], EXTR_SKIP);

		if (!$this->perform($Model)) { /* uses source, etc. from runtime */
			return false;
		}
		$Model->data[$Model->alias]['file'] = $destination['dirname'] . DS . $destination['basename'];
		return $Model->data[$Model->alias];
	}
/**
 * Triggered by `beforeValidate`, `beforeSave` or upon user request
 *
 * Prepares runtime for being used by `perform()`
 *
 * @param Model $Model
 * @param string $file Optionally provide a valid transfer resource to be used as source
 * @return mixed true if transfer is ready to be performed, false on error, null if no data was found
 */
	function prepare(&$Model, $file = null) {
		/* Pre */

		if (isset($Model->data[$Model->alias]['file'])) {
			$file = $Model->data[$Model->alias]['file'];
		}
		if (empty($file)) {
			return null;
		}
		if ($this->runtime[$Model->alias]['hasPerformed']) {
			$this->reset($Model);
		}
		if ($this->runtime[$Model->alias]['isReady']) {
			return true;
		}
		/* Extraction must happen after reset */
		extract($this->settings[$Model->alias], EXTR_SKIP);
		extract($this->runtime[$Model->alias], EXTR_SKIP);

		if (TransferValidation::blank($file)) {
			/* Set explicitly null enabling allowEmpty in rules act upon emptiness */
			return $Model->data[$Model->alias]['file'] = null;
		}

		/* From */

		if ($source = $this->_source($Model, $file)) {
			$this->runtime[$Model->alias]['source'] = $source;
		} else {
			return false;
		}

		/* Via */

		if ($source['type'] !== 'file-local') {
			$temporary = $this->runtime[$Model->alias]['temporary'] = $this->_temporary($Model, $file);
		}

		/* To */

		if (method_exists($Model, 'transferTo')) {
			$file = $baseDirectory . $Model->transferTo($temporary, $source);
		}  else {
			$file = $baseDirectory . $this->transferTo($Model, $temporary, $source);
		}
		if (!$file = $this->_alternativeFile($file, $alternativeFileTries)) {
			$message  = "TransferBehavior::prepare - ";
			$message .= "Exceeded number of max. tries while finding alternative file name.";
			trigger_error($message, E_USER_NOTICE);
			return false;
		}

		if ($destination = $this->_destination($Model, $file)) {
			$this->runtime[$Model->alias]['destination'] = $destination;
		} else {
			return false;
		}
		if ($destination == $source || $destination == $temporary) {
			return false;
		}
		$Folder = new Folder($destination['dirname'], $createDirectory);

		if (!$Folder->pwd()) {
			$message  = "TransferBehavior::prepare - Directory `{$destination['dirname']}` could ";
			$message .= "not be created or is not writable. Please check the permissions.";
			trigger_error($message, E_USER_WARNING);
			return false;
		}

		/* Post */

		return $this->runtime[$Model->alias]['isReady'] = true;
	}
/**
 * Returns a relative path to the destination file
 *
 * @param array $source Information about the source
 * @return string
 */
	function transferTo(&$Model, $via, $from) {
		extract($from);
		$path  = Medium::short($file, $mimeType) . DS;
		$path .= strtolower(Inflector::slug($filename));
		$path .= !empty($extension) ? '.' . strtolower($extension) : null;
		return $path;
	}
/**
 * Parse data to be used as source
 *
 * @param mixed Path to file in local FS, URL or file-upload array
 * @return mixed Array with parsed results on success, false on error
 * @todo evaluate errors in file uploads
 */
	function _source(&$Model, $data) {
		if (TransferValidation::fileUpload($data)) {
			return array_merge($this->info($Model, $data), array('error' => $data['error']));
		} elseif (MediaValidation::file($data)) {
			return $this->info($Model, $data);
		} elseif (TransferValidation::url($data, array('scheme' => 'http'))) {
			return $this->info($Model, $data);
		}
		return false;
	}
/**
 * Parse data to be used as temporary
 *
 * @param mixed Path to file in local FS or file-upload array
 * @return mixed Array with parsed results on success, false on error
 */
	function _temporary(&$Model, $data) {
		if (TransferValidation::fileUpload($data)
		&& TransferValidation::uploadedFile($data['tmp_name'])) {
			return array_merge(
				$this->info($Model, $data['tmp_name']),
				array('error' => $data['error'])
			);
		} elseif (MediaValidation::file($data)) {
			return $this->info($Model, $data);
		}
		return false;
	}
/**
 * Parse data to be used as destination
 *
 * @param mixed Path to file in local FS
 * @return mixed Array with parsed results on success, false on error
 */
	function _destination(&$Model, $data) {
		/* Destination file may not exist yet */
		if (MediaValidation::file($data , false)) {
			return $this->info($Model, $data);
		}
		return false;
	}
/**
 * Performs a transfer
 *
 * @param Model $Model
 * @param array $source
 * @param array $temporary
 * @param array $destination
 * @return boolean true on success, false on failure
 */
	function perform(&$Model) {
		extract($this->runtime[$Model->alias]);

		if (!$isReady || $hasPerformed) {
			return false;
		}
		$hasPerformed = false;

		$chain = implode('>>', array($source['type'], $temporary['type'], $destination['type']));

		if ($source['type'] === 'http-url-remote') {
			if (!class_exists('HttpSocket')) {
				App::import('Core','HttpSocket');
			}
			$Socket = new HttpSocket(array('timeout' => 5));
			$Socket->request(array('method' => 'GET', 'uri' => $source['file']));

			if (!empty($Socket->error) || $Socket->response['status']['code'] != 200) {
				return $this->runtime[$Model->alias]['hasPerformed'] = $hasPerformed;
			}
		}

		if ($chain === 'file-upload-remote>>uploaded-file-local>>file-local') {
			$hasPerformed = move_uploaded_file($temporary['file'], $destination['file']);
		} elseif ($chain === 'file-local>>>>file-local') {
			$hasPerformed = copy($source['file'], $destination['file']);
		} elseif ($chain === 'file-local>>file-local>>file-local') {
			if (copy($source['file'], $temporary['file'])) {
				$hasPerformed = rename($temporary['file'], $destination['file']);
			}
		} elseif ($chain === 'http-url-remote>>>>file-local') {
			$hasPerformed = file_put_contents($destination['file'], $Socket->response['body']);
		} elseif ($chain === 'http-url-remote>>file-local>>file-local') {
			if (file_put_contents($temporary['file'], $Socket->response['body'])) {
				$hasPerformed = rename($temporary['file'], $destination['file']);
			}
		}
		return $this->runtime[$Model->alias]['hasPerformed'] = $hasPerformed;
	}
/**
 * Resets runtime property
 *
 * @param Model $Model
 * @return void
 */
	function reset(&$Model) {
		$this->runtime[$Model->alias] = $this->_defaultRuntime;
	}
/**
 * Convenience method which (if available) returns absolute path to last transferred file
 *
 * @param Model $Model
 * @return mixed
 */
	function transferred(&$Model) {
		extract($this->runtime[$Model->alias], EXTR_SKIP);

		if ($hasPerformed) {
			return $destination['file'];
		}
		return false;
	}
/**
 * Checks if field contains a transferable resource
 *
 * @see TransferBehavior::source
 *
 * @param Model $Model
 * @param array $field
 * @return boolean
 */
	function checkResource(&$Model, $field) {
		return TransferValidation::resource(current($field));
	}
/**
 * Checks if sufficient permissions are set to access the resource
 * Source must be readable, temporary read or writable, destination writable
 *
 * @param Model $Model
 * @param array $field
 * @return boolean
 */
	function checkAccess(&$Model, $field) {
		extract($this->runtime[$Model->alias]);

		if (MediaValidation::file($source['file'], true)) {
			if (!MediaValidation::access($source['file'], 'r')) {
				return false;
			}
		} else {
			if (!MediaValidation::access($source['permission'], 'r')) {
				return false;
			}
		}
		if (!empty($temporary)) {
			if (MediaValidation::file($temporary['file'], true)) {
				if (!MediaValidation::access($temporary['file'], 'r')) {
					return false;
				}
			} elseif (MediaValidation::folder($temporary['dirname'], true)) {
				if (!MediaValidation::access($temporary['dirname'], 'w')) {
					return false;
				}
			}
		}
		if (!MediaValidation::access($destination['dirname'], 'w')) {
			return false;
		}
		return true;
	}
/**
 * Checks if resource is located within given locations
 *
 * @param Model $Model
 * @param array $field
 * @param mixed $allow True or * allows any location, an array containing absolute paths to locations
 * @return boolean
 */
	function checkLocation(&$Model, $field, $allow = true) {
		extract($this->runtime[$Model->alias]);

		if (strpos(':', $allow) !== false) {
			$message  = "TransferBehavior::checkLocation - ";
			$message .= "Makers cannot be used in parameters for this method anymore. ";
			$message .= "Please use predefined constants instead.";
			trigger_error($message, E_USER_NOTICE);
		}

		foreach (array('source', 'temporary', 'destination') as $type) {
			if ($type == 'temporary' && empty($$type)) {
				continue;
			}
			if ($type == 'source' && ${$type}['type'] == 'file-upload-remote') {
				continue;
			}
			if (!MediaValidation::location(${$type}['file'], $allow)) {
				return false;
			}
		}
		return true;
	}
/**
 * Checks if provided or potentially dangerous permissions are set
 *
 * @param Model $Model
 * @param array $field
 * @param mixed $match True to check for potentially dangerous permissions,
 * 	a string containing the 4-digit octal value of the permissions to check for an exact match,
 * 	false to allow any permissions
 * @return boolean
 */
	function checkPermission(&$Model, $field, $match = true) {
		extract($this->runtime[$Model->alias]);

		foreach (array('source', 'temporary') as $type) {
			if ($type == 'temporary' && empty($$type)) {
				continue;
			}
			if (!MediaValidation::permission(${$type}['permission'], $match)) {
				return false;
			}
		}
		return true;
	}
/**
 * Checks if resource doesn't exceed provided size
 *
 * Please note that the size will always be checked against
 * limitations set in `php.ini` for `post_max_size` and `upload_max_filesize`
 * even if $max is set to false.
 *
 * @param Model $Model
 * @param array $field
 * @param mixed $max String (e.g. 8M) containing maximum allowed size, false allows any size
 * @return boolean
 */
	function checkSize(&$Model, $field, $max = false) {
		extract($this->runtime[$Model->alias]);

		foreach (array('source', 'temporary') as $type) {
			if ($type == 'temporary' && empty($$type)) {
				continue;
			}
			if (!MediaValidation::size(${$type}['size'], $max)) {
				return false;
			}
		}
		return true;
	}
/**
 * Checks if resource (if it is an image) pixels doesn't exceed provided size
 *
 * Useful in situation where you wan't to prevent running out of memory when
 * the image gets resized later. You can calculate the amount of memory used
 * like this: width * height * 4 + overhead
 *
 * @param Model $Model
 * @param array $field
 * @param mixed $max String (e.g. 40000 or 200x100) containing maximum allowed amount of pixels
 * @return boolean
 */
	function checkPixels(&$Model, $field, $max = false) {
		extract($this->runtime[$Model->alias]);

		foreach (array('source', 'temporary') as $type) { /* pixels value is optional */
			if (($type == 'temporary' && empty($$type)) || !isset(${$type}['pixels'])) {
				continue;
			}
			if (!MediaValidation::pixels(${$type}['pixels'], $max)) {
				return false;
			}
		}
		return true;
	}
/**
 * Checks if resource has (not) one of given extensions
 *
 * @param Model $Model
 * @param array $field
 * @param mixed $deny True or * blocks any extension,
 * 	an array containing extensions (w/o leading dot) selectively blocks,
 * 	false blocks no extension
 * @param mixed $allow True or * allows any extension,
 * 	an array containing extensions (w/o leading dot) selectively allows,
 * 	false allows no extension
 * @return boolean
 */
	function checkExtension(&$Model, $field, $deny = false, $allow = true) {
		extract($this->runtime[$Model->alias]);

		foreach (array('source', 'temporary', 'destination') as $type) {
			if (($type == 'temporary' && empty($$type)) || !isset(${$type}['extension'])) {
				continue;
			}
			if (!MediaValidation::extension(${$type}['extension'], $deny, $allow)) {
				return false;
			}
		}
		return true;
	}
/**
 * Checks if resource has (not) one of given MIME types
 *
 * @param Model $Model
 * @param array $field
 * @param mixed $deny True or * blocks any MIME type,
 * 	an array containing MIME types selectively blocks,
 * 	false blocks no MIME type
 * @param mixed $allow True or * allows any extension,
 * 	an array containing extensions selectively allows,
 * 	false allows no MIME type
 * @return boolean
 */
	function checkMimeType(&$Model, $field, $deny = false, $allow = true) {
		extract($this->runtime[$Model->alias]);
		extract($this->settings[$Model->alias], EXTR_SKIP);

		foreach (array('source', 'temporary') as $type) {
			/*
			 * MIME types and trustClient setting
			 *
			 * trust | source   | (temporary) | (destination)
			 * ------|----------|----------------------------
			 * true  | x/x      | x/x         | x/x,null
			 * ------|----------|----------------------------
			 * false | x/x,null | x/x,null    | null
			 */
			/* Temporary is optional */
			if ($type === 'temporary' && empty($$type)) {
				continue;
			}
			/* With `trustClient` set to `false` we don't necessarily have a MIME type */
			if (!isset(${$type}['mimeType']) && !$trustClient) {
				continue;
			}
			if (!MediaValidation::mimeType(${$type}['mimeType'], $deny, $allow)) {
				return false;
			}
		}
		return true;
	}
/**
 * Gather/Return information about a resource
 *
 * @param mixed $resource Path to file in local FS, URL or file-upload array
 * @param string $what scheme, host, port, file, MIME type, size, permission,
 * 	dirname, basename, filename, extension or type
 * @return mixed
 */
	function info(&$Model, $resource, $what = null) {
		extract($this->settings[$Model->alias], EXTR_SKIP);

		$defaultResource = array(
			'scheme'      => null,
			'host'        => null,
			'port'        => null,
			'file'        => null,
			'mimeType'    => null,
			'size'        => null,
			'pixels'      => null,
			'permisssion' => null,
			'dirname'     => null,
			'basename'    => null,
			'filename'    => null,
			'extension'   => null,
			'type'        => null,
		);

		/* Currently HTTP is supported only */
		if (TransferValidation::url($resource, array('scheme' => 'http'))) {
			$resource = array_merge(
				$defaultResource,
				pathinfo(parse_url($resource, PHP_URL_PATH)),
				array(
					'scheme' => parse_url($resource, PHP_URL_SCHEME),
					'host'   => parse_url($resource, PHP_URL_HOST),
					'port'   => parse_url($resource, PHP_URL_PORT),
					'file'   => $resource,
					'type'   => 'http-url-remote',
			));

			if (!class_exists('HttpSocket')) {
				App::import('Core', 'HttpSocket');
			}
			$Socket =& new HttpSocket(array('timeout' => 5));
			$Socket->request(array('method' => 'HEAD', 'uri' => $resource['file']));

			if (empty($Socket->error) && $Socket->response['status']['code'] == 200) {
				$resource = array_merge(
					$resource,
					array(
						'size'       => $Socket->response['header']['Content-Length'],
						'mimeType'   => $trustClient ? $Socket->response['header']['Content-Type'] : null,
						'permission' => '0004'
				));
			}
		} elseif (MediaValidation::file($resource, false)) {
			$resource = array_merge(
				$defaultResource,
				pathinfo($resource),
				array(
					'file' => $resource,
					'host' => 'localhost',
					'mimeType' => MimeType::guessType($resource, array('paranoid' => !$trustClient)),
			));

			if (TransferValidation::uploadedFile($resource['file'])) {
				$resource['type'] = 'uploaded-file-local';
			} else {
				$resource['type'] = 'file-local';
			}

			if (is_readable($resource['file'])) {
				/*
				 * Because there is not better  way to determine if resource is an image
				 * first, we suppress a warning that would be thrown here otherwise.
				 */
				list($width, $height) = @getimagesize($resource['file']);

				$resource = array_merge(
					$resource,
					array(
						'size'       => filesize($resource['file']),
						'permission' => substr(sprintf('%o', fileperms($resource['file'])), -4),
						'pixels'     => $width * $height,
				));
			}
		} elseif (TransferValidation::fileUpload($resource)) {
			$resource = array_merge(
				$defaultResource,
				pathinfo($resource['name']),
				array(
					'file'       => $resource['name'],
					'host'       => env('REMOTE_ADDR'),
					'size'       => $resource['size'],
					'mimeType'   => $trustClient ? $resource['type'] : null,
					'permission' => '0004',
					'type'       => 'file-upload-remote',
			));
		} else {
			return null;
		}

		if (!isset($resource['filename'])) { /* PHP < 5.2.0 */
			$length = isset($resource['extension']) ? strlen($resource['extension']) + 1 : 0;
			$resource['filename'] = substr($resource['basename'], 0, - $length);
		}

		if (is_null($what)) {
			return $resource;
		} elseif (array_key_exists($what, $resource)) {
			return $resource[$what];
		}
		return null;
	}
/**
 * Finds an alternative filename for an already existing file
 *
 * @param string $file Absolute path to file in local FS
 * @param integer $tries Number of tries
 * @return mixed A string if an alt. name was found, false if number of tries were exceeded
 */
	function _alternativeFile($file, $tries = 100) {
		$extension = null;
		extract(pathinfo($file), EXTR_OVERWRITE);

		if (!isset($filename)) { /* PHP < 5.2.0 */
			$filename = substr($basename, 0, isset($extension) ? - (strlen($extension) + 1) : 0);
		}
		$newFilename = $filename;

		$Folder = new Folder($dirname);
		$names = $Folder->find($filename . '.*');

		foreach ($names as &$name) { /* PHP < 5.2.0 */
			$length =  strlen(pathinfo($name, PATHINFO_EXTENSION));
			$name = substr(basename($name), 0, $length ? - ($length + 1) : 0);
		}

		for ($count = 2; in_array($newFilename, $names); $count++) {
			if ($count > $tries) {
				return false;
			}

			$newFilename = $filename . '_' . $count;
		}

		$new = $dirname . DS . $newFilename;

		if (isset($extension)) {
			$new .= '.' . $extension;
		}
		return $new;
	}
/**
 * Convenience method which (if available) returns absolute path to last transferred file
 *
 * @param Model $Model
 * @return mixed
 * @deprecated
 */
	function getLastTransferredFile(&$Model) {
		$message  = "TransferBehavior::getLastTransferredFile - ";
		$message .= "Has been deprecated in favour of `transferred()`";
		trigger_error($message, E_USER_NOTICE);
		return $this->transferred($Model);
	}
}
?>