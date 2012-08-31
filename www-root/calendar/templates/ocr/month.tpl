{HEADER}
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="calborder">
		<tr>
			<td align="center" valign="middle">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr valign="top">
						<td align="left" width="615" class="title"><h1>{DISPLAY_DATE}</h1><span class="V9G">{CALENDAR_NAME} {L_CALENDAR}</span></td>
						<td align="right" width="120" class="navback">	
							<div style="padding-top: 3px;">
							<table width="120" border="0" cellpadding="0" cellspacing="0">
								<tr valign="top">
									<td><a class="psf" href="day.php?cal={CAL}&amp;getdate={GETDATE}"><img src="templates/{TEMPLATE}/images/day_on.gif" alt="{L_DAY}" border="0" /></a></td>
									<td><a class="psf" href="week.php?cal={CAL}&amp;getdate={GETDATE}"><img src="templates/{TEMPLATE}/images/week_on.gif" alt="{L_WEEK}" border="0" /></a></td>
									<td><a class="psf" href="month.php?cal={CAL}&amp;getdate={GETDATE}"><img src="templates/{TEMPLATE}/images/month_on.gif" alt="{L_MONTH}" border="0" /></a></td>
									<td><a class="psf" href="year.php?cal={CAL}&amp;getdate={GETDATE}"><img src="templates/{TEMPLATE}/images/year_on.gif" alt="{L_YEAR}" border="0" /></a></td>
								</tr>
							</table>
							</div>
						</td>
					</tr>  			
				</table>
			</td>
		</tr>	
	</table>
	{MONTH_LARGE|+0}
{FOOTER}