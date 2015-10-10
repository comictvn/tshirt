<?php
/**
 *  This load the products, the categories and the settings	
 */
 
require __DIR__ . '/autoload.php';
use Symfony\Component\Yaml\Yaml;
use \Michelf\Markdown;
$result = [];
$result['status'] = true;

$data_dir = "../data/";

//get files in folder
$files = glob($data_dir . "products/*.yml");
$products = [];
$category_names = [];
foreach($files as $file) {
	$product = Yaml::parse($file);
	$product['description'] = Markdown::defaultTransform($product['description']);
	$products[] = $product;
	$category_names[] = $product['category'];
}
usort($products, function($a, $b) {
	if(!isset($b['position']))
		$b['position'] = 1;
	if(!isset($a['position']))
		$a['position'] = 1;
    return $a['position'] - $b['position'];
});
$result['products'] = $products;

$categories = Yaml::parse($data_dir . "categories.yml");
$category_list = [];
foreach($categories as $category) {
	if(in_array($category['name'], $category_names)) {
		$category_list[] = $category;
	}
}
$result['categories'] = $category_list;

//load the settings
$settings = include $data_dir . "settings.php";
unset($settings['stripe_secret_key']);
unset($settings['password']);
$result['settings'] = $settings;

header('Content-Type: application/json');
echo json_encode($result);

