<?php

/**
 * ixr.metaweblogclient.php
 * derived from Beau Lebens' ixr.bloggerclient.php
 * NOT a full implementation of the metaweblog client spec; does not
 * handle the newMediaObject method.
 */

require_once(realpath(dirname(__FILE__)) . '/' . '../../../../wp-includes/class-IXR.php');

class metaweblogstruct {
    var $title;
    var $description;

    function metaweblogstruct($title, $description) {
        $this->title = $title;
        $this->description = $description;
    }
}

class metaweblogclient {

    var $bServer;
    var $bPath;
    var $apiName = "metaWeblog";
    var $subApiName = "blogger";
    var $blogClient;
    var $XMLusername;
    var $XMLpassword;

    function metaweblogclient($server, $path, $username, $password)
    {
	$this->bServer = $server;
	$this->bPath = $path;

        // Connect to metaWeblog server
	if (!$this->connectToMetaWeblog()) {
	    return false;
	}

    	// Create variables to send in the message
    	$this->XMLusername = $username;
    	$this->XMLpassword = $password;
    	return $this;
    }

    // blogger API stuff also in metaWeblog
    function getUsersBlogs()
    {
    	// Construct query for the server
        $r = $this->blogClient->query($this->subApiName . ".getUsersBlogs", $this->XMLusername, $this->XMLpassword);
    	return $this->blogClient->getResponse();
    }

    function getUserInfo()
    {
        $r = $this->blogClient->query($this->subApiName . ".getUserInfo", $this->XMLusername, $this->XMLpassword);
        return $this->blogClient->getResponse();
    }
        
    function getTemplate($blogID, $template="main")
    {
        $XMLblogid = $blogID;
        $XMLtemplate = $template;
        $r = $this->blogClient->query($this->subApiName . ".getTemplate", $XMLblogid, $this->XMLusername, $this->XMLpassword, $XMLtemplate);
        return $this->blogClient->getResponse();
    }
        
    function setTemplate($blogID, $template="archiveIndex")
    {
        $XMLblogid = $blogID;
        $XMLtemplate = $template;
        $r = $this->blogClient->query($this->subApiName . ".setTemplate", $XMLblogid, $this->XMLusername, $this->XMLpassword, $XMLtemplate);
        return $this->blogClient->getResponse();
    }

    function deletePost($postID, $publish=false)
    {
        $XMLpostid = $postID;
        $XMLpublish = $publish;
        $r = $this->blogClient->query($this->subApiName . ".deletePost", "", $XMLpostid, $this->XMLusername, $this->XMLpassword, $XMLpublish);
        return $this->blogClient->getResponse();
    }
    
    // metaWeblog specific

    // returns MW struct
    function getPost($postID)
    {
        $XMLpostid = $postID;
        $r = $this->blogClient->query($this->apiName . ".getPost", $XMLpostid, $this->XMLusername, $this->XMLpassword);
        return $this->blogClient->getResponse();
    }

    // returns post ID
    function newPost($blogID, $title, $content, $publish=false)
    {
        $XMLblogid = $blogID;
	$contentStruct = new metaweblogstruct($title, $content);
        $XMLpublish = $publish;
        $r = $this->blogClient->query($this->apiName . ".newPost", $XMLblogid, $this->XMLusername, $this->XMLpassword, $contentStruct, $XMLpublish);
        return $this->blogClient->getResponse();
    }
    
    // returns true  
    function editPost($postID, $title, $content, $publish=false)
    {
        $XMLpostid = $postID;
	$contentStruct = new metaweblogstruct($title, $content);
        $XMLpublish = $publish;
        $r = $this->blogClient->query($this->apiName . ".editPost", $XMLpostid, $this->XMLusername, $this->XMLpassword, $contentStruct, $XMLpublish);
        return $this->blogClient->getResponse();
    }
    
    // uploads an object, but is not implemented in this client
    function newMediaObject($title, $content) {
        return false;
    }

    // returns struct
    function getCategories($blogID) {
	$XMLblogid = $blogID;
        $r = $this->blogClient->query($this->apiName . ".getRecentPosts", $XMLblogid, $this->XMLusername, $this->XMLpassword);
        return $this->blogClient->getResponse();
    }

    // returns array of structs
    function getRecentPosts($blogID, $postCount) {
	$XMLblogid = $blogID;
        $r = $this->blogClient->query($this->apiName . ".getRecentPosts", $XMLblogid, $this->XMLusername, $this->XMLpassword, $postCount);
        return $this->blogClient->getResponse();
    }

    // class helper functions
    // Returns a connection object to the blogger server
    function connectToMetaWeblog() {
    	if($this->blogClient = new IXR_Client($this->bServer, $this->bPath)) {
    		return true;
    	} else {
    		return false;
    	}
    }
}
?>
