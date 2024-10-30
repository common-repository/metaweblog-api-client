<?php
/*
Plugin Name: MetaWeblog API Client
Plugin URI: http://ryanlee.org/software/wp/mwac/
Description: Re-posts published posts to anything that implements the MetaWeblog API (such as <a href="http://www.drupal.org/">Drupal</a>) with directions back to the original Wordpress blog for updates and comments.  Also edits and deletes according to WordPress' actions.  MWAC is based on <a href="http://ryanlee.org/software/wp/bac/">Blogger API Client</a> and the <a href="http://www.dentedreality.com.au/bloggerapi/class/">bloggerapi class</a> (made available by Beau Lebens).  This plugin was produced as part of <a href="http://dig.csail.mit.edu/" title="Decentralized Information Group">DIG</a>'s infrastructure, a member of MIT's <a href="http://www.csail.mit.edu/" title="Computer Science and Artificial Intelligence Laboratory">CSAIL</a>.  Licensed under BSD license in LICENSE
Version: 0.1
Author: Ryan Lee
Author URI: http://ryanlee.org/
*/

include_once("options/ixr.metaweblogclient.php");
include_once("options/mwac-db.php");

// Changes per-version
$mwac_version = "0.1";

function mwac_map_set_remote_id($postID, $remotePostID) {
	global $wpdb;
	$insert_dml = "INSERT INTO mwac_wp_post_map (post_ID, remote_post_ID) VALUES ($postID, '$remotePostID')";
	$rowcount = $wpdb->query($insert_dml);
	if ($rowcount == 1) {
		// successful insert
		return true;
	} else {
		// failure
		return false;
	}
}

function mwac_map_id_exists($postID) {
	global $wpdb;
	$query = "SELECT COUNT(*) FROM mwac_wp_post_map WHERE post_ID = $postID";
	$exists = $wpdb->get_var($query);
	if ($exists == 1) {
		return true;
	} else {
		return false;
	}
}

function mwac_map_get_remote_id($postID) {
	global $wpdb;
	$query = "SELECT remote_post_ID FROM mwac_wp_post_map WHERE post_ID = $postID";
	$remoteID = $wpdb->get_var($query);
	return $remoteID;
}

function mwac_map_delete_map($postID) {
	global $wpdb;
	$delete_dml = "DELETE FROM mwac_wp_post_map WHERE post_ID = $postID";
	$rowcount = $wpdb->query($delete_dml);
	if ($rowcount == 1) {
		// successful deletion
		return true;
	} else {
		// failure
		return false;
	}
}

function mwac_post_action($action, $ID) {
	global $post;
	$mwac_server = get_option('mwac_server');
	$mwac_path = get_option('mwac_path');
	$mwac_username = get_option('mwac_username');
	$mwac_password = get_option('mwac_password');
	$mwac_category = get_option('mwac_category');
	$mwac_blogid = get_option('mwac_blogid');
	$blog = new metaweblogclient($mwac_server, $mwac_path, $mwac_username, $mwac_password);

	if ($action == 'delete') {
		if (!mwac_map_id_exists($ID)) {
			return;
		}
		$mwac_ID = mwac_map_get_remote_id($ID);
		$blog->deletePost($mwac_ID, true);
		mwac_map_delete_map($ID);
		return;
	}

	query_posts('p=' . $ID);
	the_post();
	$category_match = false;
	if (count($mwac_category) == 0) {
		$category_match = true;
	}
	for ($i = 0; !$category_match && $i < count($mwac_category); $i++) {
		if (in_category($mwac_category[$i])) {
			$category_match = true;
		}
	}
	if (!$category_match) return;
	$title = the_title('', '', false);
	$head = "<p><em>This entry was <a href=\"" . get_permalink($ID) . "\">originally published</a>";
	$head .= " at <a href=\"" . get_settings('home') . "\">" . get_settings('blogname') . "</a></em></p>\n\n";
	$content = get_the_content();
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]>', $content);
	//!!!uncomment for urlparse support
	//$content = urlparse_external_links($content, $ID);
	$entry = $head . $content;
	if ($action == 'edit') {
		if (!mwac_map_id_exists($ID)) {
			return;
		}
		$mwac_ID = mwac_map_get_remote_id($ID);
		$blog->editPost($mwac_ID, $title, $entry, true);
	} elseif ($action == 'post') {
		$mwac_ID = $blog->newPost($mwac_blogid, $title, $entry, true);
		mwac_map_set_remote_id($ID, $mwac_ID);
	}
}

// new post
function mwac_post_entry($ID) {
	mwac_post_action('post', $ID);
	return $ID;
}

// edit a post already existing
function mwac_edit_post($ID) {
	mwac_post_action('edit', $ID);
	return $ID;
}

function mwac_delete_post($ID) {
	mwac_post_action('delete', $ID);
	return $ID;
}

// if edited and already published:
//   - check if still in right category, remote-delete if not
function mwac_edit_dispatch($ID) {
	global $wpdb;
	$mwac_category = get_option('mwac_category');
	$deleted = false;
	if ($wpdb->get_var("SELECT post_password FROM $wpdb->posts WHERE id = '$ID';") != "") {
		if (mwac_map_id_exists($ID)) {
			mwac_delete_post($ID);
			$deleted = true;
		}
	}
	if ($wpdb->get_var("SELECT post_status FROM $wpdb->posts WHERE id = '$ID';") != "publish" && !$deleted) {
		if (mwac_map_id_exists($ID)) {
			mwac_delete_post($ID);
			$deleted = true;
		}
	}
	if (!$deleted) {
		query_posts('p=' . $ID);
		the_post();
		$category_match = false;
		if (count($mwac_category) == 0) {
			$category_match = true;
		}
		for ($i = 0; !$category_match && $i < count($mwac_category); $i++) {
			if (in_category($mwac_category[$i])) {
				$category_match = true;
			}
		}
		if (!$category_match && mwac_map_id_exists($ID)) {
			mwac_delete_post($ID);
		}
	}

	return $ID;
}

function mwac_dispatch($ID) {
	if (mwac_post_in_future($ID))
		return;

	return mwac_certain_dispatch($ID);
}

function mwac_certain_dispatch($ID) {
	global $wpdb;

	if (mwac_check_configuration()) return;

        $pw_len = strlen($wpdb->get_var("SELECT post_password FROM $wpdb->posts WHERE id = '$ID';"));

	if ($pw_len == 0) {
		if (mwac_map_id_exists($ID)) {
			mwac_edit_post($ID);
		} else {
			mwac_post_entry($ID);
		}
	}

	return $ID;
}

function mwac_post_in_future($ID) {
	global $wpdb;

	if ($wpdb->get_var("SELECT post_status FROM $wpdb->posts WHERE id = '$ID'") == "future")
		return true;

	return false;
}

function mwac_check_configuration() {
    return (!get_option('mwac_username') || !get_option('mwac_password'));
}

function mwac_add_options_page() {
    add_options_page(__("MWAC"), __("MWAC"), 'manage_options', 'mwac/options/mwac-options.php');
}

function mwac_init() {
    add_action('admin_head', 'mwac_add_options_page');
    
// register for publish_post; this may delay seeing the posting result as it
// must access four pages in sequence before the post to Xanga is made
// (this seems to take care of edits too)
    add_action('publish_post', 'mwac_dispatch', 9);
    add_action('publish_future_post', 'mwac_certain_dispatch', 9);

// register for delete_post
    add_action('delete_post', 'mwac_delete_post');

// register for change in password protection
    add_action('edit_post', 'mwac_edit_dispatch');
}

add_action('init', 'mwac_init');

?>
