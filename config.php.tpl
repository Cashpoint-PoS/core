<?
$config=array();
$config["db"]=array();
$config["db"]["host"]="%%DBHOST%%";
$config["db"]["user"]="%%DBUSER%%";
$config["db"]["pass"]="%%DBPASS%%";
$config["db"]["db"]="%%DBNAME%%";
$config["paths"]["webroot"]="%%WEBROOT%%";
$config["paths"]["root"]=realpath(dirname(__FILE__));
$config["paths"]["api"]=$config["paths"]["webroot"]."/api.php";
$config["paths"]["filestore"]=$config["paths"]["root"]."/data";
