<?php
/**
 * Main navigation of pp
 *
 * User pp navigation page
 *
 * @link http://wp3.in
 * @since 0.0.1
 *
 * @package ProfilePress
 */

/**
 * global 
 */
global $pp_navigation;
?>
<ul id="pp-navigation" class="clearfix">
	<?php foreach ($pp_navigation as $k => $args) : ?>
		<li<?php echo !empty($args['class']) ? ' class="'.$args['class'].'"' : '' ?>>
		<a href="<?php echo $args['link'] ?>"><?php echo $args['title'] ?><i class="ppicon-chevron-right"></i></a>
	</li>
<?php endforeach; ?>
</ul>