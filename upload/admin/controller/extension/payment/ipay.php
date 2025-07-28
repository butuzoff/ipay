<?php
class ControllerExtensionPaymentIpay extends Controller {
    private $error = array();
    public function index() {
        $this->load->language('extension/payment/ipay');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_ipay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }
        $data['heading_title'] = $this->language->get('heading_title');
           if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['mch_id'])) {
            $data['error_mch_id'] = $this->error['mch_id'];
        } else {
            $data['error_mch_id'] = '';
        }

        if (isset($this->error['mch_key'])) {
            $data['error_mch_key'] = $this->error['mch_key'];
        } else {
            $data['error_mch_key'] = '';
        }
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/ipay', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['action'] = $this->url->link('extension/payment/ipay', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
        
        // ID merchant
        if (isset($this->request->post['payment_ipay_mch_id'])) {
            $data['payment_ipay_mch_id'] = $this->request->post['payment_ipay_mch_id'];
        } else {
            $data['payment_ipay_mch_id'] = $this->config->get('payment_ipay_mch_id');
        }

        // Key merchant
        if (isset($this->request->post['payment_ipay_mch_key'])) {
            $data['payment_ipay_mch_key'] = $this->request->post['payment_ipay_mch_key'];
        } else {
            $data['payment_ipay_mch_key'] = $this->config->get('payment_ipay_mch_key');
        }

        // Test mode
        if (isset($this->request->post['payment_ipay_test_mode'])) {
            $data['payment_ipay_test_mode'] = $this->request->post['payment_ipay_test_mode'];
        } else {
            $data['payment_ipay_test_mode'] = $this->config->get('payment_ipay_test_mode');
        }
        if (isset($this->request->post['payment_ipay_order_status_id'])) {
            $data['payment_ipay_order_status_id'] = $this->request->post['payment_ipay_order_status_id'];
        } else {
            $data['payment_ipay_order_status_id'] = $this->config->get('payment_ipay_order_status_id');
        }
           if (isset($this->request->post['payment_ipay_order_failed_status_id'])) {
            $data['payment_ipay_order_failed_status_id'] = $this->request->post['payment_ipay_order_failed_status_id'];
        } else {
            $data['payment_ipay_order_failed_status_id'] = $this->config->get('payment_ipay_order_failed_status_id');
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        if (isset($this->request->post['payment_ipay_geo_zone_id'])) {
            $data['payment_ipay_geo_zone_id'] = $this->request->post['payment_ipay_geo_zone_id'];
        } else {
            $data['payment_ipay_geo_zone_id'] = $this->config->get('payment_ipay_geo_zone_id');
        }
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        
               if (isset($this->request->post['payment_ipay_status'])) {
            $data['payment_ipay_status'] = $this->request->post['payment_ipay_status'];
        } else {
            $data['payment_ipay_status'] = $this->config->get('payment_ipay_status');
        }

        if (isset($this->request->post['payment_ipay_sort_order'])) {
            $data['payment_ipay_sort_order'] = $this->request->post['payment_ipay_sort_order'];
        } else {
            $data['payment_ipay_sort_order'] = $this->config->get('payment_ipay_sort_order');
        }

           $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
       $this->response->setOutput($this->load->view('extension/payment/ipay', $data));
    }
    
    protected function validate() {
             if (!$this->user->hasPermission('modify', 'extension/payment/ipay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
             if (!$this->request->post['payment_ipay_mch_id']) {
            $this->error['mch_id'] = $this->language->get('error_mch_id');
        }
        
        if (!$this->request->post['payment_ipay_mch_key']) {
            $this->error['mch_key'] = $this->language->get('error_mch_key');
        }
        return !$this->error;
    }
}