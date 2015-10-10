<?php

/**
 *  
 *  Some functions we need  
 *  
 */

function site_url($path = null) {
    return ADMIN_URL.'/'.$path;
}

function api_url($path = null) {
    return API_URL.'/'.$path;
}

function current_url() {
    $current_url = str_replace(ADMIN_URL, "", $_SERVER['REQUEST_URI']);
    return $current_url;
}

function get_segment($position) {
	$current_url = explode('/', current_url());
    return (isset($current_url[$position])?$current_url[$position]:false);
}

function redirect_to($path = null, $data = null) {
	if(current_url() == '/'.$path) {
		return false;
	}
    if($data) {
        if(isset($data['success']))
            $_SESSION['success'] = $data['success'];
        if(isset($data['error']))
            $_SESSION['error'] = $data['error'];        
        if(isset($data['vars']))
            $_SESSION['vars'] = $data['vars'];
    }
    header('Location: '. site_url($path));
    exit();
}

function slugify($text) { 
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text); // transliterate
    $text = strtolower($text);// lowercase
    $text = preg_replace('~[^-\w]+~', '', $text);// remove unwanted characters

    if (empty($text))
        return 'n-a';
    return $text;
}
