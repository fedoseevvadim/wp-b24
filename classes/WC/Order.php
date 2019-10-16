<?php

namespace WC;

class Order {

    public function get ( int $id ): array {

        global $wpdb;

        if ( empty ( $id ) ) {
            throw new \RuntimeException('Id of order is not defined or empty');
        }

        return $wpdb->get_results("SELECT * FROM wp_woocommerce_order_items WHERE order_id = $id");

    }

}