<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = [
	"NAME" => GetMessage("MULTDOMAIN_COMPONENT_DESCRIPTION_DATA"),
	"DESCRIPTION" => '',
//	"ICON" => "/images/catalog.gif",
	"COMPLEX" => "Y",
	"SORT" => 10,
	"PATH" => [
		"ID" => "dev2fun",
		"CHILD" => array(
			"ID" => "multidomain",
			"NAME" => GetMessage("MULTDOMAIN_COMPONENT_DESCRIPTION_GROUP_NAME"),
			"SORT" => 30,
		)
	]
];
?>