<?php

require_once(dirname(__FILE__) . '/../model/subdimension.php');
require_once(dirname(__FILE__) . '/../../evalcomix/classes/evalcomix_tool.php');
require_once(dirname(__FILE__) . '/../../evalcomix/classes/evalcomix.php');

/**
 * Unit tests subdimension class
 * @group gescompeval
 *
 * phpunit blocks/gescompeval_md/tests/subdimension_test.php
 */
class subdimension_test extends advanced_testcase
{
    /**
     * @covers subdimension::insert
     */
    public function test_insert()
    {
    	global $DB;
    	// Reset all changes automatically after this test
     	$this->resetAfterTest(true);

     	// Assert method exists
     	$subdimension = new subdimension('', '12345qwerty', 1);
     	$this->assertTrue(method_exists($subdimension, 'insert'));

    	// Assert return correct id
     	$id = $subdimension->insert();
    	$this->assertEquals($subdimension->get_id(), $id);

    	// Assert a false inserting
    	$this->assertFalse($DB->record_exists($subdimension->table, array('id'=>$id)));

    	// Add an evalcomix tool
    	$evx = new evalcomix('', 1);
    	$evx->insert();
    	$evxtool = new evalcomix_tool('', $evx->id, 'Test tool', 'list', '1');
    	$evxtool->insert();

    	// Assert a correct inserting
    	$subdimension = new subdimension('', '12345qwerty', $evxtool->id);
    	$id = $subdimension->insert();
    	$this->assertTrue($DB->record_exists($subdimension->table, array('id'=>$id)));

    	$subdimensiondb = subdimension::fetch(array('id'=>$id));
    	$this->assertEquals($subdimension, $subdimensiondb);

    	unset($subdimension);
    	unset($evxtool);
    	unset($id);
    	unset($subdimensiondb);
    }

    /**
     * @covers subdimension::fetch
     * @covers subdimension::fetch_all
     */
    public function test_fetch()
    {
    	// Reset all changes automatically after this test
    	$this->resetAfterTest(true);

    	// Add two evalcomix tools
    	$evx = new evalcomix('', 1);
    	$evx->insert();
    	$evxtool1 = new evalcomix_tool('', $evx->id, 'Test tool 1', 'list', 1);
    	$evxtool1->insert();
    	$evxtool2 = new evalcomix_tool('', $evx->id, 'Test tool 2', 'scale', 2);
    	$evxtool2->insert();

    	// Assert a correct inserting
    	$subdimension = new subdimension('', '12345qwerty', $evxtool1->id);
    	$subdimension->insert();
    	$subdimension = new subdimension('', '6789qwerty', $evxtool1->id);
    	$subdimension->insert();
    	$subdimension = new subdimension('', '101112qwerty', $evxtool2->id);
    	$subdimension->insert();

    	// Assert method exists
    	$this->assertTrue(method_exists($subdimension, 'fetch'));

    	// Assert fetch_all
    	$subdimensions = subdimension::fetch_all(array('toolid'=>$evxtool1->id));
    	foreach($subdimensions as $subdimension){
    		$this->assertEquals($subdimension->get_toolid(), $evxtool1->id);
    	}
    	$this->assertCount(2, $subdimensions);

    	// Assert fetch
    	$subfetch = subdimension::fetch(array('evxsubid'=>'6789qwerty'));
    	$this->assertEquals($subdimension, $subfetch);

    	unset($subdimension);
    	unset($evxtool);
    	unset($subdimensions);
    	unset($subfetch);
    }

    /**
     * @covers subdimension::set_connected_courses
     * @covers subdimension::get_courses
     */
    /*public function test_set_connected_courses()
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
    }*/

    /**
     * @covers subdimension::delete
     */
    public function test_delete()
    {
    	global $DB;
    	// Reset all changes automatically after this test
    	$this->resetAfterTest(true);

    	// Assert method exists
    	$evx = new evalcomix('', 1);
    	$evx->insert();
    	$evxtool1 = new evalcomix_tool('', $evx->id, 'Test tool 1', 'list', 1);
    	$evxtool1->insert();
    	$subdimension = new subdimension('', '12345qwerty', $evxtool1->id);
    	$this->assertTrue(method_exists($subdimension, 'delete'));

    	// Assert delete
    	$id = $subdimension->insert();
    	$this->assertTrue($DB->record_exists($subdimension->table, array('id'=>$id)));
    	$subdimension->delete();
    	$this->assertFalse($DB->record_exists($subdimension->table, array('id'=>$id)));

    	unset($subdimension);
    	unset($evxtool);
    }
}
