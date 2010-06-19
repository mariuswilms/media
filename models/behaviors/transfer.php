<?php
/**
 * Transfer Behavior File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.models.behaviors
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Lib', 'Mime_Type', array('file' => 'mm/src/Mime/Type.php'));
App::import('Lib', 'Media.MediaValidation');
App::import('Lib', 'Media.TransferValidation');

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
 *   false -
 *   true  - Trust the MIME type submitted together with an upload
 *
 * transferDirectory
 *   string - An absolute path (with trailing slash) to a directory
 *
 * createDirectory
 *   false - Fail on missing directories
 *   true  - Recursively create missing directories
 *
 * alternativeFileTries
 *   integer - Specifies the maximum number of tries for finding an alternative destination file name
 *
 * overwrite
 *   false - Existing destination files with the same are not overridden, an alternative name is used
 *   true - Overwrites existing destination files with the same name
 *
 * @var array
 */
	var $_defaultSettings = array(
		'trustClient'       => false,
		'transferDirectory' => MEDIA_TRANSFER,
		'createDirectory'   => true,
		'alternativeFile'   => 100,
		'overwrite'         => false
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
		'isPrepared'   => false,
		'hasPerformed' => false
	);

/**
 * Setup
 *
 * Merges default settings with provided config and sets default validation options
 *
 * @param Model $Model
 * @param array $settings See defaultSettings for configuration options
 * @return void
 */
	function setup(&$Model, $settings = array()) {
		$settings = (array)$settings;

		if (isset($settings['destinationFile'])) {
			$message  = "TransferBehavior::setup - The `destinationFile` settings has been ";
			$message .= "removed in favor of the `transferTo()` callback. Implement the method ";
			$message .= "in the `{$Model->alias}` model to get custom destination paths.";
			trigger_error($message, E_USER_WARNING);
		}
		if (isset($settings['baseDirectory'])) {
			$message  = "TransferBehavior::setup - The `baseDirectory` settings has been ";
			$message .= "renamed to `transferDirectory`.";
			trigger_error($message, E_USER_WARNING);
		}

		/* If present validation rules get some sane default values */
		if (isset($Model->validate['file'])) {
			$default = array('allowEmpty' => true, 'required' => false, 'last' => true);

			foreach ($Model->validate['file'] as &$rule) {
				$rule = array_merge($default, $rule);
			}
		}

		$this->settings[$Model->alias] = $settings + $this->_defaultSettings;
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
		if (!isset($Model->data[$Model->alias]['file'])) {
			return true;
		}
		$file = $Model->data[$Model->alias]['file'];

		if (TransferValidation::blank($file)) {
			$Model->data[$Model->alias]['file'] = null;
			return true;
		}

		if (!$this->_prepare($Model, $file)) {
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
		if (!isset($Model->data[$Model->alias]['file'])) {
			return true;
		}
		$file = $Model->data[$Model->alias]['file'];

		if (TransferValidation::blank($file)) {
			unset($Model->data[$Model->alias]['file']);
			return true;
		}

		if (!$file = $this->transfer($Model, $file)) {
			return false;
		}
		$Model->data[$Model->alias]['file'] = $file;
		return $Model->data[$Model->alias];
	}

/**
 * Retrieves metadata of any transferrable resource
 *
 * @param Model $Model
 * @param array|string $resource Transfer resource
 * @return array|void
 */
	function transferMeta(&$Model, $resource) {
		extract($this->settings[$Model->alias]);

		$defaultResource = array(
			'scheme'      => null,
			'host'        => null,
			'port'        => null,
			'file'        => null,
			'mimeType'    => null,
			'size'        => null,
			'pixels'      => null,
			'permission'  => null,
			'dirname'     => null,
			'basename'    => null,
			'filename'    => null,
			'extension'   => null,
			'type'        => null
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
					'type'   => 'http-url-remote'
			));

			if (!class_exists('HttpSocket')) {
				App::import('Core', 'HttpSocket');
			}
			$Socket = new HttpSocket(array('timeout' => 5));
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
					'mimeType' => Mime_Type::guessType($resource, array('paranoid' => !$trustClient))
			));

			if (TransferValidation::uploadedFile($resource['file'])) {
				$resource['type'] = 'uploaded-file-local';
			} else {
				$resource['type'] = 'file-local';
			}

			if (is_readable($resource['file'])) {
				$resource = array_merge(
					$resource,
					array(
						'size'       => filesize($resource['file']),
						'permission' => substr(sprintf('%o', fileperms($resource['file'])), -4),
				));
				/*
				 * Because there is not better way to determine if resource is an image
				 * first, we suppress a warning that would be thrown here otherwise.
				 */
				if (function_exists('getimagesize')) {
					list($width, $height) = @getimagesize($resource['file']);
					$resource['pixels'] = $width * $height;
				}
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
					'type'       => 'file-upload-remote'
			));
		} else {
			return null;
		}

		if (!isset($resource['filename'])) { /* PHP < 5.2.0 */
			$length = isset($resource['extension']) ? strlen($resource['extension']) + 1 : 0;
			$resource['filename'] = substr($resource['basename'], 0, - $length);
		}
		return $resource;
	}

