<?php
/*
 * Plugin Name: List Category
 * Version: 1.0.1
 * Plugin URI:
 * Description: List the posts of a category.
 * Author: Ahmad Sherif
 * Author URI:
 */

class WP_Widget_List_Category extends WP_Widget {
	function WP_Widget_List_Category() {
		$widget_ops = array('classname' => 'widget_list_category', 'description' => __("List the posts of a category"));
		$this->WP_Widget('list-category-widget', __('List Category'), $widget_ops);
	}

	function widget($args, $instance) {
		global $wpdb;
		extract($args);
		if ($instance['widget-title'])
			$title = apply_filters('widget_title', $instance['widget-title']);
		else
			$title = apply_filters('widget_title', __('Category Content'));
		if (!$instance['category-id'])
			$lst = __('Please specify a category.');
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo $this->make_list($instance);
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		var_dump($new_instance, $old_instance);
		foreach ($new_instance as $key => $val)
			$new_instance[$key] = htmlspecialchars(strip_tags(stripslashes($new_instance[$key])), ENT_QUOTES);
		return $new_instance;
	}

	function form($instance) {
		echo '<label for="' . $this->get_field_id('widget-title') . '">' . __('Title:') . '</label>';
		echo '<input type="text" id="' . $this->get_field_id('widget-title') . '" name="' . $this->get_field_name('widget-title') . '" value="' . $instance['widget-title'] . '"/>';
		echo '<br />';
		echo '<label for="' . $this->get_field_id('category-id') . '">' . __('Category title:') . '</label>';
		$categories = get_categories();
		echo '<select id="' . $this->get_field_id('category-id') . '" name="' . $this->get_field_name('category-id') . '">';
		foreach ($categories as $category_obj) {
			echo '<option value="' . $category_obj->cat_ID . '"' . (($instance['category-id'] == $category_obj->cat_ID) ? 'selected="selected"' : '') .'>' . $category_obj->name . '</option>';
		}
		echo '</select>';
		echo '<br />';
		echo '<label for="' . $this->get_field_id('pages-count') . '">' . __('How many items to list:') . '</label>';
		echo '<input type="text" id="' . $this->get_field_id('pages-count') . '" name="' . $this->get_field_name('pages-count') . '" value="' . ((bool)$instance['pages-count'] ? $instance['pages-count'] : 5) . '" style="width: 40px;" />';
		echo '<br />';
		echo '<label for="' . $this->get_field_id('orderby') . '">' . __('Sort by:') . '</label>';
		$orderby = array(__('ID'), __('Date'), __('Title'));
		echo '<select id="' . $this->get_field_id('orderby') . '" name="' . $this->get_field_name('orderby') . '">';
		foreach ($orderby as $key => $val) {
			echo '<option value="'. $key . '"' . (($instance['orderby'] == $key) ? 'selected="selected"' : '') . '>' . $val . '</option>';
		}
		echo '</select>';
		$order = array(__('Descending'), __('Ascending'));
		echo '<br />';
		echo '<label for="' . $this->get_field_id('order') . '">' . __('Sort:') . '</label>';
		echo '<select id="' . $this->get_field_id('order') . '" name="' . $this->get_field_name('order') . '">';
		foreach ($order as $key => $val) {
			echo '<option value="'. $key . '"' . (($instance['order'] == $key) ? 'selected="selected"' : '') . '>' . $val . '</option>';
		}
		echo '</select>';
	}

	function make_list($instance) {
		$args = "category={$instance['category-id']}&numberposts={$instance['pages-count']}";
		if ($instance['orderby'] == '0')
			$args .= '&orderby=ID';
		else if ($instance['orderby'] == '1')
			$args .= '&orderby=date';
		else
			$args .= '&orderby=title';
		if ($instance['order'] == '0')
			$args .= '&order=DESC';
		else
			$args .= '&order=ASC';

		$_lst = get_posts($args);
		$lst = "<ul>\n";
		foreach ($_lst as $item) {
			$lst .= "<li><a title=\"{$item->post_title}\" href=\"" . get_permalink($item->ID) . "\">{$item->post_title}</a></li>\n";
		}
		$lst .= '</ul>';
		return $lst;
	}
}

function ListCategoryInit() {
	register_widget('WP_Widget_List_Category');
}
add_action('widgets_init', 'ListCategoryInit');