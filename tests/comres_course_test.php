<?php

require_once(dirname(__FILE__) . '/../model/skill_course.php');
require_once(dirname(__FILE__) . '/../model/skill.php');

/**
 * Unit tests skill_course class
 * @group gescompeval
 *
 * phpunit blocks/gescompeval_md/tests/skill_course_test.php
 */
class skill_course_test extends advanced_testcase
{
	/**
	 * @covers skill_course::insert
	 */
	public function test_insert()
	{
		global $DB;
		// Reset all changes automatically after this test
		$this->resetAfterTest(true);

		// Assert method exists
		$cc = new skill_course('', 1, 2);
		$this->assertTrue(method_exists($cc, 'insert'));

		// Assert return correct id
		$id = $cc->insert();
		$this->assertEquals($cc->get_id(), $id);

		// Assert a false inserting
		$this->assertFalse($DB->record_exists($cc->table, array('id'=>$id)));

		// Add a competence and a course
		$skill = new skill('', 1,'competence');
		$skill->insert();
		$course = $this->getDataGenerator()->create_course();

		// Assert a correct inserting
		$cc = new skill_course('', 1, $course->id);
		$id = $cc->insert();
		$this->assertTrue($DB->record_exists($cc->table, array('id'=>$id)));

		$ccdb = skill_course::fetch(array('id'=>$id));
		$this->assertEquals($cc, $ccdb);

		unset($skill);
		unset($cc);
		unset($ccdb);
		unset($id);
	}

	/**
	 * @covers skill_course::fetch
	 * @covers skill_course::fetch_all
	 */
	public function test_fetch()
	{
		// Reset all changes automatically after this test
		$this->resetAfterTest(true);

		// Add competences/results and courses
		$skill = new skill('', 1,'competence');
		$id1 = $skill->insert();
		$skill = new skill('', 2,'competence');
		$id2 = $skill->insert();
		$course1 = $this->getDataGenerator()->create_course();
		$course2 = $this->getDataGenerator()->create_course();

		$cc = new skill_course('', $id1, $course1->id);
		$cc->insert();
		$cc = new skill_course('', $id1, $course2->id);
		$cc->insert();
		$cc = new skill_course('', $id2, $course1->id);
		$cc->insert();

		// Assert method exists
		$this->assertTrue(method_exists($cc, 'fetch'));

		// Assert fetch_all
		$rows = skill_course::fetch_all(array('courseid'=>$course1->id));

		foreach($rows as $row){
			$this->assertEquals($row->get_courseid(), $course1->id);
		}
		$this->assertCount(2, $rows);

		// Assert fetch
		$row = skill_course::fetch(array('skillid'=>$id2));
		$this->assertEquals($cc, $row);

		unset($id1);
		unset($id2);
		unset($course1);
		unset($course2);
		unset($skill);
		unset($cc);
		unset($rows);
		unset($row);
	}

	/**
	 * @covers skill_course::delete
	 */
	public function test_delete()
	{
		global $DB;
		// Reset all changes automatically after this test
		$this->resetAfterTest(true);

		$skill = new skill('', 1,'competence');
		$id1 = $skill->insert();
		$course1 = $this->getDataGenerator()->create_course();
		$cc = new skill_course('', $id1, $course1->id);

		// Assert method exists
		$this->assertTrue(method_exists($cc, 'delete'));

		// Assert delete
		$id = $cc->insert();
		$this->assertTrue($DB->record_exists($cc->table, array('id'=>$id)));
		$cc->delete();
		$this->assertFalse($DB->record_exists($cc->table, array('id'=>$id)));

		unset($skill);
		unset($id1);
		unset($id);
		unset($course1);
		unset($cc);
	}
}