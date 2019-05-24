<?php

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require('../class/LastsOrders.php');

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequestorder') {

    $lastsOrders = new LastsOrders();
    $order = $lastsOrders->getOrderFormated($lastsOrders->getOneLastOrder());

    echo json_encode($order, JSON_UNESCAPED_UNICODE);

    die();
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequestpopup') {

    echo json_encode(Configuration::get('POP_ORDERS_DELAY_REFRESH'), JSON_UNESCAPED_UNICODE);

    die();
}