<section>
	<div class="panel-content">
		<h1>Additional Pages</h1>
		<ul>
			{foreach from=$site_primary_navigation key=key item=menu_item name=navigation}
				{foreach from=$menu_item.link_children key=ckey1 item=child_item name=navigation}
					{if $child_item.link_selected}
						{foreach from=$child_item.link_children key=ckey1 item=tertiary_item name=navigation}
							<li><a href="{$site_community_relative}{$tertiary_item.link_url}">{$tertiary_item.link_title}</a></li>
						{/foreach}
					{/if}
				{/foreach}
			{/foreach}
		</ul>
	</div>
</section>