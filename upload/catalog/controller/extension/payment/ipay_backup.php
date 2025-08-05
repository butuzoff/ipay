<?php
/**
 * Контроллер оплаты iPay для OpenCart 3.x
 * 
 * @author flancer.eu
 * @version 1.0.0
 * @license MIT
 */
class ControllerExtensionPaymentIpay extends Controller {
    // Режим отладки - включите для логирования дополнительной информации
    const IPAY_DEBUG_MODE = false;
    
    // API URL-адреса iPay
    const IPAY_API_URL = 'https://checkout.ipay.ua/api302';
    const IPAY_SANDBOX_API_URL = 'https://sandbox-checkout.ipay.ua/api302';
    
    // Таймауты для cURL соединений
    const CURL_CONNECT_TIMEOUT = 10;
    const CURL_TIMEOUT = 30;
    
    // Поддерживаемые валюты
    const SUPPORTED_CURRENCIES = ['UAH', 'USD', 'EUR'];
    
    // Минимальная сумма платежа в копейках
    const MIN_AMOUNT = 100;

    public function index() {
        $this->load->language('extension/payment/ipay');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');
        return $this->load->view('extension/payment/ipay', $data);
    }

    public function confirm() {
        $this->load->language('extension/payment/ipay');
        $this->load->model('checkout/order');

        $json = array();

        if (!isset($this->session->data['order_id'])) {
            $json['error'] = 'Ошибка сессии: ID заказа не найден.';
        } else {
            $order_id = $this->session->data['order_id'];
            $order_info = $this->model_checkout_order->getOrder($order_id);

            if ($order_info) {
                // Prepare data for iPay API request
                $mch_id = $this->config->get('payment_ipay_mch_id');
                $sign_key = $this->config->get('payment_ipay_mch_key');
                $is_test_mode = $this->config->get('payment_ipay_test_mode');
                $api_url = $is_test_mode ? self::IPAY_SANDBOX_API_URL : self::IPAY_API_URL;
                $order_total = $order_info['total'];
                $total_in_default_currency = $this->currency->convert($order_total, $order_info['currency_code'], $this->config->get('config_currency'));
                $amount = (int)round($total_in_default_currency * 100);

                // Определяем валюту платежа
                $currency = $this->getCurrency($order_info['currency_code']);
                if (!$currency) {
                    $json['error'] = 'Валюта ' . $order_info['currency_code'] . ' не поддерживается';
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                    return;
                }
                
                // Валидируем сумму платежа
                if ($amount < self::MIN_AMOUNT) {
                    $json['error'] = 'Минимальная сумма платежа: ' . (self::MIN_AMOUNT / 100) . ' ' . $currency;
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                    return;
                }
                
                $desc = $this->sanitizeDescription('Оплата заказа №' . $order_info['order_id']);
                $info = json_encode(['order_id' => (int)$order_info['order_id']], JSON_UNESCAPED_UNICODE);
                // Генерируем криптографически безопасный salt и подпись
                $salt = bin2hex(random_bytes(32));
                $sign = hash_hmac('sha512', $salt, $sign_key);
               // Create XML request
                $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><payment></payment>');
                $auth = $xml->addChild('auth');
                $auth->addChild('mch_id', $mch_id);
                $auth->addChild('salt', $salt);
                $auth->addChild('sign', $sign);
                $urls = $xml->addChild('urls');
                $urls->addChild('good', $this->url->link('checkout/success', '', true));
                $urls->addChild('bad', $this->url->link('checkout/cart', '', true));
                $urls->addChild('auto_redirect_good', 1);
                $urls->addChild('auto_redirect_bad', 1);
                $transactions = $xml->addChild('transactions');
                $transaction = $transactions->addChild('transaction');
                $transaction->addChild('amount', $amount);
                $transaction->addChild('currency', $currency);
                $transaction->addChild('desc', $desc);
                $transaction->addChild('info', htmlspecialchars($info, ENT_XML1, 'UTF-8'));
                $xml->addChild('lifetime', 24);
                $xml->addChild('lang', $this->session->data['language'] == 'ru-ru' ? 'ru' : 'ua');
                $xml_string = $xml->asXML();
                // Prepare POST data
                $post_data = http_build_query(['data' => $xml_string]);

                if (self::IPAY_DEBUG_MODE) {
                    $this->log->write("[iPay Debug] Запрос для заказа #" . $order_id . ": " . $post_data);
                }

                $ch = curl_init();
                
                if (self::IPAY_DEBUG_MODE) {
                    curl_setopt($ch, CURLOPT_VERBOSE, true);
                    $verbose_log_stream = fopen('php://temp', 'w+');
                    curl_setopt($ch, CURLOPT_STDERR, $verbose_log_stream);
                }

                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CURL_CONNECT_TIMEOUT);
                curl_setopt($ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:']);

                $response_xml = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curl_error = curl_error($ch);
                
                if (self::IPAY_DEBUG_MODE) {
                    rewind($verbose_log_stream);
                    $verbose_log = stream_get_contents($verbose_log_stream);
                    fclose($verbose_log_stream);
                    $this->log->write("[iPay cURL Verbose Log] Для заказа #" . $order_id . ":\n" . $verbose_log);
                }

                curl_close($ch);

                if (self::IPAY_DEBUG_MODE) {
                    $this->log->write("[iPay] Ответ для заказа #" . $order_id . " (HTTP " . $http_code . "): " . $response_xml);
                    if ($curl_error) {
                        $this->log->write("[iPay] cURL Ошибка для заказа #" . $order_id . ": " . $curl_error);
                    }
                }

                if ($http_code == 200 && $response_xml) {
                    $response = simplexml_load_string($response_xml);
                    if ($response && isset($response->url)) {
                        $json['redirect'] = (string)$response->url;
                    } else {
                        $error_message = isset($response->message) ? (string)$response->message : 'Неверный ответ от шлюза.';
                        $json['error'] = 'Ошибка iPay: ' . $error_message;
                    }
                } else {
                    $json['error'] = 'Не удалось связаться со шлюзом iPay. Пожалуйста, попробуйте позже.';
                }
            } else {
                 $json['error'] = 'Ошибка: Заказ не найден.';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Получение поддерживаемой валюты
     */
    private function getCurrency($currency_code) {
        $currency_map = [
            'UAH' => 'UAH',
            'USD' => 'USD', 
            'EUR' => 'EUR',
            'RUB' => 'UAH', // Конвертируем в UAH
        ];
        
        return isset($currency_map[$currency_code]) ? $currency_map[$currency_code] : null;
    }
    
    /**
     * Очистка описания от опасных символов
     */
    private function sanitizeDescription($description) {
        // Удаляем HTML теги и опасные символы
        $clean = strip_tags($description);
        $clean = htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');
        // Ограничиваем длину
        return mb_substr($clean, 0, 255, 'UTF-8');
    }
    
    /**
     * Callback handler - обработчик уведомлений от iPay
    /**
     * Callback handler - обработчик уведомлений от iPay
     * Этот метод вызывается iPay при изменении статуса платежа.
     * Проверяет подпись запроса, обновляет статус заказа и логирует результат.
     */
    public function callback() {
        $request_body = file_get_contents('php://input');
        
        if (self::IPAY_DEBUG_MODE) {
            $this->log->write('iPay Callback Received: ' . $request_body);
        }
        // Проверяем безопасность - только HTTPS для продакшена
        if (!$this->config->get('payment_ipay_test_mode') && 
            (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
            if (self::IPAY_DEBUG_MODE) {
                $this->log->write('iPay Callback Security Error: HTTPS required for production');
            }
            http_response_code(400);
            exit('HTTPS required');
        }
        
        if (!$request_body) {
            if (self::IPAY_DEBUG_MODE) {
                $this->log->write('iPay Callback Error: Request body is empty.');
            }
            http_response_code(400);
            exit('Request body is empty.');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($request_body);

        if ($xml === false || !isset($xml->status)) {
            if (self::IPAY_DEBUG_MODE) {
                $this->log->write('iPay Callback Error: Failed to parse XML or missing status field.');
                // Логируем ошибки парсинга XML
                $errors = libxml_get_errors();
                foreach ($errors as $error) {
                    $this->log->write('XML Parse Error: ' . $error->message);
                }
                libxml_clear_errors();
            }
            http_response_code(400);
            exit('XML Parse Error');
        }

        $sign_key = $this->config->get('payment_ipay_mch_key');
        $received_salt = (string)$xml->salt;
        $received_sign = (string)$xml->sign;
        $calculated_sign = hash_hmac('sha512', $received_salt, $sign_key);

        if (!hash_equals($received_sign, $calculated_sign)) {
            if (self::IPAY_DEBUG_MODE) {
                $this->log->write('iPay Callback CRITICAL ERROR: Signature mismatch!');
                $this->log->write('Expected: ' . $calculated_sign);
                $this->log->write('Received: ' . $received_sign);
            }
            http_response_code(403);
            exit('Signature mismatch');
        }

        if (!isset($xml->order_id)) {
             if (self::IPAY_DEBUG_MODE) $this->log->write('iPay Callback CRITICAL ERROR: order_id is missing in callback!');
             http_response_code(400);
             exit('order_id is missing');
        }
        
        $order_id = (int)$xml->order_id;
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!$order_info) {
            if (self::IPAY_DEBUG_MODE) $this->log->write('iPay Callback ERROR: Order #' . $order_id . ' not found.');
            http_response_code(404);
            exit('Order not found');
        }

        $payment_status_code = (int)$xml->status;
        $pid = isset($xml->pid) ? (string)$xml->pid : 'N/A';
        // Determine the order status based on the payment status code
        $status_name = '';
        switch ($payment_status_code) {
            case 5: $status_name = 'Успешно'; $order_status_id = $this->config->get('payment_ipay_order_status_id'); $notify_customer = true; break;
            case 4: $status_name = 'Неуспешно'; $order_status_id = $this->config->get('payment_ipay_order_failed_status_id'); $notify_customer = false; break;
            case 3: $status_name = 'Авторизован'; $order_status_id = $order_info['order_status_id']; $notify_customer = false; break;
            case 1: $status_name = 'Зарегистрирован'; $order_status_id = $order_info['order_status_id']; $notify_customer = false; break;
            default: $status_name = 'Неизвестный статус (' . $payment_status_code . ')'; $order_status_id = $order_info['order_status_id']; $notify_customer = false;
        }
        
        $comment = "iPay Callback: \n" . "Статус: " . $status_name . " (код " . $payment_status_code . ")\n" . "ID платежа (pid): " . $pid;

        if ($order_info['order_status_id'] != $order_status_id || $payment_status_code < 4) {
             $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, $notify_customer);
             if (self::IPAY_DEBUG_MODE) $this->log->write('iPay: Order #' . $order_id . ' status updated to ' . $status_name);
        } else {
             if (self::IPAY_DEBUG_MODE) $this->log->write('iPay: Order #' . $order_id . ' status is already ' . $status_name . '. No update needed.');
        }

        echo 'OK';
        exit();
    }
}
