<?php
class ControllerExtensionModuleRegistrationUpdate extends Controller {
	private $error = array();

	private static $config_defaults = array(
		'method',
		'prevent_closing',
		'fullname',
		'cpf',
		'cnpj',
		'telephone',
		'birthdate',
		'custom_field_cpf',
		'custom_field_cnpj',
		'custom_field_birthdate'
	);

	private static $config_alerts = array(
		'warning',
		'cpf',
		'cnpj',
		'birthdate'
	);

	public function index() {
		$this->load->language('extension/module/registration_update');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$registration_update_config = array();

			foreach (self::$config_defaults as $key) {
				$registration_update_config[$key] = (isset($this->request->post[$key]) ? $this->request->post[$key] : '');
			}

			$this->request->post['module_registration_update_config'] = json_encode($registration_update_config);

			$this->model_setting_setting->editSetting('module_registration_update', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->get['stay']) && ($this->request->get['stay'] == 1))
				$this->response->redirect($this->url->link('extension/module/registration_update', 'user_token=' . $this->session->data['user_token'], true));
			else
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$this->document->addScript('https://cdn.jsdelivr.net/npm/bootstrap5-toggle@4.2.0/js/bootstrap5-toggle.min.js');
		$this->document->addStyle('https://cdn.jsdelivr.net/npm/bootstrap5-toggle@4.2.0/css/bootstrap5-toggle.min.css');

		$data['heading_title'] = $this->language->get('heading_title');

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		foreach (self::$config_alerts as $alert) {
			if (isset($this->error[$alert]))
				$data['error_' . $alert] = $this->error[$alert];
			else
				$data['error_' . $alert] = array();
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'	=> $this->language->get('text_home'),
			'href'	=> $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text'	=> $this->language->get('text_extension'),
			'href'	=> $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text'	=> $this->language->get('heading_title'),
			'href'	=> $this->url->link('extension/module/registration_update', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/registration_update', 'user_token=' . $this->session->data['user_token'], true);
		$data['stay'] 	= $this->url->link('extension/module/registration_update', 'user_token=' . $this->session->data['user_token'] . '&stay=1', true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['module_registration_update_status']))
			$data['status'] = $this->request->post['module_registration_update_status'];
		else
			$data['status'] = $this->config->get('module_registration_update_status');

		$this->registration_update_config = json_decode($this->config->get('module_registration_update_config'), true);

		foreach (self::$config_defaults as $field) {
			if (isset($this->request->post[$field]))
				$data[$field] = $this->request->post[$field];
			else if (!empty($this->registration_update_config[$field]))
				$data[$field] = $this->registration_update_config[$field];
			else
				$data[$field] = '';
		}

		$this->load->model('customer/custom_field');

		$data['custom_fields'] = array();

		$filter_data = array(
			'sort'  => 'cf.name',
			'order' => 'ASC'
		);

		$custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['status'] && $custom_field['location'] == 'account') {
				$data['custom_fields'][] = array(
					'custom_field_id'	=> $custom_field['custom_field_id'],
					'name'						=> $custom_field['name'],
				);
			}
		}

		$data['header'] 		 = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] 		 = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/registration_update', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/registration_update'))
			$this->error['warning'] = $this->language->get('error_permission');

		if (isset($this->request->post['cpf']) && empty($this->request->post['custom_field_cpf']))
			$this->error['cpf'] = $this->language->get('error_cpf');

		if (isset($this->request->post['cnpj']) && empty($this->request->post['custom_field_cnpj']))
			$this->error['cnpj'] = $this->language->get('error_cnpj');

		if (isset($this->request->post['birthdate']) && empty($this->request->post['custom_field_birthdate']))
			$this->error['birthdate'] = $this->language->get('error_birthdate');

		if ($this->error && !isset($this->error['warning']))
			$this->error['warning'] = $this->language->get('error_warning');

		return !$this->error;
	}
}