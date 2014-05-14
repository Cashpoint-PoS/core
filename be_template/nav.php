<?
global $sb_nav;
global $mod;

?>
		<ul id="mainNav">			
<?
foreach($sb_nav as $id=>$nav) {
	if(!acl_check($nav["mod"],0,"r"))
		continue;
?>
			<li id="nav-<?=$id?>" class="nav <?= ($mod===$nav["mod"]) ? "active" : "" ?>">
				<span class="<?=$nav["icon"]?>"></span>
				<a href="be_index.php?mod=<?=$nav["mod"]?>&amp;action=list"><?=$nav["dname"]?></a>
<?
if(isset($nav["sub"]) && is_array($nav["sub"])) {
?>
				<ul class="subNav">
<?
	foreach($nav["sub"] as $sub=>$dname) {
		if(!acl_check($nav["mod"]."/".$sub,0,"r"))
			continue;
?>
					<li><a href="be_index.php?mod=<?=$nav["mod"]?>&amp;sub=<?=$sub?>&amp;action=list"><?=$dname?></a></li>
<?
	}
?>
				</ul>
<?
}
?>
			</li>
<?
}
?>
<? /*						
			<li id="navPages" class="nav">
				<span class="icon-document-alt-stroke"></span>
				<a href="javascript:;">Sample Pages</a>				
				
				<ul class="subNav">
					<li><a href="./invoice.html">Invoice</a></li>
					<li><a href="./support.html">Support</a></li>
					<li><a href="./people.html">People Directory</a></li>
					<li><a href="./calendar.html">Calendar</a></li>
					<li><a href="./stream.html">Stream</a></li>
					<li><a href="./gallery.html">Gallery</a></li>
					<li><a href="./reports.html">Reports</a></li>
				</ul>						
				
			</li>	
			
			<li id="navForms" class="nav">
				<span class="icon-article"></span>
				<a href="javascript:;">Form Elements</a>
				
				<ul class="subNav">
					<li><a href="./forms.html">Layouts & Elements</a></li>
					<li><a href="./forms-validations.html">Validations</a></li>					
				</ul>	
								
			</li>
			
			<li id="navType" class="nav">
				<span class="icon-info"></span>
				<a href="./typography.html">Typography</a>	
			</li>
			
			<li id="navGrid" class="nav">
				<span class="icon-layers"></span>
				<a href="./grids.html">Grid Layout</a>	
			</li>
			
			<li id="navTables" class="nav">
				<span class="icon-list"></span>
				<a href="./tables.html">Tables</a>	
			</li>
			
			<li id="navButtons" class="nav">
				<span class="icon-compass"></span>
				<a href="./buttons.html">Buttons & Icons</a>	
			</li>
			
			<li id="navInterface" class="nav">
				<span class="icon-equalizer"></span>
				<a href="./interface.html">Interface Elements</a>	
			</li>
			
			<li id="navCharts" class="nav">
				<span class="icon-chart"></span>
				<a href="./charts.html">Charts & Graphs</a>
			</li>
			
			<li id="navMaps" class="nav">
				<span class="icon-map-pin-fill"></span>
				<a href="./maps.html">Map Elements</a>
			</li>	
			
			<li class="nav">
				<span class="icon-denied"></span>
				<a href="javascript:;">Error Pages</a>
				
				<ul class="subNav">
					<li><a href="./error-401.html">401 Page</a></li>
					<li><a href="./error-403.html">403 Page</a></li>
					<li><a href="./error-404.html">404 Page</a></li>	
					<li><a href="./error-500.html">500 Page</a></li>	
					<li><a href="./error-503.html">503 Page</a></li>					
				</ul>	
			</li>
*/ ?>
		</ul>
				
