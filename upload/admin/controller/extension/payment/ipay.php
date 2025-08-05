<?php
/**
 * Админ контроллер модуля оплаты iPay для OpenCart 3.x
 * 
 * @author flancer.eu
 * @version 1.0.0
 * @license MIT
 */
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

        // Добавляем callback URL для отображения в админке
        $data['callback_url'] = HTTPS_CATALOG . 'index.php?route=extension/payment/ipay/callback';
        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/payment/ipay', $data));
    }
    
    /**
     * Валидация настроек модуля
     * @return bool Результат валидации
     */
    protected function validate() {
        // Проверяем права доступа
        if (!$this->user->hasPermission('modify', 'extension/payment/ipay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        // Проверяем обязательные поля
        if (empty($this->request->post['payment_ipay_mch_id'])) {
            $this->error['mch_id'] = $this->language->get('error_mch_id');
        } elseif (!is_numeric($this->request->post['payment_ipay_mch_id'])) {
            $this->error['mch_id'] = 'Merchant ID должен быть числом';
        }
        
        if (empty($this->request->post['payment_ipay_mch_key'])) {
            $this->error['mch_key'] = $this->language->get('error_mch_key');
        } elseif (strlen($this->request->post['payment_ipay_mch_key']) < 32) {
            $this->error['mch_key'] = 'Ключ слишком короткий (минимум 32 символа)';
        }
        
        return !$this->error;
    }
    
    /**
     * Проверка соединения с iPay API
     * @return array Результат проверки
     */
    public function testConnection() {
        $this->load->language('extension/payment/ipay');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'extension/payment/ipay')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $mch_id = $this->request->post['mch_id'] ?? '';
            $mch_key = $this->request->post['mch_key'] ?? '';
            $test_mode = $this->request->post['test_mode'] ?? false;
            
            if (empty($mch_id) || empty($mch_key)) {
                $json['error'] = 'Не указаны Merchant ID или Merchant Key';
            } else {
                // Простая проверка доступности API
                $api_url = $test_mode ? 
                    'https://sandbox-checkout.ipay.ua/api302' : 
                    'https://checkout.ipay.ua/api302';
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                
                $result = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($error) {
                    $json['error'] = 'Ошибка соединения: ' . $error;
                } elseif ($http_code >= 200 && $http_code < 400) {
                    $json['success'] = 'Соединение с API успешно';
                } else {
                    $json['error'] = 'API недоступен (HTTP ' . $http_code . ')';
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}