<?php
/*
 * Plugin Name: ASDB Taxonomy and Category Meta
 * Description: Add custom Meta Data (text, textarea, checkbox, Image Upload, WISIWYG Editor) to the Wordpress Taxonomies and Categories
 * Version: 1.0.3
 * Author: Mikhail "kitassa" Tkacheff
 * Author URI: http://tkacheff.ru
 *
 * This plugin has been developped and tested with Wordpress Version 4.4
 *
 * Copyright 2016  Mikhail "kitassa" Tkacheff (@tkacheff)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 */

// Define contants
define( 'ASDB_TAXMETA_VERSION' , '1.0.3' );
define( 'ASDB_TAXMETA_ROOT' , dirname(__FILE__) );
define( 'ASDB_TAXMETA_FILE_PATH' , ASDB_TAXMETA_ROOT . '/' . basename(__FILE__) );
define( 'ASDB_TAXMETA_URL' , plugins_url( '/', __FILE__ ) );

// Initialization and Hooks
global $wp_version;
global $taxmeta_version;
$taxmeta_version = ASDB_TAXMETA_VERSION;
$min_wp_version = '4.4';

if ( version_compare($wp_version, $min_wp_version, "<") ) {
   $pluginError = sprintf(__('This plugins work only WordPress version %s or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>'), $min_wp_version);
   exit ($pluginError);
}

// Actions & Filters
add_action('admin_init', 'taxmeta_init');
add_filter('admin_enqueue_scripts', 'taxmeta_admin_enqueue_scripts');

if (is_admin()) {
    include ASDB_TAXMETA_ROOT . '/admin/asdb-taxmeta-admin.php';
    $ASDBAdmin = new taxmeta_admin();
}

/**
 * Function that initialise the plugin.
 * It loads the translation files.
 *
 * @return void.
 */

