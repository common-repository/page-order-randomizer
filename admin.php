<?php
function page_order_randomizer_admin()
{
?>
<div>
	<a href="http://www.fasw.ws">
		<img src="<?php echo plugin_dir_url(__FILE__) ?>fasw-logo.png" alt="FASW" />
	</a>
</div>
<h2>PAGE ORDER RANDOMIZER OPTIONS</h2>
<form method="post">
<input type="hidden" name="page" value="page_order_randomizer_main_menu" />
<?php 
global $wpdb, $table_name;
$issaved = false;
if (isset($_POST['page_order_randomizer_submit']))
{
    $sql = "DELETE FROM  $table_name";  
    $wpdb->query($sql);
    $dnow = date('Y-m-d H:i:s'); 
    if (isset($_POST['post']))
	foreach ($_POST['post'] as $s_post)
	{
		$wpdb->query( $wpdb->prepare( 
			"
				INSERT INTO $table_name
				(post_parent_id, refresh_time, last_refresh_date )
				VALUES ( %d, %d, %s )
			", 
		    $s_post, 
			isset($_POST['refresh_time'][$s_post])?$_POST['refresh_time'][$s_post]:0, 
			$dnow 
		));
	}
	$issaved = true;
}

$rn_posts = $wpdb->get_results( 
	"
	SELECT p.ID, p.post_title, r.refresh_time 
	FROM $wpdb->posts as p LEFT JOIN $table_name as r
	ON p.ID=r.post_parent_id
	WHERE post_status='publish' order by p.post_title
	"
);
?>	
<table cellspacing="0" class="wp-list-table widefat fixed posts" style="width: 800px;">
	<thead>
	<tr>
		<th style="padding-bottom: 10px;" class="manage-column column-cb check-column" id="cb" scope="col">
			<input type="checkbox">
		</th>
		<th style="" class="manage-column column-title sortable desc" id="title" scope="col">
			PAGE/POST (Selected Pages/Posts Children will be randomized)
		</th>
		<th style="width: 200px;" class="manage-column column-title sortable desc" id="title" scope="col">
			REFRESH INTERVAL (mins)
		</th>
	</thead>
	<tbody id="the-list">
	<?php 	
	foreach ($rn_posts as $rn_post) 
	{
	?>
		<tr valign="top" class="alternate author-self status-publish format-default iedit" id="post-1">
				<th class="check-column" scope="row">
					<input type="checkbox" value="<?php echo $rn_post->ID ?>" name="post[]" <?php if (isset($rn_post->refresh_time)) echo "checked"; ?>>
				</th>
				<td class="post-title page-title column-title">
					<strong><?php echo $rn_post->post_title ?></strong>
				</td>
			<td class="date column-date"><input type="text" name="refresh_time[<?php echo $rn_post->ID ?>]" style="width: 50px;" maxlength="5" value="<?php if (isset($rn_post->refresh_time)) echo $rn_post->refresh_time; ?>" /></td>		
		</tr>
	<?php
	}
	?>
	</tbody>
</table>
<br />
<input style="width: 390px; margin-right: 15px;" type="submit" name="page_order_randomizer_submit" value="SAVE" />
<input style="width: 390px;" type="button" name="page_order_randomizer_cancel" value="cancel" onclick="window.location.href=window.location.href;" />	
</form>
<?php 
if ($issaved) echo '<br /><h2>Options Saved!</h2>';
}
?>