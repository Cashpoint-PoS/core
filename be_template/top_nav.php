	<div id="topNav">
		 <ul>
		 	<li>
		 		<a href="#menuProfile" class="menu"><?=$_SESSION["user"]["displayname"]?></a>
		 		
		 		<div id="menuProfile" class="menu-container menu-dropdown">
					<div class="menu-content">
						<ul class="">
							<li><a href="be_index.php?mod=user&amp;sub=users&amp;id=<?=$_SESSION["user"]["id"]?>&amp;action=edit">Profil bearbeiten</a></li>
						</ul>
					</div>
				</div>
	 		</li>
		 	<li><a href="logout.php">Abmelden</a></li>
		 </ul>
	</div> <!-- #topNav -->
