<?

namespace Concrete\Controller\SinglePage\Dashboard\Extend;
use \Concrete\Core\Page\Controller\MarketplaceDashboardPageController;
use TaskPermission;
use Marketplace;
use Loader;
use \Concrete\Core\Marketplace\RemoteItemList as MarketplaceRemoteItemList;

class Themes extends MarketplaceDashboardPageController {

    public function getMarketplaceType()
    {
        return 'themes';
    }

	public function view() {

		$tp = new TaskPermission();
		$mi = Marketplace::getInstance();
		if ($mi->isConnected() && $tp->canInstallPackages()) { 

            /*
			$mri = new MarketplaceRemoteItemList();
			$mri->setItemsPerPage(9);
			$sets = MarketplaceRemoteItemList::getItemSets('themes');

			$setsel = array('' => t('All Items'), 'FEATURED' => t('Featured Items'));
			if (is_array($sets)) {
				foreach($sets as $s) {
					$setsel[$s->getMarketplaceRemoteSetID()] = $s->getMarketplaceRemoteSetName();
				}
			}
			
			$sortBy = array(
				'' => t('Recommended'),
				'popular' => t('Popular'),
				'recent' => t('Recently Added'),
				'rating' => t('Highest Rated'),
				'price_low' => t('Price: Low to High'),
				'price_high' => t('Price: High to Low')
			);
			
			
			$mri->setIncludeInstalledItems(false);
			if (isset($_REQUEST['marketplaceRemoteItemSetID'])) {
				$set = $_REQUEST['marketplaceRemoteItemSetID'];
			}
			
			if (isset($_REQUEST['mpID'])) {
				$mri->filterByMarketplaceItemID($_REQUEST['mpID']);
			}
			
			if (isset($_REQUEST['marketplaceRemoteItemSortBy'])) {
				$this->set('selectedSort', Loader::helper('text')->entities($_REQUEST['marketplaceRemoteItemSortBy']));
				$mri->sortBy($_REQUEST['marketplaceRemoteItemSortBy']);
			} else {
				$mri->sortBy('recommended');
			}
			
			if (isset($_REQUEST['marketplaceIncludeOnlyCompatibleAddons']) && $_REQUEST['marketplaceIncludeOnlyCompatibleAddons'] == 1) {
				$mri->filterByCompatibility(1);
			}
			
			if (isset($_REQUEST['marketplaceRemoteItemKeywords']) && $_REQUEST['marketplaceRemoteItemKeywords']) {
				$keywords = $_REQUEST['marketplaceRemoteItemKeywords'];
				$sortBy = array('relevance' => t('Relevance')) + $sortBy;
			}
			
			if ($keywords != '') {
				$mri->filterByKeywords($keywords);
			}
			
			if ($set == 'FEATURED') {
				$mri->filterByIsFeaturedRemotely(1);
			} else if ($set > 0) {
				$mri->filterBySet($set);
			}
			
			$mri->setType('themes');
			$mri->execute();
			
			$items = $mri->getPage();
	
			$this->set('sortBy', $sortBy);
			$this->set('selectedSet', $set);
			$this->set('list', $mri);
			$this->set('items', $items);
			$this->set('form', Loader::helper('form'));
			$this->set('sets', $setsel);
			$this->set('pagination', $mri->getPagination());
			$this->set('type', $what);
            */

		} else {
			$this->redirect('/dashboard/extend/connect');
		}
	}
	


}