function taxmeta_init()
{
    global $wp_version;

	// Load language file
    if (function_exists('load_plugin_textdomain')) {
        load_plugin_textdomain('asdb-taxmeta', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    } else {
        $locale = get_locale();
        if (!empty($locale)) {
            load_textdomain('asdb-taxmeta', ASDB_TAXMETA_ROOT . 'languages' . '/asdb-taxmeta-' . $locale . '.mo');
        }
    }

add_action('created_term',	'taxmeta_save_meta_tags');
add_action('edit_term',		'taxmeta_save_meta_tags');
add_action('delete_term',	'taxmeta_delete_meta_tags');

	$taxmeta_taxonomies = get_taxonomies('', 'names');
	if (is_array($taxmeta_taxonomies)) {
    	foreach ($taxmeta_taxonomies as $taxmeta_taxonomy)
    	{
        	add_action($taxmeta_taxonomy . '_add_form_fields', 'taxmeta_add_meta_textinput');
        	add_action($taxmeta_taxonomy . '_edit_form', 'taxmeta_add_meta_textinput');
    	}
	}
}

/**
 * Add the loading of needed javascripts and CSS for Admin panel.
 *
 */
function taxmeta_admin_enqueue_scripts()
{
    if (is_admin() && isset($_REQUEST["taxonomy"])) {
        wp_register_style('thickbox-css', '/wp-includes/js/thickbox/thickbox.css');
        wp_enqueue_style('thickbox-css');

        wp_enqueue_script('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('quicktags');
        wp_enqueue_media();
    }
}



/**
 * Function that saves the meta from form.
 *
 * @param $id : terms (category) ID
 * @return void;
 */
function taxmeta_save_meta_tags($id)
{
    $single = true;
    $key = '';

    //$taxmetaList = get_term_meta( $id, $key, $single );
    $taxmetaList = get_option("taxmeta_config");
    $taxmeta_edit = $_POST["taxmeta_edit"];

    if (isset($taxmeta_edit) && !empty($taxmeta_edit)) {
        foreach ($taxmetaList as $inputName => $inputType) {
            if ($inputType['taxonomy'] == $_POST['taxonomy']) {
                $inputValue = $_POST['taxmeta_' . str_replace(' ', '_', $inputName)];
                delete_term_meta($id, $inputName);
                if (isset($inputValue) && !empty($inputValue)) {
                    add_term_meta($id, $inputName, $inputValue);
                }
            }
        }
    }
}


/**
 * Function that display the meta text input.
 *
 * @param Object $tag
 * @return void.
 */
function taxmeta_add_meta_textinput($tag)
{
    global $category, $wp_version, $taxonomy;
    $single = true;
    $key = '';

    $category_id = $category;

    if ($wp_version >= '3.0') {
        $category_id = (is_object($tag)) ? $tag->term_id : null;
    }

    if (is_object($category_id)) {
        $category_id = $category_id->term_id;
    }

    //$taxmetaList = get_term_meta( $category_id, $key, $single );
    $taxmetaList = get_option("taxmeta_config");

    if (!is_null($taxmetaList) && count($taxmetaList) > 0 && $taxmetaList != '' && isset($_GET['tag_ID'])) { ?>
		<hr>
        <h3 class='hndle'>
            <span><?php _e('Term Meta', 'asdb-taxmeta'); ?></span></h3>

        <div class="inside">

            <input value="taxmeta_edit" type="hidden" name="taxmeta_edit"/>
            <input type="hidden" name="image_field" id="image_field" value=""/>
            <table class="form-table">
                <?php
                foreach ($taxmetaList as $inputName => $inputData) {
                    $inputTaxonomy = 'category';
                    $inputType = $inputData;
                    if (is_array($inputData)) {
                        $inputLabel = $inputData['label'];
                        $inputType = $inputData['type'];
                        $inputTaxonomy = $inputData['taxonomy'];
                    }

                    // display the input field
                    if ($inputTaxonomy == $taxonomy) {
                        $inputValue = htmlspecialchars(stripcslashes(get_term_meta($category_id, $inputName, true)));
                        ?>

                        <?php if ($inputType == 'text') { ?>
                            <tr class="form-field">
                                <th scope="row" valign="top">
                                    <label
                                        for="category_nicename"><?php echo $inputLabel; ?></label>
                                </th>
                                <td>
                                    <input value="<?php echo $inputValue ?>" type="text" size="40" name="<?php echo 'taxmeta_' . $inputName; ?>"/><br/>
                                    <div class="description"><?php _e('This custom data is attached to the current Term', 'asdb-taxmeta'); ?></div>
                                </td>
                            </tr>

                        <?php } elseif ($inputType == 'textarea') { ?>

                            <tr class="form-field">
                                <th scope="row" valign="top">
                                    <label
                                        for="category_nicename"><?php echo $inputLabel; ?></label>
                                </th>
                                <td>
                                    <textarea name="<?php echo "taxmeta_" . $inputName ?>" rows="5" cols="50" class="large-text"><?php echo $inputValue ?></textarea><br/>
                                    <div class="description"><?php _e('This custom data is attached to the current Term', 'asdb-taxmeta'); ?></div>
                                </td>
                            </tr>

                        <?php } elseif ($inputType == 'editor') { ?>

                            <? $inputValue = get_term_meta($category_id, $inputName, true); ?>

                            <tr>
                                <th scope="row" valign="top">
                                    <label
                                        for="category_nicename"><?php echo $inputLabel; ?></label>
                                </th>
                                <td>
                                    <?php wp_editor($inputValue, "taxmeta_" . str_replace(' ', '_', $inputName), array('textarea_name' => "taxmeta_" . str_replace(' ', '_', $inputName))); ?>
                                    <div class="description"><?php _e('This custom data is attached to the current Term', 'asdb-taxmeta'); ?></div>
                                </td>
                            </tr>

                        <?php } elseif ($inputType == 'image') { ?>

                            <?php $current_image_url = get_term_meta($category_id, $inputName, true); ?>

							<script type="text/javascript">
								jQuery(document).ready(function($){
									$('#uploadImage_<?php echo str_replace(' ','_',$inputName); ?>').click(function(e) {
									e.preventDefault();
										var image = wp.media({
		    							title: 'Upload Image',
		    								multiple: false
										}).open()
										.on('select', function(e){
										var uploaded_image = image.state().get('selection').first();
											console.log(uploaded_image);
										var image_url = uploaded_image.toJSON().url;
										$('#<?php echo "taxmeta_".str_replace(' ','_',$inputName);?>').val(image_url);
										});
									});
									$('#deleteImage_<?php echo str_replace(' ','_',$inputName); ?>').click(function() {
										$('#<?php echo "taxmeta_".str_replace(' ','_',$inputName);?>').val('');
									});
								});
							</script>

                            <tr class="form-field">
                                <th scope="row" valign="top">
                                    <label
                                        for="<?php echo "taxmeta_" . str_replace(' ', '_', $inputName); ?>"
                                        class="taxmeta_meta_name_label"><?php echo $inputLabel; ?></label>
                                </th>

								<td>
									<div id="<?php echo "taxmeta_".str_replace(' ','_',$inputName);?>_selected_image" class="asdb_selected_image">
										<?php if ($current_image_url != '') echo '<img src="'.$current_image_url.'" style="max-width:100%;"/>';?>
									</div>
									<input type="regular-text" name="<?php echo "taxmeta_".str_replace(' ','_',$inputName);?>" id="<?php echo "taxmeta_".str_replace(' ','_',$inputName);?>" value="<?php echo $current_image_url;?>" />
									<input type='button' class="button-primary" value="<?php _e('Upload Image', 'asdb-taxmeta'); ?>" id="uploadImage_<?php echo str_replace(' ','_',$inputName); ?>"/>
									<input type='button' class="button-secondary" value="<?php _e('Delete Image', 'asdb-taxmeta'); ?>" id="deleteImage_<?php echo str_replace(' ','_',$inputName); ?>"/><br />
									<div class="description"><?php _e('This custom data is attached to the current Term', 'asdb-taxmeta'); ?></div>
								</td>

                            </tr>

                        <?php } elseif ($inputType == 'checkbox') { ?>

                            <tr class="form-field">
                                <th scope="row" valign="top">
                                    <label
                                        for="category_nicename"><?php echo $inputLabel; ?></label>
                                </th>
                                <td>
                                    <input value="checked" type="checkbox" <?php echo $inputValue ? 'checked="checked" ' : ''; ?> name="<?php echo 'taxmeta_' . $inputName; ?>"/><br/>
                                    <div class="description"><?php _e('This custom data is attached to the current Term', 'asdb-taxmeta'); ?></div>
                                </td>
                            </tr>
                        <?php } //end ELSEIF input type ?>
                <?php  }//end FOREACH
                }//end IF ?>
            </table>

        <?php $configuration = get_option("taxmeta_config"); ?>
        </div>
		<div class="clear clearfix"></div>
		<hr>
	<?php }// end IF $taxmetaList
}