<?php
/**
 * Template Name: Migración WOO a ACF
 */
   
   //Funcion para importar las imagenes solo con la url y retorno el id de la imagen
   function wp_insert_attachment_from_url($url, $parent_post_id = null) {

      if ( ! class_exists( 'WP_Http' ) ) {
         require_once ABSPATH . WPINC . '/class-http.php';
      }

      $http     = new WP_Http();
      $response = $http->request( $url );
      if ( 200 !== $response['response']['code'] ) {
         return false;
      }

      $upload = wp_upload_bits( basename( $url ), null, $response['body'] );
      if ( ! empty( $upload['error'] ) ) {
         return false;
      }

      $file_path        = $upload['file'];
      $file_name        = basename( $file_path );
      $file_type        = wp_check_filetype( $file_name, null );
      $attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
      $wp_upload_dir    = wp_upload_dir();

      $post_info = array(
         'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
         'post_mime_type' => $file_type['type'],
         'post_title'     => $attachment_title,
         'post_content'   => '',
         'post_status'    => 'inherit',
      );

      // Create the attachment.
      $attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );

      // Include image.php.
      require_once ABSPATH . 'wp-admin/includes/image.php';

      // Generate the attachment metadata.
      $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

      // Assign metadata to attachment.
      wp_update_attachment_metadata( $attach_id, $attach_data );

      return $attach_id;
   }


   //Consulta de productos
   $args = array(
     'post_type'      => 'product',
     'posts_per_page' => 1
   );

   $loop = new WP_Query($args);

   while ($loop->have_posts()): $loop->the_post();

      global $product;

      $product_id = $product->get_id();


      //Se valida si ya se importo el producto
      $result_valid = $GLOBALS['wpdb']->get_results("SELECT `post_id` FROM `wp_postmeta` WHERE  `meta_key` = 'woocommerce_id' AND `meta_value` = ".$product_id."");

      if (empty($result_valid)) {

         //Insetar productos en el nuevo post_type boat
         $args = array(
           'post_type' => 'boat',
           'post_title'     => $product->get_name(),
           'post_content'   => $product->get_description(),
           'post_excerpt'   => $product->get_short_description()
         );

         $post_id = wp_insert_post($args);

         //Se valida si se guardo de manera correcta el producto
         if ($post_id)
         {
            // General
            update_field('woocommerce_id', $product_id, $post_id);


            // Similar Yacht Listing
            update_field('display_similar_yachts', get_post_meta($product_id, 'wpcf-display-similar-yachts', true), $post_id);


            // Additional Yacht Fields
            $wp_insert_attachment_from_url = wp_insert_attachment_from_url(get_post_meta($product_id, 'wpcf-featured-image', true));
            update_field('featured_image', $wp_insert_attachment_from_url, $post_id);

            update_field('builders_name', get_post_meta($product_id, 'wpcf-builders-name', true), $post_id);
            update_field('year_built', get_post_meta($product_id, 'wpcf-year-built', true), $post_id);
            update_field('year_refit', get_post_meta($product_id, 'wpcf-year-refit', true), $post_id);
            update_field('yacht_length', get_post_meta($product_id, 'wpcf-yacht-length', true), $post_id);
            update_field('yacht_beam_width', get_post_meta($product_id, 'wpcf-yacht-beam-width', true), $post_id);
            update_field('yacht_draft_depth', get_post_meta($product_id, 'wpcf-yacht-draft-depth', true), $post_id);
            update_field('yacht_cruising_speed', get_post_meta($product_id, 'wpcf-yacht-cruising-speed', true), $post_id);
            update_field('yacht_crew', get_post_meta($product_id, 'wpcf-yacht-crew', true), $post_id);
            update_field('yacht_guests', get_post_meta($product_id, 'wpcf-yacht-guests', true), $post_id);
            update_field('yacht_cabins', get_post_meta($product_id, 'wpcf-yacht-cabins', true), $post_id);
            update_field('yacht_charter_time', get_post_meta($product_id, 'wpcf-yacht-charter-time', true), $post_id);
            update_field('charter_rate_all_inclusive_expenses', get_post_meta($product_id, 'wpcf-inclusive-or-expenses', true), $post_id);
            update_field('charter_rate_low', get_post_meta($product_id, 'wpcf-charter-rate-low', true), $post_id);
            update_field('charter_rate_high', get_post_meta($product_id, 'wpcf-charter-rate-high', true), $post_id);
            update_field('charter_rate_summer_low', get_post_meta($product_id, 'wpcf-charter-rate-summer-low', true), $post_id);
            update_field('charter_rate_summer_high', get_post_meta($product_id, 'wpcf-charter-rate-summer-high', true), $post_id);
            update_field('charter_rate_winter_low', get_post_meta($product_id, 'wpcf-charter-rate-winter-low', true), $post_id);
            update_field('charter_rate_winter_high', get_post_meta($product_id, 'wpcf-charter-rate-winter-high', true), $post_id);
            update_field('yacht_rate_notes', get_post_meta($product_id, 'wpcf-yacht-rate-notes', true), $post_id);
            update_field('cruising_areas_summer', get_post_meta($product_id, 'wpcf-cruising-areas-summer', true), $post_id);
            update_field('cruising_areas_winter', get_post_meta($product_id, 'wpcf-cruising-areas-winter', true), $post_id);
            update_field('boat_video_youtube', get_post_meta($product_id, 'wpcf-boat-video', true), $post_id);
            update_field('boat_video_vimeo', get_post_meta($product_id, 'wpcf-boat-video-vimeo', true), $post_id);
            //update_field('', get_post_meta($product_id, '', true), $post_id);


            // Custom Tabs
            update_field('tenders_water_toys', get_post_meta($product_id, 'wpcf-tenders-water-toys', true), $post_id);
            update_field('cabin_configuration', get_post_meta($product_id, 'wpcf-cabin-configuration', true), $post_id);
            update_field('cabin_resume', get_post_meta($product_id, 'wpcf-cabin-resume', true), $post_id);
         }

      }

      //Con este codigo se listan todos los post meta del producto

      /*$attributes = get_post_meta($product_id);

      foreach ($attributes as $key => $value) {

         echo $key." - ".get_post_meta($product_id, $key, true);
         echo "<br/><br/>";

      }*/

   endwhile;


   wp_reset_query();
?>