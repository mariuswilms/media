
Changes & Migration Instructions for 0.6.x
==========================================

This page lists all changes necessary for migrating to a new version of the
plugin. Please note that instructions for migrations to non-stable versions are
still in-flux.

Behavior of MimeType::simplify()
--------------------------------
The second and third parameters now do exactly the opposite. This only affects
you if you specified them before.
 
  before: MimeType::simplify('application/x-example', true, true);
  after:  MimeType::simplify('application/x-example', false, false);
  
  before: MimeType::simplify('application/x-example');
  after:  MimeType::simplify('application/x-example');

Options taken by MimeType::guessType()
--------------------------------------
Each option now does exactly the opposite.

looseProperties => properties
looseExperimental => experimental

Location and naming of magic/glob databases
-------------------------------------------
The convention for the naming of the databases has been changed:

  before: magic.{php,db}
  after:  mime_magic.{php,db}

  before: glob.{php,db}
  after:  mime_glob.{php,db}

Files are now searched in this order and in these locations:
  1. app/configs/mime_magic.php
  2. all app vendors/mime_magic.db
  3. plugin vendors/mime_magic.db

Shell
-----
The shell has been renamed to _media_. Before you've invoked it with @cake
media.manage@, now you'll do that with @cake media@

Most existing tasks have been rewritten. Options and arguments have also been
changed. Please check the shell's help with @cake media help@ to review those
changes.

Media Behavior
--------------
The _base_ option of the Media Behavior has been renamed to _baseDirectory_

Media Behavior now interacts with the Transfer Behavior (in case it's also
attached to the model). Media Behavior will overwrite its settings for
_createDirectory_ and use the  @dirname()@ of _baseDirectory_ for its
_baseDirectory_ setting.

Transfer Behavior
-----------------
The _destinationFile_ option of the Transfer Behavior has been split into
_baseDirectory_ and _destinationFile_ options. _destinationFile_ is now a
relative path.

The _checkLocation_ rule doesn't accept markers as parameters any more. Use the
predefined constants.

Medium Helper
------------
Any settings provided are used as a _map_. For the syntax have a look into the
default values provided for the _map_ property of the helper.

The _file_ method now also takes an array (with _dirname_ and _basename_ keys)
as a path.



