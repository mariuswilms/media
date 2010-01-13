<?php
/**
 * Medium File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs.medium
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.MimeType');
/**
 * Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class Medium extends Object {
/**
 * Name of the Medium
 * e.g. Image
 *
 * @var string
 */
	var $name;
/**
 * Abbreviated name of the name
 * e.g. img
 *
 * @var string
 */
	var $short;
/**
 * These Adapters will be tried to be loaded in given order
 * works similiar to helpers or components properterties
 *
 * @var array
 * @access public
 */
	var $adapters = array();
/**
 * Holds a reference to the 'original' or 'temporary' file of the files property
 *
 * @var string
 */
	var $file;
/**
 * Related files
 *
 * @var array
 */
	var $files = array();
/**
 * Related open resources keyed by resource type
 *
 * @var array
 */
	var $resources = array();
/**
 * Related objects keyed by class name
 *
 * @var array
 */
	var $objects = array();
/**
 * Related contents (text or binary)
 * The 'raw' key of this property holds - if present -
 * a dump of the complete files contents
 *
 * @var array
 */
	var $contents = array();
/**
 * The current MIME type
 *
 * @var string
 */
	var $mimeType;
/**
 * Mapping MIME type (part) to medium name
 *
 * @var array
 */
	static $_mimeTypesToNames = array(
		'application/ogg'		=> 'Audio',
		'application/pdf'       => 'Document',
		'application/msword'    => 'Document',
		'image/icon'            => 'Icon',
		'text/css'              => 'Css',
		'text/javascript'       => 'Js',
		'text/code'             => 'Generic',
		'text/rtf'              => 'Document',
		'text/plain'            => 'Text',
		'image/'                => 'Image',
		'audio/'                => 'Audio',
		'video/'                => 'Video',
		'text/'                 => 'Generic',
		'/'                     => 'Generic',
	);
/**
 * Mapping medium name to short medium name
 *
 * @var array
 */
	static $_namesToShort = array(
		'Audio'    => 'aud',
		'Css'      => 'css',
		'Document' => 'doc',
		'Generic'  => 'gen',
		'Icon'     => 'ico',
		'Image'    => 'img',
		'Js'       => 'js',
		'Text'     => 'txt',
		'Video'    => 'vid',
	);
/**
 * Constructor
 *
 * Possible values for $file:
 * 	string containing absolute path to a file (used as 'original')
 * 	resource
 *  object
 *  array of resources
 *  array of objects
 *  array of absolute paths to files
 *
 * @param mixed $file See description above
 * @param string $mimeType A valid MIME type, provide if autodetection fails upon $file
 * 	or to save the cost for an extra MimeType::detectType call
 */
	function __construct($file, $mimeType = null) {
		if (is_resource($file)) {
			$this->resources[get_resource_type($file)] = $file;
		} elseif (is_object($file)) {
			$this->objects[get_class($file)] = $file;
		} elseif (is_array($file)) {
			if (is_string(current($file))) {
				$this->files = $file;
			} elseif (is_resource(current($file))) {
				$this->resources = $file;
			} elseif (is_object(current($file))) {
				$this->objects = $file;
			}
		} else {
			$this->files['original'] = $file;
		}

		if (isset($this->files['original'])) {
			$this->file =& $this->files['original'];
		} elseif (isset($this->files['temporary'])) {
			$this->file =& $this->files['temporary'];
		}

		if ($mimeType === null) {
			$mimeType = MimeType::guessType($this->file);
		}

		$this->mimeType = $mimeType;
		$this->name = self::name(null, $mimeType);
		$this->short = self::short(null, $mimeType);

		$this->Adapters = new MediumAdapterCollection();
		$this->Adapters->init($this, $this->adapters);
	}
/**
 * Destructor
 *
 * Deletes temporary files
 *
 * @return void
 */
	function __destruct() {
		if (isset($this->files['temporary']) && file_exists($this->files['temporary'])) {
			unlink($this->files['temporary']);
		}
	}
