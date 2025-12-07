<?php
//require_once ('db.php');
/*
 # -- BEGIN LICENSE BLOCK ----------------------------------
 #
 # This file is part of MAGIX CMS.
 # MAGIX CMS, The tabspanelContent management system optimized for users
 # Copyright (C) 2008 - 2021 magix-cms.com <support@magix-cms.com>
 #
 # OFFICIAL TEAM :
 #
 #   * Aurelien Gerits (Author - Developer) <aurelien@magix-cms.com> <contact@aurelien-gerits.be>
 #
 # Redistributions of files must retain the above copyright notice.
 # This program is free software: you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation, either version 3 of the License, or
 # (at your option) any later version.
 #
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU General Public License for more details.
 #
 # You should have received a copy of the GNU General Public License
 # along with this program.  If not, see <http://www.gnu.org/licenses/>.
 #
 # -- END LICENSE BLOCK -----------------------------------
 #
 # DISCLAIMER
 #
 # Do not edit or add to this file if you wish to upgrade MAGIX CMS to newer
 # versions in the future. If you wish to customize MAGIX CMS for your
 # needs please refer to http://www.magix-cms.com for more information.
 */
/**
 * @category plugins
 * @package indexnow
 * @copyright  MAGIX CMS Copyright (c) 2011 - 2026 Gerits Aurelien, http://www.magix-dev.be, http://www.magix-cms.com
 * @license Dual licensed under the MIT or GPL Version 3 licenses.
 * @version 1.0
 * @create 06-12-2025
 * @Update 12-04-2021
 * @author Gérits Aurélien <contact@magix-dev.be>
 * @name plugins_indexnow_admin
 */
class plugins_indexnow_admin extends plugins_indexnow_db {
	/**
	 * @var backend_model_template $template
	 * @var backend_controller_plugins $plugins
	 * @var backend_model_data $data
	 * @var component_core_message $message
	 * @var backend_model_language $modelLanguage
	 * @var component_collections_language $collectionLanguage
	 * @var backend_controller_domain $domain
	 * @var component_routing_url $routingUrl
	 */
	protected backend_model_template $template;
	protected backend_controller_plugins $plugins;
	protected backend_model_data $data;
	protected component_core_message $message;
	protected backend_model_language $modelLanguage;
	protected component_collections_language $collectionLanguage;
	protected backend_controller_domain $domain;
	protected component_routing_url $routingUrl;
    protected backend_model_sitemap $sitemap;
    protected debug_logger $logger;

    /**
     * @var string $lang ,
     * @var string $action
     * @var string $tab
     */
    public string
		$lang,
		$action,
		$tab,
        $analyse_url;

    /**
     * @var int $edit
     */
    public int $edit;

    /**
     * @var array $indexnowData
     */
    public array $indexnowData, $conf;

	/**
	 *
	 */
    public function __construct(?backend_model_template $t = null) {
        $this->template = $t instanceof backend_model_template ? $t : new backend_model_template;
        $this->plugins = new backend_controller_plugins();
        $this->message = new component_core_message($this->template);
        $this->modelLanguage = new backend_model_language($this->template);
        $this->collectionLanguage = new component_collections_language();
        $this->logger = new debug_logger(MP_LOG_DIR);
        $this->data = new backend_model_data($this);
        $this->domain = new backend_controller_domain();
        $this->routingUrl = new component_routing_url();
        $this->sitemap = new backend_model_sitemap();
        $this->setting = new backend_controller_setting($t);
        $this->DBPages = new backend_db_pages();
        $this->DBNews = new backend_db_news();
        $this->DBCatalog = new backend_db_catalog();
        $this->DBPlugins = new backend_db_plugins();
    }

    /**
     * Assign data to the defined variable or return the data
     * @param string $type
     * @param string|int|null $id
     * @param string|null $context
     * @param bool|string $assign
     * @return mixed
     */
    private function getItems(string $type, $id = null, ?string $context = null, $assign = true) {
        return $this->data->getItems($type, $id, $context, $assign);
    }

    /**
     * Method to override the name of the plugin in the admin menu
     * @return string
     */
    public function getExtensionName(): string {
        return $this->template->getConfigVars('indexnow_plugin');
    }

    /**
     * @return string
     */
    private function setDefaultDomain(): string {
        $defaultDomain = '';
        $dbdomain = new backend_db_domain();
        $data = $dbdomain->fetchData(['context' => 'one', 'type' => 'defaultDomain']);
        if(!empty($data)) $defaultDomain = $data['url_domain'];
        return $defaultDomain;
    }
    private function setHostUrl(): string {
        $setting = $this->setting->setItemsData();
        if($setting['ssl'] === '0'){
            $host = 'http://';
        }else{
            $host = 'https://';
        }
        return $host.$this->setDefaultDomain();
    }

