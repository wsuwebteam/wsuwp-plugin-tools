<div class="wsu-a-code-snippets">
	<p>
		<label>Snippet Type <span>Required</span></label>
		<select name="wsuwp_code_snippet_type">
			<option value="">...Select</option>
			<?php foreach ( self::get( 'snippet_types' ) as $snippet_key => $snippet_label ) : ?>
			<option value="<?php echo esc_attr( $snippet_key ); ?>" <?php selected( $snippet_key, $type ); ?>><?php echo esc_html( $snippet_label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label>Snippet Location <span>Required</span></label>
		<select name="wsuwp_code_snippet_location">
			<option value="" <?php selected( '', $location ); ?>>...Select</option>
			<option value="header" <?php selected( 'header', $location ); ?>>Header</option>
			<option value="body" <?php selected( 'body', $location ); ?>>After Open Body Tag</option>
			<option value="footer" <?php selected( 'footer', $location ); ?>>Footer</option>
		</select>
	</p>
	<p>
		<label>Code Snippet</label>
		<textarea name="wsuwp_code_snippet"><?php echo $snippet; ?></textarea>
	</p>
</div>
<style>
.wsu-a-code-snippets label {
	display: block;
	font-weight: bold;
	padding-bottom: 0.25rem;
}
.wsu-a-code-snippets label span {
	font-size: 0.6rem;
	color: #dc3232;
	font-weight: normal;
	padding-left: 0.5rem;
}
.wsu-a-code-snippets textarea {
	display: block;
	width: 100%;
	height: 400px;
	padding: 1rem;
	box-sizing: border-box;
}
</style>