/**
 * Returns a relative path to the destination file
 *
 * @param array $source Information about the source
 * @return string
 */
	function transferTo(&$Model, $via, $from) {
		extract($from);

		$irregular = array(
			'image' => 'img',
			'text' => 'txt'
		);
		$name = Mime_Type::guessName($mimeType ? $mimeType : $file);

		if (isset($irregular[$name])) {
			$short = $irregular[$name];
		} else {
			$short = substr($name, 0, 3);
		}

		$path  = $short . DS;
		$path .= strtolower(Inflector::slug($filename));
		$path .= !empty($extension) ? '.' . strtolower($extension) : null;

		return $path;
	}

/**
 * Prepares (if neccessary) and performs a transfer
 *
 * Please note that if a file with the same name as the destination exists,
 * it will be silently overwritten.
 *
 * @param Model $Model
 * @param mixed $file File from which source, temporary and destination are derived
 * @return string|boolean Destination file on success, false on failure
 */
	function transfer(&$Model, $file) {
		if ($this->runtime[$Model->alias]['hasPerformed']) {
			$this->runtime[$Model->alias] = $this->_defaultRuntime;
			$this->runtime[$Model->alias]['hasPerformed'] = true;
		}
		if (!$this->runtime[$Model->alias]['isPrepared']) {
			if (!$this->_prepare($Model, $file)) {
				return false;
			}
		}
		extract($this->runtime[$Model->alias]);

		if ($source['type'] === 'http-url-remote') {
			if (!class_exists('HttpSocket')) {
				App::import('Core','HttpSocket');
			}
			$Socket = new HttpSocket(array('timeout' => 5));
			$Socket->request(array('method' => 'GET', 'uri' => $source['file']));

			if (!empty($Socket->error) || $Socket->response['status']['code'] != 200) {
				return false;
			}
		}

		$chain = implode('>>', array($source['type'], $temporary['type'], $destination['type']));
		$result = false;

		switch ($chain) {
			case 'file-upload-remote>>uploaded-file-local>>file-local':
				$result = move_uploaded_file($temporary['file'], $destination['file']);
				break;
			case 'file-local>>>>file-local':
				$result = copy($source['file'], $destination['file']);
				break;
			case 'file-local>>file-local>>file-local':
				if (copy($source['file'], $temporary['file'])) {
					$result = rename($temporary['file'], $destination['file']);
				}
				break;
			case 'http-url-remote>>>>file-local':
				$result = file_put_contents($destination['file'], $Socket->response['body']);
				break;
			case 'http-url-remote>>file-local>>file-local':
				if (file_put_contents($temporary['file'], $Socket->response['body'])) {
					$result = rename($temporary['file'], $destination['file']);
				}
				break;
		}
		return $result ? $destination['file'] : false;
	}

/**
 * Convenience method which (if available) returns absolute path to last transferred file
 *
 * @param Model $Model
 * @return string|boolean
 */
	function transferred(&$Model) {
		extract($this->runtime[$Model->alias], EXTR_SKIP);
		return isset($destination['file']) ? $destination['file'] : false;
	}

/**
 * Triggered by `beforeValidate` and `transfer()`
 *
 * @param Model $Model
 * @param array|string $resource Transfer resource
 * @return boolean true if transfer is ready to be performed, false on error
 */
	function _prepare(&$Model, $resource) {
		$this->runtime[$Model->alias]['isPrepared'] = true;

		extract($this->settings[$Model->alias], EXTR_SKIP);
		extract($this->runtime[$Model->alias], EXTR_SKIP);

		if ($source = $this->_source($Model, $resource)) {
			$this->runtime[$Model->alias]['source'] = $source;
		} else {
			return false;
		}

		if ($source['type'] !== 'file-local') {
			$temporary = $this->runtime[$Model->alias]['temporary'] = $this->_temporary($Model, $resource);
		}

		if (!$file = $Model->transferTo($temporary, $source)) {
			$message = "TransferBehavior::_prepare - Could not obtain destination file path.";
			trigger_error($message, E_USER_NOTICE);
			return false;
		}
		$file = $transferDirectory . $file;

		if (!$overwrite) {
			if (!$file = $this->_alternativeFile($file, $alternativeFile)) {
				$message  = "TransferBehavior::_prepare - ";
				$message .= "Exceeded number of max. tries while finding alternative file name.";
				trigger_error($message, E_USER_NOTICE);
				return false;
			}
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
			$message  = "TransferBehavior::_prepare - Directory `{$destination['dirname']}` could ";
			$message .= "not be created or is not writable. Please check the permissions.";
			trigger_error($message, E_USER_WARNING);
			return false;
		}
		return true;
	}

