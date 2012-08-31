<nav class="span-24 top-navigation">
	<ul class="span-24 navigation-list">
	{foreach from=$site_primary_navigation key=key item=menu_item name=navigation}
		{assign var="ctr" value=$ctr+1}
		{if $ctr <= 8}
			{if $smarty.foreach.navigation.first}
				<li class="home"><a href="{$site_community_relative}{$menu_item.link_url}"{if $menu_item.link_new_window} target="_blank"{/if}><img src="{$template_relative}/images/home.png" /></a>
			{else}
				<li><a href="{$site_community_relative}{$menu_item.link_url}"{if $menu_item.link_new_window} target="_blank"{/if}>{$menu_item.link_title}</a>
				{if $menu_item.link_children}
					<span class="arrow-down">&#9660;</span>
					<ul class="submenu">
					{foreach from=$menu_item.link_children key=ckey1 item=child_item name=navigation}
						<li class="sub-pages{if $child_item.link_selected} selected{/if}"><a href="{$site_community_relative}{$child_item.link_url}"{if $child_item.link_new_window} target="_blank"{/if}>{$child_item.link_title}</a></li>
					{/foreach}
					</ul>
					</li>
				{else}
					</li>
				{/if}
			{/if}
		{/if}
	{/foreach}
	</ul>
</nav>