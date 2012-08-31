<table width="170" class="sidebar" id="sidebar-date" cellspacing="0" summary="{SIDEBAR_DATE}">
<thead>
	<tr>
		<td class="sidebar-head" style="text-align: center">{MONTH_TITLE}</td>
	</tr>
</thead>
<tbody>
	<tr>
		<td class="sidebar-body" style="text-align: center">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr align="center">
					<!-- loop weekday on -->	
					<td width="22"><b>{LOOP_WEEKDAY}</b></td>
					<!-- loop weekday off -->
				</tr>
				<!-- loop monthweeks on -->
				<tr align="center">
					<!-- loop monthdays on -->
					<!-- switch notthismonth on -->
					<td>
						<a class="psf" href="{MINICAL_VIEW}.php?cal={CAL}&amp;getdate={DAYLINK}"><span class="G10G">{DAY}</span></a>
					</td>
					<!-- switch notthismonth off -->
					<!-- switch istoday on -->
					<td>
						<a class="ps2" href="{MINICAL_VIEW}.php?cal={CAL}&amp;getdate={DAYLINK}">{DAY}</a>
					</td>
					<!-- switch istoday off -->
					<!-- switch ismonth on -->
					<td>
						<a class="psf" href="{MINICAL_VIEW}.php?cal={CAL}&amp;getdate={DAYLINK}">{DAY}</a>
					</td>
					<!-- switch ismonth off -->
					<!-- loop monthdays off -->
				</tr>
				<!-- loop monthweeks off -->
			</table>
		</td>
	</tr>
</tbody>
</table>