/**
 * Parse data to be used as source
 *
 * @param Model $Model
 * @param array|string $resource Transfer resource good for deriving the source data from it
 * @return array|boolean Parsed results on success, false on error
 * @todo evaluate errors in file uploads
 */
	function _source(&$Model, $resource) {
		if (TransferValidation::fileUpload($resource)) {
			return array_merge(
				$this->transferMeta($Model, $resource),
				array('error' => $resource['error'])
			);
		} elseif (MediaValidation::file($resource)) {
			return $this->transferMeta($Model, $resource);
		} elseif (TransferValidation::url($resource, array('scheme' => 'http'))) {
			return $this->transferMeta($Model, $resource);
		}
		return false;
	}

/**
 * Parse data to be used as temporary
 *
 * @param Model $Model
 * @param array|string $resource Transfer resource good for deriving the temporary data from it
 * @return array|boolean Parsed results on success, false on error
 */
	function _temporary(&$Model, $resource) {
		if (TransferValidation::fileUpload($resource)
		&& TransferValidation::uploadedFile($resource['tmp_name'])) {
			return array_merge(
				$this->transferMeta($Model, $resource['tmp_name']),
				array('error' => $resource['error'])
			);
		} elseif (MediaValidation::file($resource)) {
			return $this->transferMeta($Model, $resource);
		}
		return false;
	}

/**
 * Parse data to be used as destination
 *
 * @param Model $Model
 * @param array|string $resource Transfer resource good for deriving the destination data from it
 * @return array|boolean Parsed results on success, false on error
 */
	function _destination(&$Model, $resource) {
		if (MediaValidation::file($resource , false)) {
			return $this->transferMeta($Model, $resource);
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

		foreach ((array)$allow as $allowed) {
			if (strpos(':', $allowed) !== false) {
				$message  = "TransferBehavior::checkLocation - ";
				$message .= "Makers cannot be used in parameters for this method anymore. ";
				$message .= "Please use predefined constants instead.";
				trigger_error($message, E_USER_NOTICE);
			}
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
			$length = strlen(pathinfo($name, PATHINFO_EXTENSION));
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
 * Triggered by `beforeValidate` and `transfer()`
 *
 * @param Model $Model
 * @param string $file A valid transfer resource to be used as source
 * @return boolean true if transfer is ready to be performed, false on error
 * @deprecated
 */
	function prepare(&$Model, $file = null) {
		$message  = "TransferBehavior::prepare - ";
		$message .= "Has been deprecated. Preparation is now handled by `transfer()`.";
		trigger_error($message, E_USER_NOTICE);
		return false;
	}

/**
 * Performs a transfer
 *
 * @param Model $Model
 * @return boolean true on success, false on failure
 * @deprecated
 */
	function perform(&$Model) {
		$message  = "TransferBehavior::perform - ";
		$message .= "Has been deprecated in favor of `transfer()`";
		trigger_error($message, E_USER_WARNING);
		return false;
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
		$message .= "Has been deprecated in favor of `transferred()`.";
		trigger_error($message, E_USER_NOTICE);
		return $this->transferred($Model);
	}

/**
 * Resets runtime property
 *
 * @param Model $Model
 * @return void
 * @deprecated
 */
	function reset(&$Model) {
		$message  = "TransferBehavior::reset - ";
		$message .= "Has been deprecated. It's not necessarry to directly call the method anymore.";
		trigger_error($message, E_USER_NOTICE);
		$this->runtime[$Model->alias] = $this->_defaultRuntime;

	}

/**
 * Gather/Return information about a resource
 *
 * @param mixed $resource Path to file in local FS, URL or file-upload array
 * @param string $what scheme, host, port, file, MIME type, size, permission,
 * 	dirname, basename, filename, extension or type
 * @return mixed
 * @deprecated
 */
	function info(&$Model, $resource, $what = null) {
		$message  = "TransferBehavior::info - ";
		$message .= "Has been deprecated in favor of `transferMeta()` which - ";
		$message .= "unlike `info()` - does not accept a 3rd parameter.";
		trigger_error($message, E_USER_NOTICE);
		return $this->transferMeta($Model, $resource);
	}

}
?>