=== ASDB Taxonomy and Category Meta ===
Tags: taxonomy, meta, custom field, category meta, taxonomy meta, term meta, custom fields
License URI: http://en.wikipedia.org/wiki/MIT_License
Contributors: wpbuild.ru
Author: Mikhail "kitassa" Tkacheff
Tested up to: 4.5
License: MIT License
Requires at least: 4.4.0
Stable tag: 1.0.3

== Description ==
Plugin to manage and use custom meta fields within builtin and custom taxonomies. Simply add the desired fields by navigating to Settings >Taxonomy and Category Meta in your Wordpress administration panel.

== Screenshots ==
1. The Settings-page where you can add the custom fields
2. Example of the custom fields in custom taxonomy Regions


== Changelog ==

1.0.3 : First public relise.

1.0.0 : plugin initial creation.

== Installation ==
1. Unzip into your `/wp-content/plugins/` directory. If you're uploading it make sure to upload
the top-level folder. Don't just upload all the php files and put them in `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to your Administration interface in the "Settings" menu a new "Category Meta" page is created.
Configure the meta you want to use.
4. go to your Administration interface, in the "Category" menu -> new fields are displayed in the category modification form with the meta you configured.
5. That's it!
6. you can use the folowing functions into your templates to retreive 1 meta:
<?php $metaValue = get_term_meta($category_id, $meta_key, true); ?>
7. you can use the folowing functions into your templates to retreive all meta:
<?php $metaList = get_term_meta($category_id, ''); ?>