/**
 * Overriden magic method
 *
 * @param string $method
 * @param array $args
 * @return void
 */
	function __call($method, $args) {
	}
/**
 * Factory method
 *
 * Takes a file and determines type of medium to use for it
 * Falls back to generic medium
 *
 * @param mixed $file See description of the constructor
 * @param string $mimeType Sets the mimeType of the new medium
 * @return object
 */
	static function &factory($file, $mimeType = null) {
		if ($mimeType === null) {
			$mimeType = MimeType::guessType($file, array('experimental' => false));
		}

		$name = Medium::name(null, $mimeType);
		$class = $name . 'Medium';

		if (!class_exists($class)) {
			App::import('Vendor', 'Media.' . $class, array(
				'file' => 'medium' . DS . strtolower($name) . '.php'
			));
		}

		$Object = new $class($file, $mimeType);
		return $Object;
	}
/**
 * Determines medium name for a file or MIME type
 *
 * In the case of there are no arguments passed to this method
 * the values of $_mimeTypesToNames are returned
 *
 * @param string $file
 * @param string $mimeType
 * @return mixed
 */
	static function name($file = null, $mimeType = null) {
		if ($file === null && $mimeType === null) {
			return array_values(self::$_mimeTypesToNames);
		}
		if ($mimeType === null) {
			$mimeType = MimeType::guessType($file);
		}
		$mimeType = MimeType::simplify($mimeType);

		foreach (self::$_mimeTypesToNames as $mapMimeType => $name) {
			if (strpos($mimeType, $mapMimeType) !== false) {
				return $name;
			}
		}
		return 'Generic';
	}
/**
 * Determines medium short name for a file or MIME type
 *
 * In the case of there are no arguments passed to this method
 * the values of $_nameToShort are returned
 *
 * @param string $file
 * @param string $mimeType
 * @return mixed
 */
	static function short($file = null, $mimeType = null) {
		if ($file === null && $mimeType === null) {
			return array_values(self::$_namesToShort);
		}
		return self::$_namesToShort[self::name($file, $mimeType)];
	}
/**
 * Automatically processes a file and returns a Medium instance
 *
 * Possible values for $instructions:
 * 	array('name of method', 'name of other method')
 *  array('name of method' => array('arg1', 'arg2'))
 *
 * @param string $file Absolute path to a file
 * @param array $instructions See description above
 * @return object
 */
	static function make($file, $instructions = array()) {
		$Medium = Medium::factory($file);

		foreach ($instructions as $key => $value) {
			if (is_int($key)) {
				$method = $value;
				$args = null;
			} else {
				$method = $key;
				if (is_array($value)) {
					$args = $value;
				} else {
					$args = array($value);
				}
			}

			if (!method_exists($Medium, $method)) {
				$message  = "Medium::make - Invalid instruction ";
				$message .= "`" . get_class($Medium) . "::{$method}()`.";
				trigger_error($message, E_USER_WARNING);
				return false;
			}

			$result = call_user_func_array(array($Medium, $method), $args);

			if ($result === false) {
				$message  = "Medium::make - Instruction ";
				$message .=  "`" . get_class($Medium) . "::{$method}()` failed.";
				trigger_error($message, E_USER_WARNING);
				return false;
			} elseif (is_a($result, 'Medium')) {
				$Medium = $result;
			}
		}
		return $Medium;
	}
/**
 * Stores the medium to a file and assures that the output file has the correct extension
 *
 * @param string $file Absolute path to a file
 * @param boolean $overwrite Enable overwriting of an existent file
 * @return mixed
 */
	function store($file, $overwrite = false) {
		$File = new File($file);

		if ($overwrite) {
			$File->delete();
		}
		if ($File->exists()) {
			$message = "Medium::store - File `{$file}` already exists.";
			trigger_error($message, E_USER_NOTICE);
			return false;
		}

		$file = $File->Folder->pwd() . DS . $File->name();
		$correctExtension = MimeType::guessExtension($this->mimeType);

		if ($correctExtension) {
			$file .= '.' . $correctExtension;
		} elseif (isset($extension)) {
			$file .= '.' . $File->ext();
		}

		if ($this->Adapters->dispatchMethod($this, 'store', array($file))) {
			return $file;
		}
		return false;
	}
