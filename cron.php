<?
$ts_start=microtime(true);
ini_set("zlib.output_compression","on");
header("Content-Type:application/json; charset=utf-8");

require("lib.php");
$ts_apistart=microtime(true);
$ret=array();
$log="";
try {
	$log.="Initializing job runner\n";
	$more=false;
	foreach($_core_jobrunners as $class) {
		$log.="Checking $class for available jobs\n";
		$jobs=$class::getOpenJobs();
		if(sizeof($jobs)>0) {
			$more=true;
			$i=0;
			foreach($jobs as $job) {
				$i++;
				$rv=$job->lockJob();
				if($rv==false)
					continue;
				$log.=$job->executeJob();
				if($i==1)
					break;
			}
		}
	}
	$ret["more"]=$more;
  $ret["status"]="ok";
  $ret["message"]=$log;
} catch(Exception $e) {
  $ret["status"]="error";
  $ret["message"]=$e->getMessage();
  $ret["type"]=get_class($e);
}
$ts_end=microtime(true);
$ret["rt"]=$ts_end-$ts_start;
$ret["api_rt"]=$ts_end-$ts_apistart;
if(isset($_GET["_mysqlprofile"])) {
  $ret["queries"]=DB::$queries;
  $ret["allqueries"]=DB::$allqueries;
}
echo pretty_json(json_encode($ret,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP));
