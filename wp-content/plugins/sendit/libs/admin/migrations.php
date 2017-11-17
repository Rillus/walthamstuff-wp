<?php

class Migrations{

	function GetLists()
	{
		global $wpdb;
    	$table_liste = SENDIT_LIST_TABLE;
		$liste=$wpdb->get_results("select * from $table_liste ");	
		return $liste;
	}

	function GetAllSubscribers()
	{
		global $wpdb;
    	$table_email = SENDIT_EMAIL_TABLE;
		$subscribers=$wpdb->get_results("select * from $table_email");	
		return $subscribers;
	}
	
	function GetSubscribersbyList($id)
	{
		global $wpdb;
    	$table_email = SENDIT_EMAIL_TABLE;
		$subscribers=$wpdb->get_results("select * from $table_email where id_lista=$id");	
		return $subscribers;
	}
	


	function json_field($json,$fieldname)
	{

		$options= urldecode($json->options);
		parse_str($options,$output);
		return $output[$fieldname];
		//print_r($output);
	}
		
	function GetListDetail($id_lista)
	{
		global $wpdb;
    	$table_liste = SENDIT_LIST_TABLE;
		$lista=$wpdb->get_row("select * from $table_liste where id_lista = $id_lista");	
		return $lista;
	}


}
?>