/**
 * Convert
 *
 * @param string $mimeType
 * @return boolean|object false on error or a Medium object on success
 */
	function convert($mimeType) {
		$result = $this->Adapters->dispatchMethod($this, 'convert', array($mimeType));

		if (!$result) {
			return false;
		}

		$this->mimeType = $mimeType;

		if (is_a($result, 'Medium')) {
			return $result;
		}
		return $this;
	}
/**
 * Figures out which known ratio is closest to provided one
 *
 * @param integer $width
 * @param integer_type $height
 * @return string
 */
	function _knownRatio($width, $height) {
		if (empty($width) || empty($height)) {
			return null;
		}

		$knownRatios = array(
			'1:1.294' => 1/1.294,
			'1:1.545' => 1/1.1545,
			'4:3'     => 4/3,
			'1.375:1' => 1.375,
			'3:2'     => 3/2,
			'16:9'    => 16/9,
			'1.85:1'  => 1.85,
			'1.96:1'  => 1.96,
			'2.35:1'  => 2.35,
			'√2:1'    => pow(2, 1/2), /* dina4 quer */
			'1:√2'    => 1 / (pow(2, 1/2)), /* dina4 hoch */
			'Φ:1'     => (1 + pow(5,1/2)) / 2, /* goldener schnitt */
			'1:Φ'     => 1 / ((1 + pow(5,1/2)) / 2), /* goldener schnitt */
		);

		foreach ($knownRatios as $knownRatioName => &$knownRatio) {
			$knownRatio = abs(($width / $height) - $knownRatio);
		}

		asort($knownRatios);
		return array_shift(array_keys($knownRatios));
	}
}
/**
 * Medium Adapter Collection Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class MediumAdapterCollection extends Object {
/**
 * Attached adapter names
 *
 * @var array
 */
	var $_attached = array();
/**
 * Initialized adapter names
 *
 * @var array
 */
	var $_initialized = array();
/**
 * Mapped methods of adapters
 *
 * Keyed by method name
 * @var array
 */
	var $__methods = array();
/**
 * Errors
 *
 * @var boolean|array
 * @access private
 */
	var $__errors = false;
/**
 * Messages
 *
 * @var array
 * @access private
 */
	var $__messages = array();
/**
 * Attaches $adapters to $Medium
 *
 * @param object $Medium
 * @param array $adapters
 */
	function init($Medium, $adapters = array()) {
		foreach (Set::normalize($adapters) as $adapter => $config) {
			$this->attach($adapter, $config);
		}
	}
/**
 * Attaches $adapter and inits it
 *
 * @param string $adapter
 * @param array $config
 * @return boolean
 */
	function attach($adapter, $config) {
		$class = $adapter . 'MediumAdapter';
		$file = 'medium' . DS . 'adapter' . DS . Inflector::underscore($adapter) . '.php';

		if (!class_exists($class)
		&& !App::import('Vendor', 'Media.' . $class, array('file' => $file))) {
			$message = "MediumAdapterCollection::attach() - Adapter `{$adapter}` not found!";
			$this->__errors[] = $message;
			return false;
		}

		$this->{$adapter} = new $class();
		$this->_attached[] = $adapter;
		return true;
	}
/**
 * Detaches adapter and does some cleanup
 *
 * @param string $name
 */
	function detach($name) {
		$this->_attached = array_diff($this->_attached, (array)$name);
		$this->_initialized = array_diff($this->_initialized, (array)$name);
		$this->_overlay($this->_initialized);
		$this->__messages[] = "MediumCollection::detach() - Removed `{$name}MediumAdapter`.";
	}
