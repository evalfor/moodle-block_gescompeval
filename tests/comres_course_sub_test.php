<?php

require_once(dirname(__FILE__) . '/../model/skill_course_sub.php');
require_once(dirname(__FILE__) . '/../../evalcomix/classes/evalcomix_tool.php');
require_once(dirname(__FILE__) . '/../../evalcomix/classes/evalcomix.php');

/**
 * Unit tests skill_course_sub class
 * @group gescompeval
 *
 * phpunit blocks/gescompeval_md/tests/skill_course_sub_test.php
 */
class skill_course_sub_test extends advanced_testcase
{
	/**
	 * @covers skill_courses_sub::insert
	 */
	public function test_insert()
	{
		global $DB;
		// Reset all changes automatically after this test
		$this->resetAfterTest(true);

		// Assert method exists
		$cc = new skill_course_sub('', 1, 2);
		$this->assertTrue(method_exists($cc, 'insert'));

		// Assert return correct id
		$id = $cc->insert();
		$this->assertEquals($cc->get_id(), $id);

		// Assert a false inserting
		$this->assertFalse($DB->record_exists($cc->table, array('id'=>$id)));

		// Assert a correct inserting
		$ccs = $this->insert_data_test();
		$id = $ccs->insert();
		$this->assertTrue($DB->record_exists($ccs->table, array('id'=>$id)));

		$ccdb = skill_course_sub::fetch(array('id'=>$id));
		$this->assertEquals($ccs, $ccdb);

		unset($ccs);
		unset($cc);
		unset($ccdb);
		unset($id);
	}


	/**
	 * @covers skill_courses_sub::fetch
	 * @covers skill_courses_sub::fetch_all
	 */
	public function test_fetch()
	{
		// Reset all changes automatically after this test
		$this->resetAfterTest(true);

		// Insert data test
		$skill = new skill('', 1,'competence');
		$skill->insert();
		$course = $this->getDataGenerator()->create_course();
		$skill_course = new skill_course('', 1, $course->id);
		$skill_course->insert();
		$evx = new evalcomix('', 1);
		$evx->insert();
		$evxtool = new evalcomix_tool('', $evx->id, 'Test tool', 'list', '1');
		$evxtool->insert();
		$subdimension1 = new subdimension('', '12345qwerty', $evxtool->id);
		$subdimension1->insert();
		$subdimension2 = new subdimension('', '6789qwerty', $evxtool->id);
		$subdimension2->insert();
		$ccs1 = new skill_course_sub('', $skill_course->get_id(), $subdimension1->get_id());
		$ccs2 = new skill_course_sub('', $skill_course->get_id(), $subdimension1->get_id());
		$ccs3 = new skill_course_sub('', $skill_course->get_id(), $subdimension2->get_id());


		// Assert method exists
		$this->assertTrue(method_exists($ccs1, 'fetch'));

		// Assert fetch_all
		$ccs1->insert();
		$ccs2->insert();
		$ccs3->insert();

		$rows = skill_course_sub::fetch_all(array('subdimensionid'=>$subdimension1->get_id()));

		foreach($rows as $row){
			$this->assertEquals($row->get_subdimensionid(), $subdimension1->get_id());
		}
		$this->assertCount(2, $rows);

		// Assert fetch
		$row = skill_course_sub::fetch(array('id'=>$ccs3->get_id()));
		$this->assertEquals($ccs3, $row);

		unset($ccs1);
		unset($ccs2);
		unset($ccs3);
		unset($rows);
		unset($row);
		unset($skill);
		unset($skill_course);
		unset($evx);
		unset($evxtool);
		unset($subdimension1);
		unset($subdimension2);
	}

	/**
	 * @covers skill_courses_sub::delete
	 */
	public function test_delete()
	{
		global $DB;
		// Reset all changes automatically after this test
		$this->resetAfterTest(true);

		$ccs = $this->insert_data_test();

		// Assert method exists
		$this->assertTrue(method_exists($ccs, 'delete'));

		// Assert delete
		$id = $ccs->insert();
		$this->assertTrue($DB->record_exists($ccs->table, array('id'=>$id)));
		$ccs->delete();
		$this->assertFalse($DB->record_exists($ccs->table, array('id'=>$id)));

		unset($id);
		unset($ccs);
	}

	/**
	 * Insert data test
	 */
	public function insert_data_test(){
		$skill = new skill('', 1,'competence');
		$skill->insert();
		$course = $this->getDataGenerator()->create_course();
		$skill_course = new skill_course('', 1, $course->id);
		$skill_course->insert();
		$evx = new evalcomix('', 1);
		$evx->insert();
		$evxtool = new evalcomix_tool('', $evx->id, 'Test tool', 'list', '1');
		$evxtool->insert();
		$subdimension = new subdimension('', '12345qwerty', $evxtool->id);
		$subdimension->insert();
		$ccs = new skill_course_sub('', $skill_course->get_id(), $subdimension->get_id());
		return $ccs;
	}
}
