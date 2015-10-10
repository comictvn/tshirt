<?php
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

class PricingController extends BaseController
{
	public function __construct() {
		parent::__construct();
		$this->data['section'] = 'pricing';
	}

    public function index() {
		$pricing = Yaml::parse(file_get_contents('../data/pricing.yml'));

		foreach($pricing['colors'] as $k => $v) {
			$pricing['colors'][$k] = (float) number_format($pricing['colors'][$k], 2);
		}
        
		foreach($pricing['delivery_types'] as $k => $v) {
			$pricing['delivery_types'][$k]['price'] = (float) number_format($pricing['delivery_types'][$k]['price'], 2);
		}

		$this->data['pricing'] = $pricing;
		echo $this->view->render('pricing.tpl.php', $this->data);
	}
	
    public function save() {
        $json = file_get_contents('php://input');
		$post = json_decode($json, true);
        
		foreach($post['colors'] as $k => $v) {
			$post['colors'][$k] = number_format($post['colors'][$k], 2);
		}
        
		foreach($post['delivery_types'] as $k => $v) {
			$post['delivery_types'][$k]['price'] = number_format($post['delivery_types'][$k]['price'], 2);
		}

		$pricing_list = [];
		$pricing_list['colors'] = $post['colors'];
		$pricing_list['delivery_types'] = $post['delivery_types'];
		
		$dumper = new Dumper();
		$yaml = $dumper->dump($pricing_list, 2);
		file_put_contents('../data/pricing.yml', $yaml);

		echo json_encode(['status' => true]);
    }

}