/**
 * Calls a method of an adapter providing it
 * Loads and initiates the adapter if necessary
 *
 * @param string $method
 * @param array $args
 * @return mixed
 */
	function dispatchMethod($Medium, $method, $params = array(), $options = array()) {
		$options += array('normalize' => false);

		if (!is_array($params)) {
			$params = (array)$params;
		}
		array_unshift($params, $Medium);

		if (isset($this->__methods[$method])) {
			list($method, $name) = $this->__methods[$method];

			$message  = "MediumCollection::dispatchMethod() - ";
			$message .= "Calling `{$name}MediumAdapter::{$method}()`.";
			$this->__messages[] = $message;

			$result = $this->{$name}->dispatchMethod($method, $params);
			return $options['normalize'] ? $this->_normalize($result) : $result;
		}

		foreach ($this->_attached as $adapter) {
			if (!method_exists($this->{$adapter}, $method)) { /* optional */
				continue;
			}

			if (!$this->_initialized($adapter)) {
				if ($this->_initialize($Medium, $adapter)) {
					$message  = "MediumCollection::dispatchMethod() - ";
					$message .= "Initialized `{$adapter}MediumAdapter`.";
					$this->__messages[] = $message;

					$this->_overlay($adapter);
				} else {
					$message  = "MediumCollection::dispatchMethod() - ";
					$message .= "Adapter `{$adapter}` failed to initialize.";
					$this->__errors[] = $message;

					$this->detach($adapter);
					continue;
				}
			}

			if (isset($this->__methods[$method])) {
				list($method, $name) = $this->__methods[$method];

				$message  = "MediumCollection::dispatchMethod() - ";
				$message .= "Calling `{$adapter}MediumAdapter::{$method}()`.";
				$this->__messages[] = $message;

				$result = $this->{$name}->dispatchMethod($method, $params);
				return $options['normalize'] ? $this->_normalize($result) : $result;
			}
		}
		$message = "MediumCollection::dispatchMethod() - ";
		$message .= "Method `{$method}` not found in any attached adapter";
		$this->__errors[] = $message;
	}
/**
 * Checks if $adapter is compatible and initializes it with $Medium
 *
 * @param object $Medium
 * @param string $adapter
 * @return boolean
 */
	function _initialize($Medium, $adapter) {
		if (!in_array($adapter, $this->_attached)) {
			return false;
		}
		if (in_array($adapter, $this->_initialized)) {
			return true;
		}
		if (!$this->{$adapter}->compatible($Medium) || !$this->{$adapter}->initialize($Medium)) {
			return false;
		}
		$this->_initialized[] = $adapter;
		return true;
	}
/**
 * Checks if an adapter is already initialized
 * or returns currently initialized adapters
 *
 * @param string $name
 * @return mixed
 */
	function _initialized($name = null) {
		if (!empty($name)) {
			return in_array($name, $this->_initialized);
		}
		return $this->_initialized;
	}
/**
 * Adds methods of adapter(s)
 *
 * @param mixed $name
 * @return void
 */
	function _overlay($name) {
		foreach ((array)$name as $adapter) {
			foreach (get_class_methods($this->{$adapter}) as $method) {
				if ($method[0] !== '_') {
					$this->__methods[$method] = array($method, $adapter);
				}
			}
		}
		$this->__messages[] = "MediumCollection::_overlay() - Regenerated method overlays.";
	}
/**
 * Normalizes a value
 *
 * @param mixed $value
 * @param string $type
 * @access protected
 * @return mixed
 */
	function _normalize($value) {
		if (is_numeric($value)) {
			$value = (integer)$value;
		} elseif (is_string($value)) {
			$value = trim($value);
		}
		if (!empty($value)) {
			return $value;
		}
	}
