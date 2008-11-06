--
-- Schema File
-- 
-- Copyright (c) $CopyrightYear$ David Persson
--
-- Permission is hereby granted, free of charge, to any person obtaining a copy
-- of this software and associated documentation files (the "Software"), to deal
-- in the Software without restriction, including without limitation the rights
-- to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
-- copies of the Software, and to permit persons to whom the Software is
-- furnished to do so, subject to the following conditions:
-- 
-- The above copyright notice and this permission notice shall be included in
-- all copies or substantial portions of the Software.
-- 
-- THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
-- IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
-- FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
-- AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
-- LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
-- OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
-- THE SOFTWARE
-- 
-- PHP version $PHPVersion$
-- CakePHP version $CakePHPVersion$
-- 
-- @category   database
-- @package    attm
-- @subpackage attm.plugins.attachments.config.sql
-- @author     David Persson <davidpersson@qeweurope.org>
-- @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
-- @license    http://www.opensource.org/licenses/mit-license.php The MIT License
-- @version    SVN: $Id$
-- @version    Release: $Version$
-- @link       http://cakeforge.org/projects/attm The attm Project
-- @since      attachments plugin 0.50
-- 
-- @modifiedby   $LastChangedBy$
-- @lastmodified $Date$
--

CREATE TABLE IF NOT EXISTS `attachments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `model` varchar(255) NOT NULL,
  `foreign_key` int(10) NOT NULL,
  `dirname` varchar(255) default NULL,
  `basename` varchar(255) NOT NULL,
  `checksum` varchar(255) NOT NULL,
  `alternative` varchar(50) default NULL,
  `group` varchar(255) default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
);

