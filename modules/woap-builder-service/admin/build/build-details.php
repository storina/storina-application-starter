<div class="woap-build-details-wrapper">
	<p><?php _e('Building Attempt Request Details','storina-application'); ?></p>
	<table class="woap-build-details">
		<tr>
			<td><?php _e('Build apk url','storina-application'); ?></td>
			<td><?php echo esc_html($apk_url); ?></td>
		</tr>
		<tr>
			<td><?php _e('Request Build send','storina-application'); ?></td>
			<td><?php echo esc_html($created_at_date); ?></td>
		</tr>
		<tr>
			<td><?php _e('Build Request Attempt','storina-application'); ?></td>
			<td><?php echo esc_html($build_attempt); ?></td>
		</tr>
	</table>
</div>
