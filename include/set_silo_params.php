<?php
/**
 * Set Silo params for save
 *
 *
 */

if($name){$Silo->name = $name;}
if($shortname){$Silo->shortname = $shortname;}
if($paypal_account){$Silo->paypal_account = $paypal_account;}
if($financial_account_type){$Silo->financial_account = $financial_account_type;}
if($bank_name){$Silo->bank_name = $bank_name;}
if($bank_account_name){$Silo->bank_account_name = $bank_account_name;}
if($bank_account_number){$Silo->bank_account_number = $bank_account_number;}
if($bank_routing_number){$Silo->bank_routing_number = $bank_routing_number;}
if($org_name){$Silo->org_name = $org_name;}
if($ein){$Silo->ein = $ein;}
if($verified){$Silo->verified = $verified;}
if($issue_receipts){$Silo->issue_receipts = $issue_receipts;}
if($title){$Silo->title = $title;}
if($phone_number){$Silo->phone_number = $phone_number;}
if($address){$Silo->address = $address;}
if($longitude){$Silo->longitude = $longitude;}
if($latitude){$Silo->latitude = $latitude;}
if($silo_cat_id){$Silo->silo_cat_id = $silo_cat_id;}
if($start_date){$Silo->start_date = $start_date;}
if($end_date){$Silo->end_date = $end_date;}
if($goal){$Silo->goal = $goal;}
if($status){$Silo->status = $status;}
if($admin_notice){$Silo->admin_notice = $admin_notice;}
if($description){$Silo->description = $description;}
if($purpose){$Silo->purpose = $purpose;}
if(!$filename && !$success){ $Silo->photo_file = "default.png"; }
if($employee_discount){ $Silo->employee_discount = "$employee_discount"; }
if (param_post('publish') == 'Publish') { $Silo->silo_type = $silo_type; }
$silo_id = $Silo->Save();
?>