/**
 * Returns messages for this Object
 *
 * @return array
 */
	function messages() {
		return $this->__messages;
	}
/**
 * Returns errors for this Object
 *
 * @return mixed
 */
	function errors() {
		return $this->__errors;
	}
}
/**
 * Medium Adapter Class
 *
 * Base Class for adapters
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class MediumAdapter extends Object {
/**
 * Used by the compatible method
 *
 * @var array
 */
	var $require;
/**
 * Method for checking if the adapter is going to work with the provided $Medium
 *
 * Called before the adapter is going to be initialized
 * May be overridden
 *
 * @param object $Medium
 * @return boolean
 */
	function compatible($Medium) {
		$default = array(
			/* sourceFile must have one out of given MIME types */
			'mimeTypes' => array(),
			/* PHP extensions which must be loaded */
			'extensions' => array(),
			/* Functions that must exist */
			'functions' => array(),
			/* Files that are required */
			'imports' => array(),
			/* System commands which must be whichable */
			'commands' => array(),
		);
		$require = array_merge($default, $this->require);

		if (!empty($require['mimeTypes'])) {
			if (!in_array(MimeType::simplify($Medium->mimeType), $require['mimeTypes'])) {
				return false;
			}
		}
		foreach ($require['extensions'] as $check) {
			if (!extension_loaded($check)) {
				return false;
			}
		}
		foreach ($require['functions'] as $check) {
			if (!function_exists($check)) {
				return false;
			}
		}
		foreach ($require['commands'] as $check) {
			if (!$this->_which($check)) {
				return false;
			}
		}
		foreach ($require['imports'] as $import) {
			if (!App::import($import)) {
				return false;
			}
		}
		return true;
	}
/**
 * To-be-overridden
 *
 * Called after compatible
 *
 * @param object $Medium
 * @return boolean
 */
	function initialize($Medium) {
		return true;
	}
/**
 * Do system calls
 *
 * @param string $string A string containing placeholders e.g. :command: -f xy
 * @param array $data Data to be filled into the marker string
 * @return boolean
 */
	function _execute($string, $data) {
		if (!$data['command'] = $this->_which($data['command'])) {
			return false;
		}
		$line = String::insert($string, $data, array(
			'before' => ':', 'after' => ':', 'clean' => true
		));
		exec(escapeshellcmd($line) , $output, $return);
		return $return !== 0 ? false : (empty($output) ? true : array_pop($output));
	}
/**
 * Helper method to determine the absolute path to a executable
 *
 * @param string $command
 * @return mixed
 */
	function _which($command) {
		static $found = array();

		if (is_array($command)) {
			foreach ($command as $c) {
				if ($result = $this->_which($c)) {
					return $result;
				}
			}
			return false;
		}

		if (isset($found[$command])) {
			return $found[$command];
		}

		if (!ini_get('safe_mode')) {
			$paths = ini_get('safe_mode_exec_dir');
		}
		if (!isset($paths)) {
			$paths = env('PATH');
		}
		$paths = explode(PATH_SEPARATOR, $paths);
		$paths[] = getcwd();

		$os = env('OS');
		$windows = !empty($os) && strpos($os, 'Windows') !== false;

		if (!$windows) {
			exec('which ' . $command . ' 2>&1', $output, $return);

			if ($return == 0) {
				return $found[$command] = current($output);
			}
		}

		if ($windows) {
			if($extensions = env('PATHEXT')) {
				$extensions = explode(PATH_SEPARATOR, $extensions);
			} else {
				$extensions = array('exe', 'bat', 'cmd', 'com');
			}
		}
		$extensions[] = '';

		foreach ($paths as $path) {
			foreach($extensions as $extension) {
				$file = $path . DS . $command;

				if (!empty($extension)) {
					$file .= '.' . $extension;
				}

				if (is_file($file)) {
					return $found[$command] = $file;
				}
			}
		}
		return false;
	}
}
?>