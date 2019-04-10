<?php

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');

class LastsOrders
{

    private $daysDelay;
    private $orderStateAccepted;

    public function __construct()
    {

        $this->daysDelay = Configuration::get('POP_ORDERS_DAYS_DELAY');
        $this->orderStateAccepted = Configuration::get('POP_ORDERS_ORDER_STATE_ACCEPTED');
    }

    public function getOneLastOrder()
    {

        $sql = 'SELECT address.city as city, ord.product_name AS product, img.id_image AS img, DATEDIFF(NOW() ,orders.invoice_date) AS days_ago  
                FROM '._DB_PREFIX_.'orders as orders
                    RIGHT JOIN '._DB_PREFIX_.'order_detail as ord 
                    ON orders.id_cart = ord.id_order 
                    LEFT JOIN '._DB_PREFIX_.'address as address 
                    ON orders.id_customer = address.id_customer 
                    LEFT JOIN '._DB_PREFIX_.'image AS img 
                    ON ord.product_id = img.id_product AND img.cover = 1 
                WHERE orders.valid = 1 
                    AND DATEDIFF(NOW() ,orders.invoice_date) < '.$this->daysDelay.' 
                    AND orders.current_state IN ('.$this->orderStateAccepted.')';


        $results = Db::getInstance()->executeS($sql);

        if  (count($results) > 0) {

            $rand = rand(0, (count($results) - 1));
            $result = $results[$rand];

            $order = [
                'city' => $result['city'],
                'product' => $result['product'],
                'linkImg' => '/img/p',
                'daysAgo' => $result['days_ago']
            ];

            for ($i = 0; $i < strlen($result['img']); $i++) {
                $order['linkImg'] .= '/' . $result['img'][$i];
            }

            $order['linkImg'] .= '/' . $result['img'] . '.jpg';

        } else {

            $order = [
                'city' => '',
                'product' => '',
                'linkImg' => '',
                'daysAgo' => '',
                'nothing' => true
            ];
        }

        return $order;
    }

    public function getOrderFormated($order)
    {
        if (isset($order['nothing'])) {

            $orderFormated = [
                'customer' => 'Il n\'y a aucun article vendu',
                'product' => '',
                'linkImg' => '/modules/poporders/img/smiley_sad.png',
                'daysAgo' => ''
            ];
        } else {

            $orderFormated = [
                'customer' => 'Quelqu\'un à '.$order['city'].' a commandé cet article',
                'product' => $order['product'],
                'linkImg' => $order['linkImg'],
                'daysAgo' => 'Il y a '.$order['daysAgo']
            ];

            if ($order['daysAgo'] == 1) {

                $orderFormated['daysAgo'] = ' Hier';
            } elseif ($order['daysAgo'] > 1) {

                $orderFormated['daysAgo'] .= ' jours';
            } else {
                // 0
                $orderFormated['daysAgo'] = 'Aujourd\'hui';
            }
        }


        return $orderFormated;
    }
}