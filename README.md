wordpress-fieldmanager-sidebar
==============================
This Plugin adds the ability to add custom sidebars for individual posts.  The field will display a drop down of all available widgets and pull up the widget input fields when a widget is selected.  If a widget is populated in a post's custom sidebar, a hook fires to use this sidebar over the one requested.

To use this plugin first, create a sidebar named to your liking in the default widget admin area. In this example we will use 'my-sidebar-name'.  The widgets added here will be used as the default sidebar when a post has no custom sidebar data.

To set up a fieldmanager custom sidebar, add a metabox to your post type admin area:

```php
$fields = array(
	'custom_sidebar' => new Fieldmanager_Group( array(
		'name' => 'my-sidebar-name', //This name should be the same as the sidebar which is being used as the fallback default
		'label' => 'Custom Sidebar Widget',
		'limit'              => 0,
		'starting_count'     => 0,
		'add_more_label'     => 'Add another widget',
		'sortable'           => True,
		'collapsible' => True,
		'children' => array(
			'my-sidebar-name' => new Fieldmanager_Sidebar( array(
				'label' => 	'Widget Name',
				'first_empty' => True,
				'name' 	=> 	'my-sidebar-name', //This name should be the same as the sidebar which is being used as the fallback default
				'attributes' => array(
					'size' => '1',
				),
			) ),
		)
	) )
);

add_meta_box( 'example_custom_sidebar_meta_box', __('Custom Sidebar'), array( $fields['custom_sidebar'], 'render_meta_box' ), 'example-post-type', 'side', 'low' );
```

You can now populate the individual sidebar data, which will be saved when clicking the post publish/update button.  These widgets, can be dragged into the proper order (similar to the default widget admin area).  When you call the sidebar, a hook will overwrite the default sidebar if you have added custom sidebar data.  If there are no entries, the default will be used.

In our example above a hook will be called when the the function:

```php
fms_dynamic_sidebar( 'my-sidebar-name' );
```

is called.
