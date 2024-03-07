/*Remember to run the following after
composer du
*/

#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#~~~~~~~~~~~~ Local ~~~~~~~~~~~~~~
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
UPDATE `core_config_data` SET `value` = 'https://www.calor.test/' WHERE `path` = 'web/unsecure/base_url';
UPDATE `core_config_data` SET `value` = 'https://www.calor.test/' WHERE `path` = 'web/secure/base_url';
UPDATE `core_config_data` SET `value` = '0' WHERE `path` = 'dev/css/minify_files';
UPDATE `core_config_data` SET `value` = '0' WHERE `path` = 'dev/css/merge_css_files';
UPDATE `core_config_data` SET `value` = 'NOINDEX,NOFOLLOW' WHERE `path` = 'design/search_engine_robots/default_robots';
UPDATE `core_config_data` SET `value` = 'calor_dev' WHERE `path` = 'catalog/search/elasticsearch7_index_prefix';
UPDATE `core_config_data` SET `value` = 'elasticsearch7' WHERE `path` = 'catalog/search/engine';
UPDATE `core_config_data` SET `value` = '' WHERE `path` = 'sales_email/order/copy_to';

#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#~~~~~~~~ calor.selesti.net ~~~~~~
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
UPDATE `core_config_data` SET `value` = 'https://calor.selesti.net/' WHERE `path` = 'web/unsecure/base_url';
UPDATE `core_config_data` SET `value` = 'https://calor.selesti.net/' WHERE `path` = 'web/secure/base_url';
UPDATE `core_config_data` SET `value` = 'calor_live' WHERE `path` = 'catalog/search/elasticsearch7_index_prefix';
UPDATE `core_config_data` SET `value` = 'elasticsearch7' WHERE `path` = 'catalog/search/engine';
UPDATE `core_config_data` SET `value` = 'NOINDEX,NOFOLLOW' WHERE `path` = 'design/search_engine_robots/default_robots';
