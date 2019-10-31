<?php

namespace WC;

use Psr\Log\InvalidArgumentException;

class Order {

    public function get ( int $id ): array {

        global $wpdb;

        if ( empty ( $id ) ) {
            throw new \RuntimeException('Id of order is not defined or empty');
        }

        return $wpdb->get_results("
                                            SELECT ITEMS.* FROM wp_woocommerce_order_items as ITEMS
                                            WHERE ITEMS.order_id = $id
                                        "
                                 );

    }

    function rsp_get_wc_order_notes( int $order_id ){

        if (empty( $order_id) ) {
            throw new InvalidArgumentException('Order ID is not set');
        }

        //get the post
        $post = get_post($order_id);

        //if there's no post, return as error
        if (!$post) return false;

        return $post->post_excerpt;
    }


}