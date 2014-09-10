<?php 
namespace Concrete\Controller\SinglePage\Dashboard\System\Seo;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Config;
use Core;

class Codes extends DashboardPageController {

	public function view() {
		$this->set('tracking_code', Config::get('SITE_TRACKING_CODE'));
		$tracking_code_position = Config::get('SITE_TRACKING_CODE_POSITION');
		if (!$tracking_code_position) {
			$tracking_code_position = 'bottom';
		}
		$this->set('tracking_code_position', $tracking_code_position);

		if ($this->isPost()) {
			if ($this->token->validate('update_tracking_code')) {
					Config::save('SITE_TRACKING_CODE', $this->post('tracking_code'));
					Config::save('SITE_TRACKING_CODE_POSITION', $this->post('tracking_code_position'));
                    Core::make('cache')->flush();
					$this->redirect('/dashboard/system/seo/codes', 'saved');
			} else {
				$this->error->add($this->token->getErrorMessage());
			}
		}
	}
	
	public function saved() {
		$this->set('message', implode(PHP_EOL, array(
			t('Tracking code settings updated successfully.'),
			t('Cached files removed.')
		)));
		$this->view();
	}
}