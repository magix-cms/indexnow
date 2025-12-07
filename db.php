<?php
/**
 * @category plugin
 * @package recaptcha
 * @copyright MAGIX CMS Copyright (c) 2011 - 2026 Gerits Aurelien, http://www.magix-dev.be, http://www.magix-cms.com
 * @license Dual licensed under the MIT or GPL Version 3 licenses.
 * @version 1.0
 * @create 06-12-2025
 * @author Aurélien Gérits <aurelien@magix-cms.com>
 * @name plugins_indexnow_db
 */
class plugins_indexnow_db {
	/**
	 * @var debug_logger $logger
	 */
	protected debug_logger $logger;

	/**
	 * @param array $config
	 * @param array $params
	 * @return array|bool
	 */
	public function fetchData(array $config, array $params = []) {
		if ($config['context'] === 'all') {
			switch ($config['type']) {
				default:
					return false;
			}

			try {
				return component_routing_db::layer()->fetchAll($query, $params);
			}
			catch (Exception $e) {
				if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
				$this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
			}
		}
		elseif ($config['context'] === 'one') {
			switch ($config['type']) {
				case 'config':
					$query = 'SELECT * FROM mc_indexnow ORDER BY id_indexnow DESC LIMIT 0,1';
					break;
				case 'page':
					$query = 'SELECT * FROM `mc_indexnow` WHERE `id_indexnow` = :id_indexnow';
					break;
				default:
					return false;
			}

			try {
				return component_routing_db::layer()->fetch($query, $params);
			}
			catch (Exception $e) {
				if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
				$this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
			}
		}
		return false;
    }

	/**
	 * @param string $type
	 * @param array $params
	 * @return bool
	 */
	public function insert(string $type, array $params = []): bool {
		switch ($type) {
			case 'config':
				$query = 'INSERT INTO mc_indexnow (apikey,date_register)
						VALUE(:apikey,NOW())';
				break;
			default:
				return false;
		}

		try {
			component_routing_db::layer()->insert($query,$params);
			return true;
		}
		catch (Exception $e) {
			if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
			$this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
			return false;
		}
    }

	/**
	 * @param string $type
	 * @param array $params
	 * @return bool
	 */
	public function update(string $type, array $params = []): bool {
        switch ($type) {
			case 'config':
				$query = 'UPDATE mc_indexnow
						SET 
							apikey = :apikey
						WHERE id_indexnow = :id';
				break;
			default:
				return false;
            }

		try {
			component_routing_db::layer()->update($query,$params);
			return true;
		}
		catch (Exception $e) {
			if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
			$this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
			return false;
		}
    }
}