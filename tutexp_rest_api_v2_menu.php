<?php
/**
 * Plugin Name: Wordpress Rest API V2 Menu and Submenu Endpoint
 * Plugin URI: http://tutexp.com
 * Description: Adding menus endpoints on WP REST API v2
 * Version: 1.0.0
 * Author: Biswa Nath Ghosh (tapos)
 * Author URI: http://blog.tutexp.com
 * License: GPL2
 */

/**
 * Get all registered menus
 * @return array List of menus with slug and description
 */
function tutexp_wp_api_v2_menus_get_all_menus () {
    $menus = [];
    foreach (get_registered_nav_menus() as $slug => $description) {
        $obj = new stdClass;
        $obj->slug = $slug;
        $obj->description = $description;
        $menus[] = $obj;
    }

    return $menus;
}

/**
 * Get menu's data from his id
 * @param  array $data WP REST API data variable
 * @return object Menu's data with his items
 */
function tutexp_wp_api_v2_menus_get_menu_data ( $data ) {

//    $menu = new stdClass;
//	$menu->items = [];
    if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $data['id'] ] ) ) {
        $menu = get_term( $locations[ $data['id'] ] );
        $items = wp_get_nav_menu_items($menu->term_id);
        if( ! $items )
            return;

        $tmp = [];
        foreach( $items as $key => $item )
            $tmp[$item->ID] = [
                'id'        => $item->ID,
                'parent_id' => $item->menu_item_parent,
                'title'     => $item->title,
                'type'      => $item->type_label,
                'icon'      => $item->icon
            ];




        $tree =  tutexp_buildTree( $tmp, 0 );
        return  $tree ;


    }


}
function tutexp_buildTree( array &$elements, $parentId = 0 )
{
    $branch = array();
    foreach ( $elements as &$element )
    {
        if ( $element['parent_id'] == $parentId )
        {
            $children = tutexp_buildTree( $elements, $element['id'] );
            if ( $children )
            {
                $element['children'] = $children;
            }
            $branch[] = $element;
            unset( $element );
        }
    }
    return $branch;
}


add_action( 'rest_api_init', function () {
    register_rest_route( 'tutexpmenu/v2', '/menus', array(
        'methods' => 'GET',
        'callback' => 'tutexp_wp_api_v2_menus_get_all_menus',
    ) );

    register_rest_route( 'tutexpmenu/v2', '/menus/(?P<id>[a-zA-Z_(-]+)', array(
        'methods' => 'GET',
        'callback' => 'tutexp_wp_api_v2_menus_get_menu_data',
    ) );
} );