    /**
     * @param array $urls
     * @return bool|string|void
     */
    private function getIndexNow(array $urls){
        $basePath = component_core_system::basePath();
        $conf = $this->getItems('config',NULL,'one',false) ?: [];
        //if(file_exists($basePath. $conf['apikey'].'.txt')) {
            try {
                // Submitting multiple URLs efficiently
                $request = curl_init();
                $data = array(
                    'host' => $this->setDefaultDomain(),//str_replace('www.', '', $this->setDefaultDomain()),
                    'key' => $conf['apikey'],
                    'keyLocation' => $this->setHostUrl() . "/" . $conf['apikey'] . ".txt",
                    'urlList' => $urls
                );
                curl_setopt($request, CURLOPT_URL, "https://api.indexnow.org/indexnow");
                curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Host: api.indexnow.org'));
                curl_setopt($request, CURLOPT_POST, 1);
                curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($request);
                $curlInfo = curl_getinfo($request);
                curl_close($request);
                if ($curlInfo['http_code'] == '200') {
                    if ($response) {
                        return $response;
                    }
                }else{
                    $this->logger->tracelog(json_encode($data));
                    $this->logger->tracelog(json_encode($response));
                }
                //return $response;

            }catch (Exception $e){
                $this->logger->log('php', 'error', 'An error has occured : ' . $e->getMessage(), debug_logger::LOG_MONTH);
            }
       // }
    }

