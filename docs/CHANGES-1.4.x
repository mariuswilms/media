
Changes & Migration Instructions for 1.4.x
==========================================

This page lists all changes necessary for migrating to a new version of the
plugin. Please note that instructions for migrations to non-stable versions are
still in-flux.

Configuration
-------------
The plugin configuration file is now named `media.php`.

Validation
----------
Blacklisting support has been removed from both checkExtension and
checkMimeType in the Transfer Behavior. This is an BC breaking change. You must
update to the new syntax by simply dropping any blacklists you provided.

Checking against PHP ini settings in checkSize has been removed. These cases
shouldn't be covered on the plugin level. Guranteeing a sane PHP environment
for your app firsthand is way better and more explicit. 

Shell
-----
The sync task will not assume that Transfer Behavior is attached to the
to-be-synced model. It'll now defauls the transfer path to MEDIA_TRANSFER.

