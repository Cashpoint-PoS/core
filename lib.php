<?
//library

//helper functions
require("lib/functions.php");

//Config
require("config.php");

//DB
require("DB.php");
require("DB_Query.php");
//Backend theme functions
require("be_template/lib.php");

require("lib/core.errorhandling.php");
require("lib/core.DBObj.php");
require("lib/core.notify.php");
require("lib/core.DBObj.Interface_HTML.php");
require("lib/core.DBObj.Interface_JSON.php");
require("lib/core.acl.php");
//this loads and inits the plugins
require("lib/core.plugins.php");

//only after all the classes were defined!
//see http://www.macuser.de/forum/f57/problem-_session-__php_incomplete_class-431681/#post4921533
session_start();

