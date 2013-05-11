<?php
	//we need this for either canceling/restoring an object or cleaning the page url
	$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https';
	$this_page = (
		$protocol."://"
		.$_SERVER['SERVER_NAME']
		.$_SERVER["SCRIPT_NAME"]
		."?view=radar"
	);
	if($_REQUEST["radar_action"]){
		if($_REQUEST["radar_ids"] && $_REQUEST["radar_ids"] != "false"){
			$remove_record_count = 0;
			$radar_ids = explode("|",$_REQUEST["radar_ids"]);
			$FlagRadar = new FlagRadar();
			foreach($radar_ids as $x){
				$FlagRadar->Populate($x);
				$FlagRadar->UpdateStatus($_REQUEST["radar_action"]);				
			}
			//redirect to this page for a clean url
			header("location: ".$this_page);
		
		}
	}
	 
	
	
	
	$FlagRadar = new FlagRadar();
	$Flag = new Flag();
	$radar_ids = $FlagRadar->GetIds("all","","view_radar");

?>
<?php if($radar_ids){ ?>
<script type="text/javascript">
function getCheckedValues(elementId){
	var x = new Array();
	$("input:checkbox[id="+elementId+"]:checked").each(function() {x.push($(this).val());});
	if (x.length == 0) {
		return false;
	} else {
		return x.join('|');
	}
}
function cancelRestoreRadar(action){
	var url = 
		"<?php echo $this_page; ?>&radar_ids="
		+getCheckedValues("radar_id")
		+"&radar_action="+action
	;
	
	window.location = url
}



</script>

<style type ="text/css">
	#radar_table{
		text-align:center;
		border:none;
		width:800px;
	}
	#radar_table th {
		border-bottom: 1px solid;
	    background-color: #2F8DCB;
		color: #FFFFFF;
		font-weight: bold;
		padding: 5px 3px 4px 3px;

	}
	#radar_table td {
		border-bottom: 1px solid;
		padding: 5px 3px 4px 3px;

	}
	#radar_table td.status{
		color:red;
		font-weight:700;

	}
	#radar_form{
		font-weight:700;
		width:800px;
	}
	#radar_form .left{
		width:60%;
	}
	#radar_form .right{
		width:40%;
	}
	#radar_form button{
		background-color: #2F8DCB;
		border-radius: 8px 8px 8px 8px;
		border-width: 0;
		color: #FFFFFF;
		font-family: Arial,Helvetica,sans-serif;
		font-size: 12px;
		font-weight: bold;
		padding: 5px 20px;	
	}
	#radar_form button:hover{
		background-color: #84BFE5;
		cursor:pointer;
	}
	#miles{
		width:70px;
	}
	#zip{
		width:70px;
	}
	#search{
		width:200px;
	}
</style>
<table id="radar_form">
	<tr>
		<td class="left">
			<button onclick="cancelRestoreRadar('cancel');">Cancel</button>
			<button onclick="cancelRestoreRadar('restore');">Restore</button>
		</td>
	</tr>
</table>


<table id="radar_table">
	<tr>
		<th>&nbsp;</th>
		<th>Object Type</th>
		<th>Status</th>
		<th>Cause</th>
		<th>User Name</th>
		<th>Silo</th>
		<th>Event Date</th>
		<th>Phone</th>
		<th>Email</th>
		<th>Flag</th>
	</tr>
	<?php 
		foreach($radar_ids as $radar_id){
		$FlagRadar->Populate($radar_id);
		$Flag->Populate($FlagRadar->flag_id);
		$Silo = new Silo($FlagRadar->silo_id);
		$User = new User($FlagRadar->user_id);
		
		$url_user = (
			"<a href='".ACTIVE_URL."index.php?task=view_user&id=".$User->id."' "
			."title='View User Page: ".ucfirst($User->fullname)."' "
			."target='_blank' "
			.">"
			.ucfirst($User->fullname)
			."</a>"
		);
		
		$url_silo = (
			"<a href='".ACTIVE_URL."index.php?task=view_silo&id=".$Silo->id."' "
			."title='View Silo Page: ".ucfirst($Silo->name)."' "
			."target='_blank' "
			.">"
			.ucfirst($Silo->name)
			."</a>"
		);
		
		$url_mailto = (
			"<a href='mailto:"
			.$User->email
			."?subject="
			."IMPOTANT: NOTICE FROM <?=SITE_NAME?>.com"
			."'>"
			.$User->email
			."</a>"
		);
		 
	?>
	<tr>
		<td><input id="radar_id" name="radar_id" type="checkbox" value="<?php echo $radar_id; ?>"/></td>
		<td><?php echo ucfirst($FlagRadar->who) ;?></td>
		<td class="status"><?php echo ucfirst($FlagRadar->status) ;?></td>
		<td><?php echo ucfirst($FlagRadar->cause) ;?></td>
		<td><?php echo $url_user ;?></td>
		<td><?php echo $url_silo ;?></td>
		<td><?php echo Formatter::DisplayDate($FlagRadar->created,"/") ;?></td>
		<td><?php echo Formatter::PhoneNumber($User->phone) ;?></td>
		<td><?php echo $url_mailto ;?></td>
		<td><?php echo $Flag->type ;?></td>
	</tr>
	<?php } ?>
</table>
<?php
}else{ ?>
	<center>
		<h1>Conratulations</h1>
		<p>There is nothing on the radar</p>
	</center>
<?php } ?>
