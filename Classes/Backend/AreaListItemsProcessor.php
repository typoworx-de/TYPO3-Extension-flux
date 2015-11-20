<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Returns options for a "content area" selector box
 */
class AreaListItemsProcessor {

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var FluxService
	 */
	protected $fluxService;

	/**
	 * @var RecordService
	 */
	protected $recordService;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->fluxService = $this->objectManager->get('FluidTYPO3\Flux\Service\FluxService');
		$this->recordService = $this->objectManager->get('FluidTYPO3\Flux\Service\RecordService');
	}

	/**
	 * @return array
	 */
	protected function readParentAndAreaNameFromUrl() {
		$urlRequestedParent = ObjectAccess::getPropertyPath($_GET, 'defVals.tt_content.tx_flux_parent');
		$urlRequestedArea = ObjectAccess::getPropertyPath($_GET, 'defVals.tt_content.tx_flux_column');
		return array($urlRequestedParent, $urlRequestedArea);
	}

	/**
	 * ItemsProcFunc - adds items to tt_content.colPos selector (first, pipes through EXT:gridelements)
	 *
	 * @param array $params
	 * @return void
	 */
	public function itemsProcFunc(&$params) {
		list ($urlRequestedParent, $urlRequestedArea) = $this->readParentAndAreaNameFromUrl();
		if ($urlRequestedParent) {
			$parentUid = $urlRequestedParent;
		} else {
			$parentUid = $params['row']['tx_flux_parent'];
		}
		if ($parentUid > 0) {
			$items = $this->getContentAreasDefinedInContentElement($parentUid);
		} else {
			$items = array();
		}
		// adds an empty option in the beginning of the item list
		array_unshift($items, array('', ''));
		if ($urlRequestedArea) {
			foreach ($items as $index => $set) {
				if ($set[1] !== $urlRequestedArea) {
					unset($items[$index]);
				}
			}
		}
		$params['items'] = $items;
	}

	/**
	 * @param integer $uid
	 * @return array
	 */
	public function getContentAreasDefinedInContentElement($uid) {
		$uid = (integer) $uid;
		$record = $this->recordService->getSingle('tt_content', '*', $uid);
		/** @var $providers ProviderInterface[] */
		$providers = $this->fluxService->resolveConfigurationProviders('tt_content', NULL, $record);
		$columns = array();
		foreach ($providers as $provider) {
			$grid = $provider->getGrid($record);
			if (TRUE === empty($grid)) {
				continue;
			}
			$gridConfiguration = $grid->build();
			foreach ($gridConfiguration['rows'] as $row) {
				foreach ($row['columns'] as $column) {
					array_push($columns, array($column['label'] . ' (' . $column['name'] . ')', $column['name']));
				}
			}
		}
		return array_unique($columns, SORT_REGULAR);
	}

}