    /**
     * @param $debug
     * @return void
     * @throws Exception
     */
    private function setLoadData($debug = false) {
        $this->DBPages = new backend_db_pages();
        $this->DBNews = new backend_db_news();
        $this->DBCatalog = new backend_db_catalog();
        $this->DBPlugins = new backend_db_plugins();
        $this->template->configLoad();
        usleep(200000);
        $this->progress = new component_core_feedback($this->template);
        $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('control_of_data'),'progress' => 10));
        // LOAD active languages
        $lang = $this->collectionLanguage->fetchData(array('context'=>'all','type'=>'langs'));
        // Chargement de la configuration des mudles Core
        $setConfig = $this->sitemap->setConfigData();
        $allUrls = [];
        if($lang != null) {
            $i = 0;
            // ---- Sitemap URL
            foreach ($lang as $item) {
                $i++;
                //usleep(200000);
                $progress = 20 + (30 / (count($lang))) * ($i + 1);
                $this->progress->sendFeedback(array('progress' => $progress, 'rendering' => true));

                $langRootUrl = '/' . $item['iso_lang'] . '/';
                //$allUrls = $langRootUrl;

                if ($setConfig['pages'] != '0') {
                    // Load Data pages
                    $dataPages = $this->DBPages->fetchData(array('context' => 'all', 'type' => 'sitemap'), array('id_lang' => $item['id_lang']));
                    foreach ($dataPages as $key => $value) {

                        $pageUrl = $this->routingUrl->getBuildUrl(array(
                            'type' => 'pages',
                            'iso' => $value['iso_lang'],
                            'id' => $value['id_pages'],
                            'url' => $value['url_pages']
                        ));

                        $allUrls[] = $this->setHostUrl() . $pageUrl;
                    }
                }
                // WriteNode News
                if($setConfig['news'] != '0') {
                    // WriteNode Root News
                    $allUrls []= $this->setHostUrl() .'/' . $item['iso_lang'] . '/news/';
                    // Load Data news
                    $dataNews = $this->DBNews->fetchData(array('context' => 'all', 'type' => 'sitemap'), array('id_lang' => $item['id_lang']));
                    foreach ($dataNews as $key => $value) {
                        $newsUrl = $this->routingUrl->getBuildUrl(array(
                                'type' => 'news',
                                'iso' => $value['iso_lang'],
                                'date' => $value['date_publish'],
                                'id' => $value['id_news'],
                                'url' => $value['url_news']
                            )
                        );
                        $allUrls[] = $this->setHostUrl() . $newsUrl;
                    }
                    $dataTagsNews = $this->DBNews->fetchData(array('context' => 'all', 'type' => 'tags'), array('id_lang' => $item['id_lang']));

                    foreach ($dataTagsNews as $key => $value) {
                        $newsTagUrl = $this->routingUrl->getBuildUrl(array(
                                'type' => 'tag',
                                'iso' => $item['iso_lang'],
                                'id' => $value['id_tag'],
                                'url' => $value['name_tag']
                            )
                        );
                        $allUrls[] = $this->setHostUrl() . $newsTagUrl;
                    }
                }
                // Load Data catalog
                if($setConfig['catalog'] != '0') {
                    // WriteNode Root catalog
                    $allUrls []= $this->setHostUrl() .'/' . $item['iso_lang'] . '/catalog/';

                    $dataCategory = $this->DBCatalog->fetchData(array('context' => 'all', 'type' => 'category'), array('id_lang' => $item['id_lang']));
                    foreach ($dataCategory as $key => $value) {

                        $categoryUrl = $this->routingUrl->getBuildUrl(array(
                                'type' => 'category',
                                'iso' => $value['iso_lang'],
                                'id' => $value['id_cat'],
                                'url' => $value['url_cat']
                            )
                        );
                        $allUrls[] = $this->setHostUrl() . $categoryUrl;
                    }
                    // WriteNode product catalog
                    $dataProduct = $this->DBCatalog->fetchData(array('context' => 'all', 'type' => 'product'), array('id_lang' => $item['id_lang']));
                    foreach ($dataProduct as $key => $value) {

                        $productUrl = $this->routingUrl->getBuildUrl(array(
                                'type' => 'product',
                                'iso' => $value['iso_lang'],
                                'id' => $value['id_product'],
                                'url' => $value['url_p'],
                                'id_parent' => $value['id_cat'],
                                'url_parent' => $value['url_cat']
                            )
                        );
                        $allUrls[] = $this->setHostUrl() . $productUrl;
                    }
                }
            }
            if($debug) {
                //$basePath = component_core_system::basePath();
                //$conf = $this->getItems('config',NULL,'one',false) ?: [];
                $this->logger->tracelog(json_encode($allUrls));
                //print $basePath. $conf['apikey'].'.txt';
            }else{
                $this->getIndexNow($allUrls);
            }
            usleep(200000);
            $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('creating_sitemap_success'), 'progress' => 100, 'status' => 'success'));

        }else{
            usleep(200000);
            $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('creating_error'),'progress' => 100,'status' => 'error','error_code' => 'error_data'));

        }
    }
    private function setLoadUrl(array $url) {}
    /**
     * @param string $data
     * @return void
     */
    private function indexFiles(string $data) {
        $basePath = component_core_system::basePath();
        $fh = fopen($basePath. $data.'.txt', 'w+');
        //$defaultDomain = $this->setDefaultDomain();
        $basePath = component_core_system::basePath();

        if(is_writable($basePath.$data.'.txt')) {
            fwrite($fh, $data . PHP_EOL);
            fclose($fh);
        }
    }

    /**
     * @param string $textarea_content
     * @param $debug
     * @return void
     */
    public function urlFromtext(string $textarea_content,$debug = false){
        // Tableau final des URLs absolues à soumettre
        $urls_to_submit = [];

        if (!empty($textarea_content)) {

            // Diviser la chaîne par saut de ligne
            $raw_urls = preg_split("/\r\n|\n|\r/", $textarea_content);

            // Boucle de Nettoyage et de Qualification
            foreach ($raw_urls as $url) {

                $clean_url = trim($url);

                if (empty($clean_url)) {
                    continue;
                }

                if (strpos($clean_url, 'http') === 0) {
                    // L'URL est déjà absolue
                    $urls_to_submit[] = $clean_url;

                } elseif (strpos($clean_url, '/') === 0) {
                    // L'URL est relative, on la qualifie
                    $qualified_url = rtrim($this->setHostUrl(), '/') . $clean_url;
                    $urls_to_submit[] = $qualified_url;

                } else {
                    $this->logger->log('php', 'error', 'URL that is incorrectly formatted or inaccurate'.$clean_url, debug_logger::LOG_MONTH);
                }
            }
        }
        if (!empty($urls_to_submit)) {
            if($debug) {
                //$basePath = component_core_system::basePath();
                //$conf = $this->getItems('config',NULL,'one',false) ?: [];
                $this->logger->tracelog(json_encode($urls_to_submit));
                //print $basePath. $conf['apikey'].'.txt';
            }else{
                $this->getIndexNow($urls_to_submit);
            }
        }
    }
    /**
     * @return void
     * @throws Exception
     */
    public function run(){
		if (http_request::isGet('edit')) $this->edit = form_inputEscape::numeric($_GET['edit']);
		if (http_request::isGet('tabs')) $this->tab = form_inputEscape::simpleClean($_GET['tabs']);
		if (http_request::isRequest('action')) $this->action = form_inputEscape::simpleClean($_REQUEST['action']);

		$config = $this->getItems('config',NULL,'one',false);

        if(http_request::isMethod('POST') && !empty($this->action)) {


			if (http_request::isPost('indexnowData')) $this->indexnowData = form_inputEscape::arrayClean($_POST['indexnowData']);
            if (http_request::isPost('analyse_url')) $this->analyse_url = form_inputEscape::simpleClean($_POST['analyse_url']);
            $status = false;
            $type = 'error';

            if($this->action === 'edit' && !empty($this->indexnowData)) {

				$newData = [
					'apikey' => $this->indexnowData['apikey']
				];

				if($config['id_indexnow']){
					$newData['id'] = $config['id_indexnow'];
					parent::update('config',$newData);
				}
				else {
					parent::insert('config',$newData);
				}
                $this->indexFiles($this->indexnowData['apikey']);
				$status = true;
				$type = 'update';
                $this->message->json_post_response($status, $type);

            }elseif($this->action === 'push'){

                $this->setLoadData();

            }elseif($this->action === 'text'){
                $textarea_content = $this->analyse_url ?? '';
                $this->urlFromtext($textarea_content);
                $status = true;
                $type = 'update';
                $this->message->json_post_response($status, $type);
            }
        }
		else {
            $this->template->assign('page', $config);
            $this->template->display('index.tpl');
        }
    }
}
