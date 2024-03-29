<?php
/*
Plugin Name:  Post Components
Description:  Pull any component (title/date/content/excerpt/image) from either the most recent post or by id through a shortcode.
Version:      1.0
Author:       Noah Kim 
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

/* 

Resources:
https://developer.wordpress.org/reference/functions/wp_get_recent_posts/
https://developer.wordpress.org/reference/classes/wp_post/
https://stackoverflow.com/questions/8751564/get-latest-post-link-on-wordpress
https://www.wpbeginner.com/beginners-guide/how-to-find-post-category-tag-comments-or-user-id-in-wordpress/#:~:text=You%20can%20also%20view%20your,hover%20on%20your%20category%20title.
https://stackoverflow.com/questions/11434091/add-if-string-is-too-long-php
https://www.garethjmsaunders.co.uk/2015/03/07/changing-the-divi-projects-custom-post-type-to-anything-you-want/

Note:

Divi has projects, which is just a custom post type

WP_Post Object
(
    [ID] =>
    [post_author] =>
    [post_date] => 
    [post_date_gmt] => 
    [post_content] => 
    [post_title] => 
    [post_excerpt] => 
    [post_status] =>
    [comment_status] =>
    [ping_status] => 
    [post_password] => 
    [post_name] =>
    [to_ping] => 
    [pinged] => 
    [post_modified] => 
    [post_modified_gmt] =>
    [post_content_filtered] => 
    [post_parent] => 
    [guid] => 
    [menu_order] =>
    [post_type] =>
    [post_mime_type] => 
    [comment_count] =>
    [filter] =>
)

Docs:
[postComponent component="[title/content/excerpt/image/date]" type=["post_type"] limit="[word_count]"]

*/

function post_component($atts){

	//arguments for post query
	$args = array(
		'numberposts' => 1,
	);

	//if category specified
	if(isset($atts["category"])){
		$args["category"] = $atts["category"];
	}

    //project post type
    if(isset($atts["type"])){
        $args["post_type"] = $atts["type"]; 
    } //othwerise, it will default to regular post type

    $post = NULL;
	
    //get post by id
    if(isset($atts["id"])){
        $post = get_post($atts["id"], ARRAY_A);
    }
    //get most recent post
    else {
        $recent_posts = wp_get_recent_posts($args);
        if(!empty($recent_posts)){
            $post = $recent_posts[0];
        }
    }

    //post not found, so return empty string
    if(is_null($post)){
        return "";
    }

    //get the link of the post to attach to components
    $link = get_permalink($post["ID"]);

    //if no component is set, then just show the title
    if(!isset($atts["component"])){
        return "<a href='" . $link . "''>" . $post["post_title"] . "</a>";
    }

    //user specified component (text/image/date)
    $component = $atts["component"];

    //display the title
    if($component == "title"){
        return "<a href='" . $link . "''>" . $post["post_title"] . "</a>";
    }

    //post link
    if($component == "link"){
        return "<a href='" . $link . "''>" . $atts["name"] . "</a>";
    }

    //display the post content
    if($component == "content" || $component == "excerpt"){

        if($component == "content"){
            $string = $post["post_content"];
        } else {
            $string = $post["post_excerpt"];
        }

        //remove plugin shortcode wrapped around the content along with html tags
        $content = wp_strip_all_tags(preg_replace('/\[\/?et_pb.*?\]/', '', $string));

        //if the user specifies a word limit
    	if(isset($atts["limit"])){
            if (str_word_count($content, 0) > $atts["limit"]) {
                $words = str_word_count($content, 2);
                $pos   = array_keys($words);
                $text  = substr($content, 0, $pos[$atts["limit"]]) . '...';
                return "<a href='" . $link . "''>" . $text . "</a>";
            }
            return "<a href='" . $link . "''>" . $content . "</a>";
    	} 
        //othwerwise, show full content
        else {
    		return "<a href='" . $link . "''>" . $content . "</a>";
    	}
    } 

    //display the date
    if($component == "date"){
    	return "<a href='" . $link . "''>" . $post["post_date"] . "</a>";
    }
    
    //display the featured image within an img tag
    if($component == "image"){
    	return "<a href='" . $link . "''>" . get_the_post_thumbnail($post["ID"]) . "</a>";
    }

}

add_shortcode("postComponent", "post_component");

?>