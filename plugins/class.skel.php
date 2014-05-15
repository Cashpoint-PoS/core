<?
class %%CLASSNAME%% extends DBObj {
  protected static $__table="%%DBTABLE%%";
  public static $mod="%%MODNAME%%";
  public static $sub="%%SUBNAME%%";
  
  public static $elements=array(
  %%ELEMENTS%%
  );
  public static $link_elements=array(
  );
  public static $list_elements=array(
  %%LISTELEMENTS%%
  );
  public static $detail_elements=array(
  %%DETAILELEMENTS%%
  );
  public static $edit_elements=array(
  %%EDITELEMENTS%%
  );
  public static $links=array(
  );
  public function processProperty($key) {
    $ret=NULL;
    switch($key) {
    }
    return $ret;
  }
}

plugins_register_backend_handler($plugin,"%%SUBNAME%%","list",array("%%CLASSNAME%%","listView"));
plugins_register_backend_handler($plugin,"%%SUBNAME%%","edit",array("%%CLASSNAME%%","editView"));
plugins_register_backend_handler($plugin,"%%SUBNAME%%","view",array("%%CLASSNAME%%","detailView"));
plugins_register_backend_handler($plugin,"%%SUBNAME%%","submit",array("%%CLASSNAME%%","processSubmit"));
