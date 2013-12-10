<?php
namespace FluidTYPO3\Flux\Tests\Functional\Hook;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use FluidTYPO3\Flux\Backend\TceMain;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * @package Flux
 */
class HookSubscriberTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canExecuteUpdateCommandOnUnrecognisedRecord() {
		$record = $this->getSimpleRecordFixture();
		$this->attemptCommandExecution('update', $record);
		$this->attemptRecordManipulation($record, 'update');
		$this->anything();
	}

	/**
	 * @test
	 */
	public function canExecuteUpdateCommandOnRecognisedRecord() {
		$record = $this->getSimpleRecordFixtureWithSimpleFlexFormSource();
		$this->attemptCommandExecution('update', $record);
		$this->attemptRecordManipulation($record, 'update');
		$this->anything();
	}

	/**
	 * @test
	 */
	public function canExecuteMoveCommandOnUnrecognisedRecord() {
		$record = $this->getSimpleRecordFixture();
		$this->attemptCommandExecution('move', $record);
		$this->anything();
	}

	/**
	 * @test
	 */
	public function canExecuteMoveCommandOnRecognisedRecord() {
		$record = $this->getSimpleRecordFixtureWithSimpleFlexFormSource();
		$this->attemptCommandExecution('move', $record);
		$this->anything();
	}

	/**
	 * @test
	 */
	public function acceptsBasicRecordForModificationHookSubscribers() {
		$record = $this->getSimpleRecordFixture();
		$this->attemptRecordManipulation($record);
		$this->attemptRecordManipulation($record, 'update');
	}

	/**
	 * @param string $command
	 * @param array $record
	 * @param string $table
	 */
	protected function attemptCommandExecution($command, $record, $table = 'tt_content') {
		$id = Records::UID_CONTENT_NOPARENTNOCHILDREN;
		$reference = $this->getTceMainFixture();
		$subscriber = $this->createTceMainHookSubscriberInstance();
		$relativeTo = 0;
		$arguments = array('command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'preProcessCommand', $table, $id, $record, $arguments, $reference);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'postProcessCommand', $table, $id, $record, $arguments, $reference);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'postProcessDatabaseOperation', $table, $id, $record, $arguments, $reference);
		$this->any();
	}

	/**
	 * @param array $record
	 * @param string $status
	 * @param string $table
	 */
	protected function attemptRecordManipulation($record, $status = NULL, $table = 'tt_content') {
		$id = Records::UID_CONTENT_NOPARENTNOCHILDREN;
		$reference = $this->getTceMainFixture();
		$subscriber = $this->createTceMainHookSubscriberInstance();
		$arguments = array('record' => $record, 'table' => $table, 'id' => $id);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'preProcessRecord', $table, $id, $record, $arguments, $reference);
		$arguments = array('status' => $status, 'table' => $table, 'id' => $id, 'record' => $record);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'postProcessRecord', $table, $id, $record, $arguments, $reference);
		$this->any();
	}

	/**
	 * @return DataHandler
	 */
	protected function getTceMainFixture() {
		/** @var DataHandler $tceMain */
		$tceMain = $this->objectManager->get('TYPO3\CMS\Core\DataHandling\DataHandler');
		return $tceMain;
	}

	/**
	 * @return array
	 */
	protected function getSimpleRecordFixture() {
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		return $record;
	}

	/**
	 * @return array
	 */
	protected function getSimpleRecordFixtureWithSimpleFlexFormSource() {
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		return $record;
	}

	/**
	 * @return TceMain
	 */
	protected function createTceMainHookSubscriberInstance() {
		/** @var TceMain $subscriber */
		$subscriber = $this->getAccessibleMock('FluidTYPO3\Flux\Backend\TceMain');
		return $subscriber;
	}

}
