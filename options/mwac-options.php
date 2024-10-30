<?php
/*
 * mwac-options.php
 * Description: WordPress admin options for the MWAC plugin
 * Plugin URI: http://ryanlee.org/software/wp/mwac/
 * Author: Ryan Lee
 * Author URI: http://ryanlee.org/
 */

function mwac_upgrade() {
    global $mwac_version;
    
    mwac_install_table();
    
    if (!get_option('mwac_version'))
        add_option('mwac_version', $mwac_version, "MWAC version number");

    if (!get_option('mwac_username'))
        add_option('mwac_username', '', "Your remote username");
    
    if (!get_option('mwac_password'))
        add_option('mwac_password', '', "Your remote password");
        
    if (!get_option('mwac_server'))
        add_option('mwac_server', '', "The remote server");
    
    if (!get_option('mwac_path'))
        add_option('mwac_path', '', "The path to the API service");

    if (!get_option('mwac_blogid'))
        add_option('mwac_blogid', '', "The service-specific blog identifier");
    
    if (!get_option('mwac_category'))
        add_option('mwac_category', array(), "Specific categories to post from; leave empty to post from all categories");
}

if ('process' == $_POST['stage']) {
     mwac_update_options();
} else {
     mwac_display_admin_page();
}

if (!get_option('mwac_version') || get_option('mwac_version') != $mwac_version)
    mwac_upgrade();

function mwac_update_options() {
    if (isset($_POST['mwac_username']))
        update_option('mwac_username', $_POST['mwac_username']);
     else
        update_option('mwac_username', '');

    if (isset($_POST['mwac_password']))
        update_option('mwac_password', $_POST['mwac_password']);
     else
        update_option('mwac_password', '');

    if (isset($_POST['mwac_server']))
        update_option('mwac_server', $_POST['mwac_server']);
     else
        update_option('mwac_server', '');

    if (isset($_POST['mwac_path']))
        update_option('mwac_path', $_POST['mwac_path']);
     else
        update_option('mwac_path', '');

    if (isset($_POST['mwac_blogid']))
        update_option('mwac_blogid', $_POST['mwac_blogid']);
     else
        update_option('mwac_path', '1');

    if (isset($_POST['mwac_category']))
        update_option('mwac_category', $_POST['mwac_category']);
     else
        update_option('mwac_category', array());

     mwac_display_admin_page();
}

function mwac_display_admin_page() {
    global $wpdb;
    
    $location = get_option('siteurl') . '/wp-admin/admin.php?page=mwac/options/mwac-options.php';
    
    $mwac_username = get_option('mwac_username');
    $mwac_password = get_option('mwac_password');
    $mwac_server = get_option('mwac_server');
    $mwac_path = get_option('mwac_path');
    $mwac_blogid = get_option('mwac_blogid');
    $mwac_category = get_option('mwac_category');

    $r = array('orderby' => 'name', 'hide_empty' => 0, 'hierarchical' => 0);
    $categories = get_categories($r);
    if (!empty($categories)) {
        $category_output = "<select name='mwac_category[]' multiple='multiple' size='5'>\n";
        foreach ($categories as $category) {
            if (in_array($category->cat_ID, $mwac_category))
	        $category_output .= "<option value='$category->cat_ID' selected='selected'>$category->cat_name</option>";
	    else
	        $category_output .= "<option value='$category->cat_ID'>$category->cat_name</option>";
        }
        $category_output .= "</select>\n";
    }
?>

    <div class="wrap">
     <h2>MWAC Options</h2>
     <form name="mwac-options" method="post" action="<?php echo $location ?>&amp;updated=true">
        <input type="hidden" name="stage" value="process" />
        <fieldset class="options">
         <p class="submit"><input type="submit" name="Submit" value="<?php echo "Update Options"; ?> &raquo;" /></p>
         <table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform">
          <tbody>
           <tr>
            <th scope="row"><label for="mwac_username">Remote Username</label></th>
            <td><input type="text" name="mwac_username" value="<?php echo $mwac_username; ?>"/></td>
           </tr>
           <tr>
            <th scope="row"><label for="mwac_password">Remote Password</label></th>
            <td><input type="password" name="mwac_password" value="<?php echo $mwac_password; ?>"/></td>
           </tr>
           <tr>
            <th scope="row"><label for="mwac_server">Remote Server</label></th>
            <td><input type="text" name="mwac_server" value="<?php echo $mwac_server; ?>"/></td>
           </tr>
           <tr>
            <th scope="row"><label for="mwac_path">API Path</label></th>
            <td><input type="text" name="mwac_path" value="<?php echo $mwac_path; ?>"/></td>
           </tr>
           <tr>
            <th scope="row" style="vertical-align: top;"><label for="mwac_blogid">Blog ID</label><p><small>API Service specific; e.g., set to 1 for Drupal</small></p></th>
            <td style="vertical-align: top;"><input type="text" name="mwac_blogid" value="<?php echo $mwac_blogid; ?>"/></td>
           </tr>
           <tr>
            <th scope="row" style="vertical-align: top;"><label for="mwac_category">Categories</label><p><small>Post only if in category; leave unselected for all categories</small></p></th>
            <td style="vertical-align: top;"><?php echo $category_output; ?></td>
           </tr>
          </tbody>
         </table>

         <p class="submit"><input type="submit" name="Submit" value="<?php echo "Update Options"; ?> &raquo;" /></p>
        </fieldset>
     </form>
    </div>
<?php
}
?>
