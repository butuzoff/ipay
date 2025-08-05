<?php
class ModelExtensionPaymentIpay extends Model {
    
    /**
     * Получение метода оплаты для отображения в чекауте
     * @param array $address Адрес для проверки геозоны
     * @return array|bool Данные метода оплаты или false
     */
    public function getMethod($address) {
        $this->load->language('extension/payment/ipay');
        
        // Проверяем включен ли модуль
        if (!$this->config->get('payment_ipay_status')) {
            return false;
        }
        
        // Проверяем настройки подключения
        if (!$this->config->get('payment_ipay_mch_id') || !$this->config->get('payment_ipay_mch_key')) {
            return false;
        }
        
        // Проверяем геозону если настроена
        $geo_zone_id = $this->config->get('payment_ipay_geo_zone_id');
        if ($geo_zone_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
            
            if (!$query->num_rows) {
                return false;
            }
        }
        
        // Проверяем минимальную сумму заказа (если настроена)
        $total = $this->getTotal();
        $min_total = $this->config->get('payment_ipay_min_total');
        if ($min_total && $total < $min_total) {
            return false;
        }
        
        // Возвращаем данные метода оплаты
        $method_data = array(
            'code'       => 'ipay',
            'title'      => $this->language->get('text_title'),
            'terms'      => '',
            'sort_order' => $this->config->get('payment_ipay_sort_order')
        );
        
        return $method_data;
    }
    
    /**
     * Получение общей суммы заказа
     * @return float Общая сумма заказа
     */
    private function getTotal() {
        $this->load->model('setting/extension');
        
        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;
        
        // Загружаем расширения для подсчета итогов
        $results = $this->model_setting_extension->getExtensions('total');
        
        foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);
                
                // Проверяем существование метода getTotal
                $model_name = 'model_extension_total_' . $result['code'];
                if (isset($this->{$model_name}) && method_exists($this->{$model_name}, 'getTotal')) {
                    $this->{$model_name}->getTotal($totals, $taxes, $total);
                }
            }
        }
        
        return $total;
    }
    
    /**
     * Валидация настроек модуля
     * @return array Массив ошибок валидации
     */
    public function validateSettings() {
        $errors = array();
        
        if (!$this->config->get('payment_ipay_mch_id')) {
            $errors[] = 'Merchant ID is required';
        }
        
        if (!$this->config->get('payment_ipay_mch_key')) {
            $errors[] = 'Merchant Key is required';
        }
        
        // Проверяем формат Merchant ID (должен быть числом)
        $mch_id = $this->config->get('payment_ipay_mch_id');
        if ($mch_id && !is_numeric($mch_id)) {
            $errors[] = 'Merchant ID must be numeric';
        }
        
        return $errors;
    }
}
