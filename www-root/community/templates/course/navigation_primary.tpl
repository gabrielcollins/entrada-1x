<div id="sidebar" class="sidebar">
	<table class="sidebar" cellspacing="0" cellpadding="0" border="0">
	<thead>
		<tr>
			<td class="sidebar-head">Course Navigation</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<table id="primary-navigation" style="width: 100%; table-layout: fixed" cellspacing="0" cellpadding="0" border="0">
				<tbody>
					<tr>
						<td>
							<ul class="navigation">
							{foreach from=$site_primary_navigation key=key item=menu_item name=navigation}
								<li{if $menu_item.link_selected} class="selected"{/if}><a href="{$site_community_relative}{$menu_item.link_url}"{if $menu_item.link_new_window} target="_blank"{/if}>{$menu_item.link_title}</a></li>
								{foreach from=$menu_item.link_children key=ckey1 item=child_item name=navigation}
									<li class="sub-pages{if $child_item.link_selected} selected{/if}"><a href="{$site_community_relative}{$child_item.link_url}"{if $child_item.link_new_window} target="_blank"{/if}>{$child_item.link_title}</a></li>
								{/foreach}
							{/foreach}
							</ul>
						</td>
					</tr>
				</tbody>
				</table>
			</td>
		</tr>
	</tbody>
	</table>
</div>