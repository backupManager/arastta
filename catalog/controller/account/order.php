<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015-2016 Arastta Association. All rights reserved. (arastta.org)
 * @credits        See CREDITS.txt for credits and other copyright notices.
 * @license        GNU General Public License version 3; see LICENSE.txt
 */

class ControllerAccountOrder extends Controller {
    private $error = array();

    public function index() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/order', '', 'SSL');

            $this->response->redirect($this->url->link('account/login', '', 'SSL'));
        }

        $this->load->language('account/order');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', 'SSL')
        );

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/order', $url, 'SSL')
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_empty'] = $this->language->get('text_empty');

        $data['column_order_id'] = $this->language->get('column_order_id');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_date_added'] = $this->language->get('column_date_added');
        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_product'] = $this->language->get('column_product');
        $data['column_total'] = $this->language->get('column_total');
        $data['column_invoice'] = $this->language->get('column_invoice');

        $data['button_view'] = $this->language->get('button_view');
        $data['button_continue'] = $this->language->get('button_continue');
        $data['button_invoice'] = $this->language->get('button_invoice');

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['orders'] = array();

        $this->load->model('account/order');

        $order_total = $this->model_account_order->getTotalOrders();

        $results = $this->model_account_order->getOrders(($page - 1) * 10, 10);

        foreach ($results as $result) {
            $product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
            $voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

            $data['orders'][] = array(
                'order_id'   => $result['order_id'],
                'name'       => $result['firstname'] . ' ' . $result['lastname'],
                'status'     => $result['status'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'products'   => ($product_total + $voucher_total),
                'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
                'href'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL'),
                'invoice'    => !empty($result['invoice_no']) ? $this->url->link('account/order/invoice', 'order_id=' . $result['order_id'], 'SSL') : ''
            );
        }

        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->url->link('account/order', 'page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));

        $data['continue'] = $this->url->link('account/account', '', 'SSL');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/order_list.tpl')) {
            $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/order_list.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view('default/template/account/order_list.tpl', $data));
        }
    }

    public function info() {
        $this->load->language('account/order');

        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');

            $this->response->redirect($this->url->link('account/login', '', 'SSL'));
        }

        $this->load->model('account/order');

        $order_info = $this->model_account_order->getOrder($order_id);

        if ($order_info) {
            $this->document->setTitle($this->language->get('text_order'));

            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account', '', 'SSL')
            );

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/order', $url, 'SSL')
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_order'),
                'href' => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, 'SSL')
            );

            $data['heading_title'] = $this->language->get('text_order');

            $data['text_order_detail'] = $this->language->get('text_order_detail');
            $data['text_invoice_no'] = $this->language->get('text_invoice_no');
            $data['text_order_id'] = $this->language->get('text_order_id');
            $data['text_date_added'] = $this->language->get('text_date_added');
            $data['text_shipping_method'] = $this->language->get('text_shipping_method');
            $data['text_shipping_address'] = $this->language->get('text_shipping_address');
            $data['text_payment_method'] = $this->language->get('text_payment_method');
            $data['text_payment_address'] = $this->language->get('text_payment_address');
            $data['text_history'] = $this->language->get('text_history');
            $data['text_comment'] = $this->language->get('text_comment');

            $data['column_name'] = $this->language->get('column_name');
            $data['column_model'] = $this->language->get('column_model');
            $data['column_quantity'] = $this->language->get('column_quantity');
            $data['column_price'] = $this->language->get('column_price');
            $data['column_total'] = $this->language->get('column_total');
            $data['column_action'] = $this->language->get('column_action');
            $data['column_date_added'] = $this->language->get('column_date_added');
            $data['column_status'] = $this->language->get('column_status');
            $data['column_comment'] = $this->language->get('column_comment');

            $data['button_invoice'] = $this->language->get('button_invoice');
            $data['button_reorder'] = $this->language->get('button_reorder');
            $data['button_return'] = $this->language->get('button_return');
            $data['button_continue'] = $this->language->get('button_continue');

            $complete_statuses = $this->config->get('config_complete_status');

            if (in_array($order_info['order_status_id'], $complete_statuses) && !empty($order_info['invoice_no'])) {
                $data['invoice'] = $this->url->link('account/order/invoice', 'order_id=' . $order_info['order_id'], 'SSL');
            } else {
                $data['invoice'] = '';
            }

            if (isset($this->session->data['error'])) {
                $data['error_warning'] = $this->session->data['error'];

                unset($this->session->data['error']);
            } else {
                $data['error_warning'] = '';
            }

            if (isset($this->session->data['success'])) {
                $data['success'] = $this->session->data['success'];

                unset($this->session->data['success']);
            } else {
                $data['success'] = '';
            }

            if ($order_info['invoice_no']) {
                $data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
            } else {
                $data['invoice_no'] = '';
            }

            $data['order_id'] = $this->request->get['order_id'];
            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

            if ($order_info['payment_address_format']) {
                $format = $order_info['payment_address_format'];
            } else {
                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
            }

            $find = array(
                '{firstname}',
                '{lastname}',
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}'
            );

            $replace = array(
                'firstname' => $order_info['payment_firstname'],
                'lastname'  => $order_info['payment_lastname'],
                'company'   => $order_info['payment_company'],
                'address_1' => $order_info['payment_address_1'],
                'address_2' => $order_info['payment_address_2'],
                'city'      => $order_info['payment_city'],
                'postcode'  => $order_info['payment_postcode'],
                'zone'      => $order_info['payment_zone'],
                'zone_code' => $order_info['payment_zone_code'],
                'country'   => $order_info['payment_country']
            );

            $data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

            $data['payment_method'] = $order_info['payment_method'];

            if ($order_info['shipping_address_format']) {
                $format = $order_info['shipping_address_format'];
            } else {
                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
            }

            $find = array(
                '{firstname}',
                '{lastname}',
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}'
            );

            $replace = array(
                'firstname' => $order_info['shipping_firstname'],
                'lastname'  => $order_info['shipping_lastname'],
                'company'   => $order_info['shipping_company'],
                'address_1' => $order_info['shipping_address_1'],
                'address_2' => $order_info['shipping_address_2'],
                'city'      => $order_info['shipping_city'],
                'postcode'  => $order_info['shipping_postcode'],
                'zone'      => $order_info['shipping_zone'],
                'zone_code' => $order_info['shipping_zone_code'],
                'country'   => $order_info['shipping_country']
            );

            $data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

            $data['shipping_method'] = $order_info['shipping_method'];

            $this->load->model('catalog/product');
            $this->load->model('tool/upload');

            // Products
            $data['products'] = array();

            $products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

            foreach ($products as $product) {
                $option_data = array();

                $options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

                foreach ($options as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($upload_info) {
                            $value = $upload_info['name'];
                        } else {
                            $value = '';
                        }
                    }

                    $option_data[] = array(
                        'name'  => $option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                    );
                }

                $product_info = $this->model_catalog_product->getProduct($product['product_id']);

                if ($product_info) {
                    $reorder = $this->url->link('account/order/reorder', 'order_id=' . $order_id . '&order_product_id=' . $product['order_product_id'], 'SSL');
                } else {
                    $reorder = '';
                }

                $data['products'][] = array(
                    'name'     => $product['name'],
                    'model'    => $product['model'],
                    'option'   => $option_data,
                    'quantity' => $product['quantity'],
                    'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                    'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
                    'reorder'  => $reorder,
                    'return'   => $this->url->link('account/return/add', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL')
                );
            }

            // Voucher
            $data['vouchers'] = array();

            $vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);

            foreach ($vouchers as $voucher) {
                $data['vouchers'][] = array(
                    'description' => $voucher['description'],
                    'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
                );
            }

            // Totals
            $data['totals'] = array();

            $totals = $this->model_account_order->getOrderTotals($this->request->get['order_id']);

            foreach ($totals as $total) {
                $data['totals'][] = array(
                    'title' => $total['title'],
                    'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
                );
            }

            $data['comment'] = nl2br($order_info['comment']);

            // History
            $data['histories'] = array();

            $results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

            foreach ($results as $result) {
                $data['histories'][] = array(
                    'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'status'     => $result['status'],
                    'comment'    => $result['notify'] ? nl2br($result['comment']) : ''
                );
            }

            $data['continue'] = $this->url->link('account/order', '', 'SSL');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/order_info.tpl')) {
                $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/order_info.tpl', $data));
            } else {
                $this->response->setOutput($this->load->view('default/template/account/order_info.tpl', $data));
            }
        } else {
            $this->document->setTitle($this->language->get('text_order'));

            $data['heading_title'] = $this->language->get('text_order');

            $data['text_error'] = $this->language->get('text_error');

            $data['button_continue'] = $this->language->get('button_continue');

            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account', '', 'SSL')
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/order', '', 'SSL')
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_order'),
                'href' => $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL')
            );

            $data['continue'] = $this->url->link('account/order', '', 'SSL');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
                $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/error/not_found.tpl', $data));
            } else {
                $this->response->setOutput($this->load->view('default/template/error/not_found.tpl', $data));
            }
        }
    }

    public function reorder() {
        $this->load->language('account/order');

        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        $this->load->model('account/order');

        $order_info = $this->model_account_order->getOrder($order_id);

        if ($order_info) {
            if (isset($this->request->get['order_product_id'])) {
                $order_product_id = $this->request->get['order_product_id'];
            } else {
                $order_product_id = 0;
            }

            $order_product_info = $this->model_account_order->getOrderProduct($order_id, $order_product_id);

            if ($order_product_info) {
                $this->load->model('catalog/product');

                $product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);

                if ($product_info) {
                    $option_data = array();

                    $order_options = $this->model_account_order->getOrderOptions($order_product_info['order_id'], $order_product_id);

                    foreach ($order_options as $order_option) {
                        if ($order_option['type'] == 'select' || $order_option['type'] == 'radio' || $order_option['type'] == 'image') {
                            $option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
                        } elseif ($order_option['type'] == 'checkbox') {
                            $option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
                        } elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
                            $option_data[$order_option['product_option_id']] = $order_option['value'];
                        } elseif ($order_option['type'] == 'file') {
                            $option_data[$order_option['product_option_id']] = $this->encryption->encrypt($order_option['value']);
                        }
                    }

                    $this->cart->add($order_product_info['product_id'], $order_product_info['quantity'], $option_data);

                    $this->session->data['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $product_info['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                } else {
                    $this->session->data['error'] = sprintf($this->language->get('error_reorder'), $order_product_info['name']);
                }
            }
        }

        $this->response->redirect($this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL'));
    }

    public function invoice() {
        $this->load->model('account/order');
        $this->load->model('setting/setting');
        $this->load->model('tool/upload');

        $this->load->language('account/order');

        $data = $this->language->all();

        if ($this->request->server['HTTPS']) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }

        // Quick hack, must find a better way
        $data['base'] = str_replace('/admin', '', $data['base']);

        $data['orders'] = array();

        if (isset($this->request->post['selected'])) {
            $orders = $this->request->post['selected'];
        } else {
            $orders = array($this->request->get['order_id']);
        }

        foreach ($orders as $order_id) {
            $order_info = $this->model_account_order->getOrder($order_id);
            $invoice_info = $this->model_account_order->getOrderInvoice($order_id);

            if (empty($invoice_info) && !empty($this->request->post['proforma'])) {
                $invoice_info = array(
                    'invoice_id'   => 0,
                    'order_id'     => $order_id,
                    'invoice_date' => date('Y-m-d H:i:s')
                );

                $proforma = true;
            }

            if ($order_info && $invoice_info) {
                $store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

                if ($store_info) {
                    $store_address = $store_info['config_address'];
                    $store_email = $store_info['config_email'];
                    $store_telephone = $store_info['config_telephone'];
                    $store_fax = $store_info['config_fax'];
                } else {
                    $store_address = $this->config->get('config_address');
                    $store_email = $this->config->get('config_email');
                    $store_telephone = $this->config->get('config_telephone');
                    $store_fax = $this->config->get('config_fax');
                }

                $invoice_number = $order_info['invoice_prefix'] . $order_info['invoice_no'];

                if (isset($proforma)) {
                    $invoice_number = false;
                }

                if ($order_info['payment_address_format']) {
                    $format = $order_info['payment_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}',
                    '{company_id}',
                    '{tax_id}'
                );

                $replace = array(
                    'firstname' => $order_info['payment_firstname'],
                    'lastname'  => $order_info['payment_lastname'],
                    'company'   => $order_info['payment_company'],
                    'address_1' => $order_info['payment_address_1'],
                    'address_2' => $order_info['payment_address_2'],
                    'city'      => $order_info['payment_city'],
                    'postcode'  => $order_info['payment_postcode'],
                    'zone'      => $order_info['payment_zone'],
                    'zone_code' => $order_info['payment_zone_code'],
                    'country'   => $order_info['payment_country']
                );

                $payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                if ($order_info['shipping_address_format']) {
                    $format = $order_info['shipping_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_info['shipping_firstname'],
                    'lastname'  => $order_info['shipping_lastname'],
                    'company'   => $order_info['shipping_company'],
                    'address_1' => $order_info['shipping_address_1'],
                    'address_2' => $order_info['shipping_address_2'],
                    'city'      => $order_info['shipping_city'],
                    'postcode'  => $order_info['shipping_postcode'],
                    'zone'      => $order_info['shipping_zone'],
                    'zone_code' => $order_info['shipping_zone_code'],
                    'country'   => $order_info['shipping_country']
                );

                $shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                $product_data = array();

                $products = $this->model_account_order->getOrderProducts($order_id);

                foreach ($products as $product) {
                    $option_data = array();

                    $options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);

                    foreach ($options as $option) {
                        if ($option['type'] != 'file') {
                            $value = $option['value'];
                        } else {
                            $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                            if ($upload_info) {
                                $value = $upload_info['name'];
                            } else {
                                $value = '';
                            }
                        }

                        $option_data[] = array(
                            'name'  => $option['name'],
                            'value' => $value
                        );
                    }

                    $product_data[] = array(
                        'name'     => $product['name'],
                        'model'    => $product['model'],
                        'option'   => $option_data,
                        'quantity' => $product['quantity'],
                        'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                        'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
                    );
                }

                $voucher_data = array();

                $vouchers = $this->model_account_order->getOrderVouchers($order_id);

                foreach ($vouchers as $voucher) {
                    $voucher_data[] = array(
                        'description' => $voucher['description'],
                        'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
                    );
                }

                $total_data = array();

                $totals = $this->model_account_order->getOrderTotals($order_id);

                foreach ($totals as $total) {
                    $total_data[] = array(
                        'title' => $total['title'],
                        'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
                    );
                }

                $data['orders'][] = array(
                    'order_id'           => $order_id,
                    'invoice_number'     => $invoice_number,
                    'order_date'         => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                    'invoice_date'       => date($this->language->get('date_format_short'), strtotime($invoice_info['invoice_date'])),
                    'store_name'         => $order_info['store_name'],
                    'store_url'          => rtrim($order_info['store_url'], '/'),
                    'store_address'      => nl2br($store_address),
                    'store_email'        => $store_email,
                    'store_telephone'    => $store_telephone,
                    'store_fax'          => $store_fax,
                    'email'              => $order_info['email'],
                    'telephone'          => $order_info['telephone'],
                    'shipping_address'   => $shipping_address,
                    'shipping_method'    => $order_info['shipping_method'],
                    'payment_address'    => $payment_address,
                    'payment_method'     => $order_info['payment_method'],
                    'product'            => $product_data,
                    'voucher'            => $voucher_data,
                    'total'              => $total_data,
                    'comment'            => nl2br($order_info['comment'])
                );
            }
        }

        // BC for Arastta old templates
        $data['order'] = current($data['orders']);

        $data['logo'] = $this->config->get('config_logo');

        $html = $this->load->output('account/order_invoice', $data);

        include(DIR_SYSTEM . "mpdf/mpdf.php");

        $mpdf = new mPDF('', 'A4', 9, 'dejavusanscondensed');

        $mpdf->WriteHTML($html);

        // What do we want?
        $dest = !empty($this->request->get['dest']) ? $this->request->get['dest'] : 'D';

        $file_name = $invoice_number . '.pdf';

        if (isset($proforma)) {
            $file_name = $this->language->get('text_invoice_proforma') . ' ' . date($this->language->get('date_format_short'), strtotime($invoice_info['invoice_date'])) . '.pdf';
        }

        // http://mpdf1.com/manual/index.php?tid=125
        switch ($dest) {
            case 'D':
            case 'I':
                $mpdf->Output($file_name, $dest);
                exit();

                break;
            case 'F':
                $mpdf->Output(DIR_UPLOAD . $file_name, $dest);

                break;
            case 'S':
                return $mpdf->Output('', $dest);

                break;
        }
    }
}
