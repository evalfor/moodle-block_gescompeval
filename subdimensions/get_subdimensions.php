<?php

require_once('../../../config.php');
require_once($CFG->dirroot.'/blocks/gescompeval_md/model/ws_evalcomix_client.php');
require_once($CFG->dirroot.'/blocks/evalcomix/classes/evalcomix_tool.php');

if(isset($_POST['id']) && $_POST['id'] != ''){

	$id = $_POST['id'];
	//$id = 287;

	// Get evalcomix tool by its id and if it exists, get the subdimensions
  	$tool = evalcomix_tool::fetch(array('id'=>$id));
  	if($tool){
  		if($tool->type == 'mixed'){
  			$mixtools = ws_evalcomix_client::get_tools_mixed($tool->idtool);//print_r($mixtools);
  			foreach ($mixtools as $dimensions){
				foreach ($dimensions as $dimname => $subdimensions){
					echo "<optgroup label='".htmlspecialchars($dimname, ENT_QUOTES, 'UTF-8')."'>";
					foreach ($subdimensions as $id => $name){
						echo "<option value='".$id."' title='".htmlspecialchars($name, ENT_QUOTES, 'UTF-8')."'>".htmlspecialchars($name, ENT_QUOTES, 'UTF-8')."</option>";
					}
					echo "</optgroup>";
				}
  			}
  		}
  		elseif ($tool->type == 'differential'){
  			$attributes = ws_evalcomix_client::get_attributes_differential($tool->idtool);
  			foreach ($attributes as $id => $name){
  				echo "<option value='".$id."' title='".htmlspecialchars($name, ENT_QUOTES, 'UTF-8')."'>".htmlspecialchars($name, ENT_QUOTES, 'UTF-8')."</option>";
  			}
  		}
  		else{
  			$dimensions = ws_evalcomix_client::get_dimensions($tool->idtool);
			foreach ($dimensions as $dimname => $subdimensions){
				echo "<optgroup label='".htmlspecialchars($dimname, ENT_QUOTES, 'UTF-8')."'>";
				foreach ($subdimensions as $id => $name){
					echo "<option value='".$id."' title='".htmlspecialchars($name, ENT_QUOTES, 'UTF-8')."'>".htmlspecialchars($name, ENT_QUOTES, 'UTF-8')."</option>";
				}
				echo "</optgroup>";
			}
  		}
	}
}
?>