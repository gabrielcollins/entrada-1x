<div class="sidebar" id="sidebar">
	<table class="sidebar" id="sidebar-date" cellspacing="0" summary="{SIDEBAR_DATE}">
	<thead>
		<tr>
			<td class="sidebar-head">{SIDEBAR_DATE}</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="sidebar-body">
				<ul class="menu">
					<li class="link"><a class="psf" href="print.php?cal={CAL}&amp;getdate={GETDATE}&amp;printview={CURRENT_VIEW}">{L_GOPRINT}</a></li>
					<!-- switch display_download on -->
					<li class="link"><a class="psf" href="{SUBSCRIBE_PATH}">{L_SUBSCRIBE}</a></li>
					<li class="link"><a class="psf" href="{DOWNLOAD_FILENAME}">{L_DOWNLOAD}</a></li>
					<!-- switch display_download off -->
				</ul>
			</td>
		</tr>
	</tbody>
	</table>
	<br />
	<table class="sidebar" id="sidebar-date" cellspacing="0" summary="{SIDEBAR_DATE}">
	<thead>
		<tr>
			<td class="sidebar-head">{L_JUMP}</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="sidebar-body">
				<form style="margin-bottom:0;" action="{CURRENT_VIEW}.php" method="get">
					<select name="action" class="query_style" onchange="window.location=(this.options[this.selectedIndex].value);">{LIST_JUMPS}</select><br />
					<select name="action" class="query_style" onchange="window.location=(this.options[this.selectedIndex].value);">{LIST_ICALS}</select><br />
					<select name="action" class="query_style" onchange="window.location=(this.options[this.selectedIndex].value);">{LIST_YEARS}</select><br />
					<select name="action" class="query_style" onchange="window.location=(this.options[this.selectedIndex].value);">{LIST_MONTHS}</select><br />
					<select name="action" class="query_style" onchange="window.location=(this.options[this.selectedIndex].value);">{LIST_WEEKS}</select><br />
					<input type="hidden" name="cpath" value="{CPATH}" />
				</form>
				<!-- switch show_goto on -->
				<form style="margin-bottom:0;" action="day.php" method="get">
					<input type="hidden" name="cal" value="{URL_CAL}">
					<input type="text" style="width:160px; font-size:10px" name="jumpto_day">
					<input type="submit" value="Go"/>
				</form>
				<!-- switch show_goto off -->
			</td>
		</tr>
	</tbody>
	</table>
	<br />

	{MONTH_SMALL|-1}
	<br />

	{MONTH_SMALL|+0}
	<br />
	
	{MONTH_SMALL|+1}
	<br />
</div>
