<?php

require_once(dirname(__FILE__) . '/../model/skill.php');

/**
 * Unit tests skill class
 * @group gescompeval
 *
 * phpunit blocks/gescompeval_md/tests/skill_test.php
 */
class skill_test extends advanced_testcase
{
    /**
     * @covers skill::insert
     */
    public function test_insert()
    {
    	global $DB;
    	// Reset all changes automatically after this test
     	$this->resetAfterTest(true);

     	// Assert method exists
     	$skill = new skill('', 1,'competence');
     	$this->assertTrue(method_exists($skill, 'insert'));

    	// Assert return correct id
     	$id = $skill->insert();
    	$this->assertEquals($skill->get_id(), $id);

    	// Assert a correct inserting
    	$this->assertTrue($DB->record_exists($skill->table, array('id'=>$id)));

    	$skilldb = skill::fetch(array('id'=>$id));
    	$this->assertEquals($skill, $skilldb);

    	unset($skill);
    	unset($skilldb);
    }

    /**
     * @covers skill::fetch
     * @covers skill::fetch_all
     */
    public function test_fetch()
    {
    	// Reset all changes automatically after this test
    	$this->resetAfterTest(true);

    	$skill = new skill('', 1,'competence');
    	$skill->insert();
    	$skill = new skill('', 2,'competence');
    	$skill->insert();
    	$skill = new skill('', 3,'result');
    	$skill->insert();

    	// Assert method exists
    	$this->assertTrue(method_exists($skill, 'fetch'));

    	// Assert fetch_all
    	$competences = skill::fetch_all(array('type'=>'competence'));
    	foreach($competences as $competence){
    		$this->assertEquals($competence->get_type(), 'competence');
    	}
    	$this->assertCount(2, $competences);

    	// Assert fetch
    	$result = skill::fetch(array('type'=>'result'));
    	$this->assertEquals($skill, $result);

    	unset($skill);
    	unset($competences);
    	unset($result);
    }

    /**
     * @covers skill::set_connected_courses
     * @covers skill::get_courses
     */
    public function test_set_connected_courses()
    {
    	// Reset all changes automatically after this test
    	$this->resetAfterTest(true);

    	// Assert method exists
    	$skill = new skill();
    	$this->assertTrue(method_exists($skill, 'set_connected_courses'));

    	// Add datas
    	$skill->insert();
    	$course1 = $this->getDataGenerator()->create_course();
    	$course2 = $this->getDataGenerator()->create_course();

    	// Assert any course is connected with the object
    	$skill->set_connected_courses();
    	$this->assertCount(0, $skill->get_courses());

    	// Assert the two courses are connected with the object
    	$cc = new skill_course('', $skill->get_id(), $course1->id);
    	$cc->insert();
    	$cc = new skill_course('', $skill->get_id(), $course2->id);
    	$cc->insert();
    	$skill->set_connected_courses();
    	$this->assertCount(2, $skill->get_courses());

	    unset($skill);
	    unset($cc);
	    unset($course1);
	    unset($course2);
    }

    /**
     * @covers skill::delete
     */
    public function test_delete()
    {
    	global $DB;
    	// Reset all changes automatically after this test
    	$this->resetAfterTest(true);

    	// Assert method exists
    	$skill = new skill('', 1, 'competence');
    	$this->assertTrue(method_exists($skill, 'delete'));

    	// Assert delete
    	$id = $skill->insert();
    	$this->assertTrue($DB->record_exists($skill->table, array('id'=>$id)));
    	$skill->delete();
    	$this->assertFalse($DB->record_exists($skill->table, array('id'=>$id)));

    	unset($skill);
    }
}
