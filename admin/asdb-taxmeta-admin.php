<?php

Class taxmeta_admin {

    function taxmeta_admin() {

        // Load language file
        $locale = get_locale();
        if ( !empty($locale) )
        //load_textdomain('asdb-taxmeta', WPTM_ABSPATH.'lang/wp-category-meta-'.$locale.'.mo');

        add_action('admin_head', array(&$this, 'taxmeta_options_style'));
        add_action('admin_menu', array(&$this, 'taxmeta_add_options_panel'));

    }

    //styling options page
    function taxmeta_options_style() {
        ?>
        <style type="text/css" media="screen">
            .title_desc {width:300px;}
            .info { background: #FFFFCC; border: 1px dotted #D8D2A9; padding: 10px; color: #333; }
            .info a { color: #333; text-decoration: none; border-bottom: 1px dotted #333 }
            .info a:hover { color: #666; border-bottom: 1px dotted #666; }
        </style>
    <?php
    }

    //Add configuration page into admin interface.
    function taxmeta_add_options_panel() {
        add_options_page('Taxonomy and Category Meta Options', 'Taxonomy and Category Meta', 'manage_options', 'taxmeta', array(&$this, 'taxmeta_option_page'));
    }

    //build admin interface
    function taxmeta_option_page()
    {
        global $wp_version;
        $configuration = get_option("taxmeta_config");
        if(is_null($configuration) || $configuration == '')
        {
            $configuration = array();
        }

        if(isset($_POST['action']) && $_POST['action'] == "add")
        {
            $new_meta_label = $_POST["new_meta_label"];
            $new_meta_name = $_POST["new_meta_name"];
            $new_meta_name = sanitize_title(str_replace(' ','_',$new_meta_name));
            $new_meta_type = $_POST["new_meta_type"];
            $new_meta_taxonomy = $_POST["new_meta_taxonomy"];
            $configuration[$new_meta_name] = array('label' => $new_meta_label, 'type' => $new_meta_type, 'taxonomy' => $new_meta_taxonomy);

            update_option("taxmeta_config", $configuration);

        }
        else if(isset($_POST['action']) && $_POST['action'] == "delete")
        {
            $delete_taxmeta_name = $_POST["delete_taxmeta_name"];
            unset($configuration[$delete_taxmeta_name]);
            update_option("taxmeta_config", $configuration);
        }
    ?>
        <div class="wrap">
            <h2><?php _e('ASDB Taxonomy and Category Meta', 'asdb-taxmeta'); ?></h2>
            <table class="widefat fixed">
                <thead>
                    <tr class="title">
                        <th scope="col" class="manage-column"><?php _e('Meta list', 'asdb-taxmeta'); ?></th>
                        <th scope="col" class="manage-column"></th>
                        <th scope="col" class="manage-column"></th>
                        <th scope="col" class="manage-column"></th>
                        <th scope="col" class="manage-column"></th>
                    </tr>
                    <tr class="title">
                        <th scope="col" class="manage-column"><?php _e('Meta Label', 'asdb-taxmeta'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Meta Name', 'asdb-taxmeta'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Meta Type', 'asdb-taxmeta'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Meta Taxonomy', 'asdb-taxmeta'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Action', 'asdb-taxmeta'); ?></th>
                    </tr>
                </thead>
                <?php
                    foreach($configuration as $name => $data)
                    {
                        $type = '';
                        $taxonomy = 'category';
                        if(is_array($data)) {
                            $label = $data['label'];
                            $type = $data['type'];
                            $taxonomy = $data['taxonomy'];
                        } else {
                            $type = $data;
                        }
                        ?>
                <tr class="mainrow">
                    <td class="title_desc"><?php echo $label;?></td>
                    <td class="title_desc"><?php echo 'taxmeta_'.$name;?></td>
                    <td class="forminp"><?php echo $type;?></td>
                    <td class="forminp"><?php echo $taxonomy;?></td>
                    <td class="forminp">
                        <form method="post">
                        <input type="hidden" name="action" value="delete" />
                        <input type="hidden" name="delete_taxmeta_name" value="<?php echo $name;?>" />
                        <input type="submit" class="button-secondary" value="<?php _e('Delete Meta', 'asdb-taxmeta') ?>" />
                        </form>
                    </td>
                </tr>
                    <?php }
                ?>
            </table>
            <br/>
            <form method="post">
                <table class="widefat">
                    <thead>
                        <tr class="title">
                            <th scope="col" class="manage-column"><?php _e('Add Meta', 'asdb-taxmeta'); ?></th>
                            <th scope="col" class="manage-column"></th>
                        </tr>
                    </thead>
                    <tr class="mainrow">
                        <td class="title_desc"><?php _e('Meta Label','asdb-taxmeta'); ?>:</td>
                        <td class="forminp">
                            <input type="text" id="new_meta_label" name="new_meta_label" value="" />
                        </td>
                    </tr>

                    <tr class="mainrow">
                        <td class="title_desc"><?php _e('Meta Name','asdb-taxmeta'); ?>:</td>
                        <td class="forminp">
                            <input type="text" id="new_meta_name" name="new_meta_name" value="" />
                        </td>
                    </tr>
                    <tr class="mainrow">
                        <td class="title_desc"><?php _e('Meta Type','asdb-taxmeta'); ?>:</td>
                        <td class="forminp">
                            <select id="new_meta_type" name="new_meta_type">
                                <option value="text"><?php _e('Text','asdb-taxmeta'); ?></option>
                                <option value="textarea"><?php _e('Text Area','asdb-taxmeta'); ?></option>
                                <option value="editor"><?php _e('WYSIWYG Editor','asdb-taxmeta'); ?></option>
                                <option value="image"><?php _e('Image Upload','asdb-taxmeta'); ?></option>
                                <option value="checkbox"><?php _e('Check Box','asdb-taxmeta'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="mainrow">
                        <td class="title_desc"><?php _e('Add to Taxonomy','asdb-taxmeta'); ?>:</td>
                        <td class="forminp">
                            <select id="new_meta_taxonomy" name="new_meta_taxonomy">
                                <?php
                                    $taxonomies=get_taxonomies('','names');
                                    foreach ($taxonomies as $taxonomy ) {
                                      echo '<option value="'.$taxonomy.'">'. $taxonomy. '</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="mainrow">
                        <td class="title_desc">
                        <input type="hidden" name="action" value="add" />
                        </td>
                        <td class="forminp">
                        <input type="submit" class="button-primary" value="<?php _e('Add Meta', 'asdb-taxmeta') ?>" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    <?php
    }
}
?>