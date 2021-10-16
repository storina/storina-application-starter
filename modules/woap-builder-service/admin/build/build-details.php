<div class="woap-build-details-wrapper">
	<p><?php _e('Building Attempt Request Details','onlinerShopApp'); ?></p>
	<table class="woap-build-details">
		<tr>
			<td><?php _e('Build apk url','onlinerShopApp'); ?></td>
			<td><?php echo $apk_url; ?></td>
		</tr>
		<tr>
			<td><?php _e('Request Build send','onlinerShopApp'); ?></td>
			<td><?php echo $created_at_date ?></td>
		</tr>
		<tr>
			<td><?php _e('Build Request Attempt','onlinerShopApp'); ?></td>
			<td><?php echo $build_attempt; ?></td>
		</tr>
	</table>
</div>
