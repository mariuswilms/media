<?php
/**
 * Mime Glob Database File
 *
 * MIME-TYPES and the extensions that represent them
 * Based on debian's "mime-support" by Brian White
 *
 * @package    media
 * @subpackage media.libs
 * @link       http://github.com/davidpersson/media
 * @link       http://packages.debian.org/en/etch/mime-support
 */
$config = array(
	/* Application */
	array(
		'mime_type' => 'application/x-java',
		'pattern' => array('class')
		),
	array(
		'mime_type' => 'application/javascript',
		'pattern' => array('js')
		),
	array(
		'mime_type' => 'application/java-archive',
		'pattern' => array('jar')
		),
	array(
		'mime_type' => 'application/msaccess',
		'pattern' => array('mdb')
		),
	array(
		'mime_type' => 'application/msword',
		'pattern' => array('doc', 'dot')
		),
	array(
		'mime_type' => 'application/octet-stream',
		'pattern' => array('bin')
		),
	array(
		'mime_type' => 'application/ogg',
		'pattern' => array('ogg')
		),
	array(
		'mime_type' => 'application/pdf',
		'pattern' => array('pdf')
		),
	array(
		'mime_type' => 'application/pgp-keys',
		'pattern' => array('key')
		),
	array(
		'mime_type' => 'application/pgp-signature',
		'pattern' => array('pgp')
		),
	array(
		'mime_type' => 'application/postscript',
		'pattern' => array('ps', 'ai', 'eps')
		),
	array(
		'mime_type' => 'application/rar',
		'pattern' => array('rar')
		),
	array(
		'mime_type' => 'application/rdf+xml',
		'pattern' => array('rdf')
		),
	array(
		'mime_type' => 'application/rss+xml',
		'pattern' => array('rss')
		),
	array(
		'mime_type' => 'application/smil',
		'pattern' => array('smi', 'smil')
		),
	array(
		'mime_type' => 'application/xhtml+xml',
		'pattern' => array('xhtml', 'xht')
		),
	array(
		'mime_type' => 'application/xml',
		'pattern' => array('xml', 'xsl')
		),
	array(
		'mime_type' => 'application/zip',
		'pattern' => array('zip')
		),
	array(
		'mime_type' => 'application/vnd.mozilla.xul+xml',
		'pattern' => array('xul')
		),
	array(
		'mime_type' => 'application/vnd.ms-excel',
		'pattern' => array('xls', 'xlb', 'xlt')
		),
	array(
		'mime_type' => 'application/vnd.ms-powerpoint',
		'pattern' => array('ppt', 'pps')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.chart',
		'pattern' => array('odc')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.database',
		'pattern' => array('odb')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.formula',
		'pattern' => array('odf')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.graphics',
		'pattern' => array('odg')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.graphics-template',
		'pattern' => array('otg')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.image',
		'pattern' => array('odi')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.presentation',
		'pattern' => array('odp')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.presentation-template',
		'pattern' => array('otp')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.spreadsheet',
		'pattern' => array('ods')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.spreadsheet-template',
		'pattern' => array('ots')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.text',
		'pattern' => array('odt')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.text-master',
		'pattern' => array('odm')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.text-template',
		'pattern' => array('ott')
		),
	array(
		'mime_type' => 'application/vnd.oasis.opendocument.text-web',
		'pattern' => array('oth')
		),
	array(
		'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'pattern' => array('docx')
		),
	array(
		'mime_type' => 'application/vnd.visio',
		'pattern' => array('vsd')
		),
	array(
		'mime_type' => 'application/vnd.wap.wbxml',
		'pattern' => array('wbxml')
		),
	array(
		'mime_type' => 'application/vnd.wap.wmlc',
		'pattern' => array('wmlc')
		),
	array(
		'mime_type' => 'application/vnd.wap.wmlscriptc',
		'pattern' => array('wmlsc')
		),
	array(
		'mime_type' => 'application/x-123',
		'pattern' => array('wk')
		),
	array(
		'mime_type' => 'application/x-abiword',
		'pattern' => array('abw')
		),
	array(
		'mime_type' => 'application/x-apple-diskimage',
		'pattern' => array('dmg')
		),
	array(
		'mime_type' => 'application/x-bcpio',
		'pattern' => array('bcpio')
		),
	array(
		'mime_type' => 'application/x-bittorrent',
		'pattern' => array('torrent')
		),
	array(
		'mime_type' => 'application/x-bzip',
		'pattern' => array('bz2', 'bz')
		),
	array(
		'mime_type' => 'application/x-cpio',
		'pattern' => array('cpio')
		),
	array(
		'mime_type' => 'application/x-csh',
		'pattern' => array('csh')
		),
	array(
		'mime_type' => 'application/x-debian-package',
		'pattern' => array('deb', 'udeb')
		),
	array(
		'mime_type' => 'application/x-doom',
		'pattern' => array('wad')
		),
	array(
		'mime_type' => 'application/x-dvi',
		'pattern' => array('dvi')
		),
	array(
		'mime_type' => 'application/x-font',
		'pattern' => array('pfa', 'pfb', 'gsf', 'pcf', 'pcf.Z')
		),
	array(
		'mime_type' => 'application/x-gtar',
		'pattern' => array('gtar', 'tgz', 'taz')
		),
	array(
		'mime_type' => 'application/x-gettext-translation',
		'pattern' => array('mo')
		),

	array(
		'mime_type' => 'application/x-gzip',
		'pattern' => array('gz')
		),
	array(
		'mime_type' => 'application/x-iso9660-image',
		'pattern' => array('iso')
		),
	array(
		'mime_type' => 'application/x-latex',
		'pattern' => array('latex')
		),
	array(
		'mime_type' => 'application/x-lha',
		'pattern' => array('lha')
		),
	array(
		'mime_type' => 'application/x-lzh',
		'pattern' => array('lzh')
		),
	array(
		'mime_type' => 'application/x-lzx',
		'pattern' => array('lzx')
		),
	array(
		'mime_type' => 'application/x-msdos-program',
		'pattern' => array('com', 'exe', 'bat', 'dll')
		),
	array(
		'mime_type' => 'application/x-msi',
		'pattern' => array('msi')
		),
	array(
		'mime_type' => 'application/x-python-code',
		'pattern' => array('pyc', 'pyo')
		),
	array(
		'mime_type' => 'application/x-redhat-package-manager',
		'pattern' => array('rpm')
		),
	array(
		'mime_type' => 'application/x-sh',
		'pattern' => array('sh')
		),
	array(
		'mime_type' => 'application/x-shockwave-flash',
		'pattern' => array('swf', 'swfl')
		),
	array(
		'mime_type' => 'application/x-stuffit',
		'pattern' => array('sit')
		),
	array(
		'mime_type' => 'application/x-tar',
		'pattern' => array('tar')
		),
	array(
		'mime_type' => 'application/x-wingz',
		'pattern' => array('wz')
		),
	array(
		'mime_type' => 'application/x-x509-ca-cert',
		'pattern' => array('crt')
		),
	array(
		'mime_type' => 'application/x-xcf',
		'pattern' => array('xcf')
		),
	/* Audio */
	array(
		'mime_type' => 'application/x-xpinstall',
		'pattern' => array('xpi')
		),
	array(
		'mime_type' => 'audio/basic',
		'pattern' => array('au', 'snd')
		),
	array(
		'mime_type' => 'audio/x-flac',
		'pattern' => array('flac')
		),
	array(
		'mime_type' => 'audio/midi',
		'pattern' => array('mid', 'midi', 'kar')
		),
	array(
		'mime_type' => 'audio/mpeg',
		'pattern' => array('mpga', 'mpega', 'mp2', 'mp3', 'm4a')
		),
	array(
		'mime_type' => 'audio/mpegurl',
		'pattern' => array('m3u')
		),
	array(
		'mime_type' => 'audio/x-aiff',
		'pattern' => array('aif', 'aiff', 'aifc')
		),
	array(
		'mime_type' => 'audio/x-mpegurl',
		'pattern' => array('m3u')
		),
	array(
		'mime_type' => 'audio/x-ms-wma',
		'pattern' => array('wma')
		),
	array(
		'mime_type' => 'audio/x-pn-realaudio',
		'pattern' => array('ra', 'rm', 'ram')
		),
	array(
		'mime_type' => 'audio/x-realaudio',
		'pattern' => array('ra')
		),
	array(
		'mime_type' => 'audio/x-wav',
		'pattern' => array('wav')
		),
	/* Image */
	array(
		'mime_type' => 'image/gif',
		'pattern' => array('gif')
		),
	array(
		'mime_type' => 'image/jpeg',
		'pattern' => array('jpeg', 'jpg', 'jpe')
		),
	array(
		'mime_type' => 'image/pcx',
		'pattern' => array('pcx')
		),
	array(
		'mime_type' => 'image/png',
		'pattern' => array('png')
		),
	array(
		'mime_type' => 'image/svg+xml',
		'pattern' => array('svg', 'svgz')
		),
	array(
		'mime_type' => 'image/tiff',
		'pattern' => array('tiff', 'tif')
		),
	array(
		'mime_type' => 'image/vnd.wap.wbmp',
		'pattern' => array('wbmp')
		),
	array(
		'mime_type' => 'image/x-coreldraw',
		'pattern' => array('cdr')
		),
	array(
		'mime_type' => 'image/x-corelphotopaint',
		'pattern' => array('cpt')
		),
	array(
		'mime_type' => 'image/x-icon',
		'pattern' => array('ico')
		),
	array(
		'mime_type' => 'image/x-ms-bmp',
		'pattern' => array('bmp')
		),
	array(
		'mime_type' => 'image/x-photoshop',
		'pattern' => array('psd')
		),
	array(
		'mime_type' => 'image/x-xbitmap',
		'pattern' => array('xbm')
		),
	array(
		'mime_type' => 'image/x-xpixmap',
		'pattern' => array('xpm')
		),
	/* Text */
	array(
		'mime_type' => 'text/comma-separated-values',
		'pattern' => array('csv')
		),
	array(
		'mime_type' => 'text/css',
		'pattern' => array('css')
		),
	array(
		'mime_type' => 'text/h323',
		'pattern' => array('323')
		),
	array(
		'mime_type' => 'text/html',
		'pattern' => array('html', 'htm', 'shtml')
		),
	array(
		'mime_type' => 'text/x-gettext-translation',
		'pattern' => array('po')
		),
	array(
		'mime_type' => 'text/x-gettext-translation-template',
		'pattern' => array('pot')
		),
	array(
		'mime_type' => 'text/mathml',
		'pattern' => array('mml')
		),
	array(
		'mime_type' => 'text/plain',
		'pattern' => array('asc', 'txt', 'text', 'diff', 'pot')
		),
	array(
		'mime_type' => 'text/rtf',
		'pattern' => array('rtf')
		),
	array(
		'mime_type' => 'text/tab-separated-values',
		'pattern' => array('tsv')
		),
	array(
		'mime_type' => 'text/x-bibtex',
		'pattern' => array('bib')
		),
	array(
		'mime_type' => 'text/x-cake-template',
		'pattern' => array('ctp', 'thtml')
		),
	array(
		'mime_type' => 'text/x-c++hdr',
		'pattern' => array('h++', 'hpp', 'hxx', 'hh')
		),
	array(
		'mime_type' => 'text/x-c++src',
		'pattern' => array('c++', 'cpp', 'cxx', 'cc')
		),
	array(
		'mime_type' => 'text/x-chdr',
		'pattern' => array('h')
		),
	array(
		'mime_type' => 'text/x-csh',
		'pattern' => array('csh')
		),
	array(
		'mime_type' => 'text/x-csrc',
		'pattern' => array('c')
		),
	array(
		'mime_type' => 'text/x-haskell',
		'pattern' => array('hs')
		),
	array(
		'mime_type' => 'text/x-java',
		'pattern' => array('java')
		),
	array(
		'mime_type' => 'text/x-literate-haskell',
		'pattern' => array('lhs')
		),
	array(
		'mime_type' => 'text/x-pascal',
		'pattern' => array('p', 'pas')
		),
	array(
		'mime_type' => 'text/x-perl',
		'pattern' => array('pl', 'pm')
		),
	array(
		'mime_type' => 'text/x-php',
		'pattern' => array('php', 'php3', 'php4', 'php5')
		),
	array(
		'mime_type' => 'text/x-python',
		'pattern' => array('py')
		),
	array(
		'mime_type' => 'text/x-sh',
		'pattern' => array('sh')
		),
	array(
		'mime_type' => 'text/x-tcl',
		'pattern' => array('tcl', 'tk')
		),
	array(
		'mime_type' => 'text/x-tex',
		'pattern' => array('tex', 'ltx', 'sty', 'cls')
		),
	array(
		'mime_type' => 'text/x-vcalendar',
		'pattern' => array('vcs')
		),
	array(
		'mime_type' => 'text/x-vcard',
		'pattern' => array('vcf')
		),
	/* Video */
	array(
		'mime_type' => 'video/3gpp',
		'pattern' => array('3gp')
		),
	array(
		'mime_type' => 'video/mp4',
		'pattern' => array('mp4')
		),
	array(
		'mime_type' => 'video/mpeg',
		'pattern' => array('mpeg', 'mpg', 'mpe')
		),
	array(
		'mime_type' => 'video/quicktime',
		'pattern' => array('qt', 'mov')
		),
	array(
		'mime_type' => 'video/x-ms-asf',
		'pattern' => array('asf', 'asx')
		),
	array(
		'mime_type' => 'video/x-ms-wm',
		'pattern' => array('wm')
		),
	array(
		'mime_type' => 'video/x-ms-wmv',
		'pattern' => array('wmv')
		),
	array(
		'mime_type' => 'video/x-ms-wmx',
		'pattern' => array('wmx')
		),
	array(
		'mime_type' => 'video/x-ms-wvx',
		'pattern' => array('wvx')
		),
	array(
		'mime_type' => 'video/x-msvideo',
		'pattern' => array('avi')
		),
	array(
		'mime_type' => 'video/x-sgi-movie',
		'pattern' => array('movie')
		),
	array(
		'mime_type' => 'video/x-flv',
		'pattern' => array('flv')
		),
);
?>