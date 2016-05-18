<?php

//require_once(dirname(__FILE__) . '/../../../config.php');
//require_once(dirname(__FILE__) . '/../confgescompeval.php');
require_once(dirname(__FILE__) . '/../model/ws_gescompeval_client.php');

/**
 * Unit tests ws_gescompeval_client class
 * @group gescompeval
 *
 * phpunit blocks/gescompeval_md/tests/ws_gescompeval_client_test.php
 */
class ws_gescompeval_client_test extends advanced_testcase
{
    /**
	 * @covers ws_gescompeval_client::get_competencies
	 */
	public function test_get_competencies(){
		$url = ws_gescompeval_client::get_competencies();
		$this->assertNotNull($url);
		$this->assertIsA($url,'string');
		unset($url);
	}

	/**
	 * @covers ws_gescompeval_client::get_results
	 */
	public function test_get_results(){
		$url = ws_gescompeval_client::get_results();
		$this->assertNotNull($url);
		$this->assertIsA($url,'string');
		unset($url);
	}

	/**
	 * @covers ws_gescompeval_client::check_url
	 */
	public function test_check_url(){
		$url = ws_gescompeval_client::check_url('http://nanananananananananananananananabaaatmaaaan');
		$this->assertEquals($url,0);
		$url = ws_gescompeval_client::check_url('http://localhost');
		$this->assertNotNull($url);
		unset($url);
	}
}
