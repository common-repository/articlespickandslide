<?php
/*
Plugin Name: articlesPickAndSlide
Plugin URI: 
Description: Adds an article slider on the sidebar of your website which shows articles from a specific category
Version: 1.0.0
Author: Pantaleo de Pinto, Felice Breglia
Author URI: http://www.felicebreglia.it
Tags: article, picker, article picker, article slider, article widget, slider widget, widget
*/

/*  Copyright 2012  Pantaleo de Pinto (email : depinto.pantaleo@gmail.com), Felice Breglia (email : info@felicebreglia.it)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'widgets_init', 'load_widget' );

function load_widget() {
    $pluginsBasePath = plugins_url();
    
    wp_register_script( 'jquery142', $pluginsBasePath.'/articlesPickAndSlide/jsLibs/jquery-1.4.2.js');
    wp_enqueue_script( 'jquery142' );
    
    wp_register_script( 'pickAndslide', $pluginsBasePath.'/articlesPickAndSlide/jsLibs/pickAndslide.js');
    wp_enqueue_script( 'pickAndslide' );

    wp_register_style('articlesPickAndSlide_style', $pluginsBasePath.'/articlesPickAndSlide/css/style.css');
    wp_enqueue_style( 'articlesPickAndSlide_style' ); 

    register_widget( 'ArticlesPickAndSlide' );
}

class ArticlesPickAndSlide extends WP_Widget {
    function ArticlesPickAndSlide() {
        /* Widget settings */
        $widget_opt = array( 'classname' => 'articlesPickAndSlide', 'description' => __('Questo widget ti permette di visualizzare uno slider degli articoli di una categoria','articlesPickAndSlide') );
		
		/* Widget creation */
        $this->WP_Widget( 'articlesPickAndSlide-widget', __('Articles Slider','articlesPickAndSlide'), $widget_opt);
    }
	
    function widget($args, $instance) {
        extract( $args );
		global $wpdb;
		/* User settings. */
      	$title = apply_filters('widget_title', $instance['title'] );
      	$num_max_articles = $instance['num_max_articles']; 
      	$articles_cat_id = $instance['articles_cat_id'];
      	
      	echo $before_widget;
      	
      	$query = "SELECT id, post_title, post_content, post_date " 
				."FROM wp_posts "
				."INNER JOIN wp_term_relationships ON wp_posts.id = wp_term_relationships.object_id "
				."WHERE term_taxonomy_id = ".$articles_cat_id." "
				."AND post_status = 'publish' "
				."ORDER BY post_date DESC "
				."LIMIT 0, ".$num_max_articles;

    	
        $contents = $wpdb->get_results($wpdb->prepare($query));
	
        if (count($contents) > 0){
            echo "<div class='sideBox'>";
                echo "<h4>";
                    // Widget Title 
                    if ( $title )
                            echo $before_title . $title . $after_title;
                echo "</h4>";

                echo "<div id='boxSliderArticle'>";
                    echo "<div class='slider'>";
                    echo "<div class='sliderContent'>";
                    foreach ($contents as $content) {
                        echo "<div class='item'>";
                            echo "<h3><a href='".get_permalink($content->id)."'>".$content->post_title."</a></h3>";
                            echo "<p class='data'>".date('d.m.Y',strtotime($content->post_date))."</p>"; //dmY
                            echo "<p>".substr($content->post_content,0,130);
                        echo "</div>";
                    }
                    echo "</div>";
                echo "</div>";
                echo "<div class='clr'></div>";
            echo "</div>";	
      	}
      	//wp_reset_query();
      	echo $after_widget;
    }
	
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
 
 	$instance['title'] = strip_tags( $new_instance['title'] );
      	$instance['num_max_articles'] = $new_instance['num_max_articles'];
      	$instance['articles_cat_id'] = $new_instance['articles_cat_id'];
 
      	return $instance;
   }
   
   function form( $instance ) {
      /* default settings */
      $defaults = array( 'title' => 'Article Slider', );
      $instance = wp_parse_args( (array) $instance, $defaults ); 
?>
	
    <!-- Title setting -->
    <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
        <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:inherit;" />
    </p>
    <!-- Id Articles Category setting -->
    <p>
			<label for="<?php echo $this->get_field_id( 'articles_cat_id' ); ?>">Category:</label> 
			<select id="<?php echo $this->get_field_id( 'articles_cat_id' ); ?>" name="<?php echo $this->get_field_name( 'articles_cat_id' ); ?>" style="width:inherit;">
			<?php
				global $wpdb;
				$get_List_categories = "SELECT wt.term_id AS 'term_id', wt.name AS 'name' "
									   ."FROM wp_terms wt "
									   ."INNER JOIN "
	 								   ."wp_term_taxonomy wtt ON wt.term_id = wtt.term_id "
									   ."WHERE "
									   ."wtt.taxonomy = 'category'";
									   
				$categories = $wpdb->get_results($wpdb->prepare($get_List_categories));
					if (count($categories) > 0){
						foreach ($categories as $category) {
							$cat_id = $category->term_id;
							$cat_name = $category->name;
							echo "<option ";
								if ( $cat_id == $instance['articles_cat_id'] ) echo 'selected="selected" '; 
							echo 'value = "'.$cat_id.'" > ';
							echo $cat_name;
							echo "</option>";
						}
					}
			?>
			</select>
	</p>
    <!-- Max Number of article to show setting -->
    <p>
        <label for="<?php echo $this->get_field_id( 'num_max_articles' ); ?>">Max number of articles to show:</label>
        <input id="<?php echo $this->get_field_id( 'num_max_articles' ); ?>" name="<?php echo $this->get_field_name( 'num_max_articles' ); ?>" value="<?php echo $instance['num_max_articles']; ?>" style="width:inherit;" />
    </p>

<?php
   }
}
?>