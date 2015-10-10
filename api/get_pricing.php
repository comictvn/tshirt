<?php
/**
 *  
 *
 * Get the pricing set
 *  
 */	
require __DIR__ . '/autoload.php';
use Symfony\Component\Yaml\Yaml;
use \Michelf\Markdown;
$result = [];
$result['status'] = true;

$data_dir = "../data/";

$pricing = Yaml::parse($data_dir . "pricing.yml");
$result['pricing'] = $pricing['colors'];
$result['delivery_types'] = $pricing['delivery_types'];

header('Content-Type: application/json');
echo json_encode($result);

