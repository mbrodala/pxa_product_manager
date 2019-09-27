#
# Table structure for table 'tx_pxaproductmanager_domain_model_product'
#
CREATE TABLE tx_pxaproductmanager_domain_model_product (

  name varchar(255) DEFAULT '' NOT NULL,
  sku varchar(255) DEFAULT '' NOT NULL,
  price double(11,2) DEFAULT '0.00' NOT NULL,
  tax_rate decimal(5,2) DEFAULT '0.00' NOT NULL,
  teaser text,
  description text,
  disable_single_view tinyint(1) unsigned DEFAULT '0' NOT NULL,
  attributes_values  json DEFAULT NULL,
  attributes_files int(11) unsigned DEFAULT '0',
  related_products int(11) unsigned DEFAULT '0' NOT NULL,
  images int(11) unsigned DEFAULT '0',
  links int(11) unsigned DEFAULT '0' NOT NULL,
  sub_products int(11) unsigned DEFAULT '0' NOT NULL,
  alternative_title varchar(255) DEFAULT '' NOT NULL,
  keywords text,
  meta_description text,
  fal_links int(11) unsigned DEFAULT '0',
  assets int(11) unsigned DEFAULT '0' NOT NULL,
  accessories int(11) unsigned DEFAULT '0' NOT NULL,
  slug varchar(2048)

);

#
# Table structure for table 'tx_pxaproductmanager_domain_model_attributeset'
#
CREATE TABLE tx_pxaproductmanager_domain_model_attributeset (

  name varchar(255) DEFAULT '' NOT NULL,
  attributes int(11) unsigned DEFAULT '0' NOT NULL

);

#
# Table structure for table 'tx_pxaproductmanager_domain_model_attribute'
#
CREATE TABLE tx_pxaproductmanager_domain_model_attribute (

  name varchar(255) DEFAULT '' NOT NULL,
  label varchar(255) DEFAULT '' NOT NULL,
  type int(11) DEFAULT '0' NOT NULL,
  required tinyint(1) unsigned DEFAULT '0' NOT NULL,
  show_in_attribute_listing tinyint(1) unsigned DEFAULT '0' NOT NULL,
  show_in_compare tinyint(1) unsigned DEFAULT '0' NOT NULL,
  identifier varchar(255) DEFAULT '' NOT NULL,
  default_value varchar(255) DEFAULT '' NOT NULL,
  options int(11) unsigned DEFAULT '0' NOT NULL,
  label_checked varchar(255) DEFAULT '' NOT NULL,
  label_unchecked varchar(255) DEFAULT '' NOT NULL,
  icon int(11) unsigned DEFAULT '0'

);

#
# Table structure for table 'tx_pxaproductmanager_domain_model_option'
#
CREATE TABLE tx_pxaproductmanager_domain_model_option (

  attribute int(11) unsigned DEFAULT '0' NOT NULL,
  order_field int(11) unsigned DEFAULT '0' NOT NULL,
  value varchar(255) DEFAULT '' NOT NULL

);

#
# Table structure for table 'tx_pxaproductmanager_domain_model_link'
#
CREATE TABLE tx_pxaproductmanager_domain_model_link (

  product int(11) unsigned DEFAULT '0' NOT NULL,

  name varchar(255) DEFAULT '' NOT NULL,
  link varchar(255) DEFAULT '' NOT NULL,
  description tinytext

);

#
# Table structure for table 'tx_pxaproductmanager_product_product_mm'
#
CREATE TABLE tx_pxaproductmanager_product_product_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_pxaproductmanager_product_subproducts_product_mm'
#
CREATE TABLE tx_pxaproductmanager_product_subproducts_product_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'sys_category'
#
CREATE TABLE sys_category (
  pxapm_image int(11) unsigned DEFAULT '0',
  alternative_title tinytext,
  keywords text,
  meta_description text,
  pxapm_subcategories int(11) unsigned DEFAULT '0',
  pxapm_attributes_sets int(11) unsigned DEFAULT '0' NOT NULL,
  pxapm_description text,
  pxapm_tax_rate decimal(5,2) DEFAULT '0.00' NOT NULL,
  pxapm_slug varchar(2048)
);

#
# Add show in preview to file reference
#
CREATE TABLE sys_file_reference (
  pxapm_use_in_listing tinyint(4) DEFAULT '0' NOT NULL,
  pxapm_main_image tinyint(4) DEFAULT '0' NOT NULL,
  pxa_attribute int(11) unsigned DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tx_pxaproductmanager_attributeset_attribute_mm'
#
CREATE TABLE tx_pxaproductmanager_attributeset_attribute_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_pxaproductmanager_product_category_mm'
#
CREATE TABLE tx_pxaproductmanager_category_attributeset_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_pxaproductmanager_domain_model_filter'
#
CREATE TABLE tx_pxaproductmanager_domain_model_filter (

  type int(11) DEFAULT '0' NOT NULL,
  name varchar(255) DEFAULT '' NOT NULL,
  label varchar(255) DEFAULT '' NOT NULL,
  parent_category int(11) unsigned DEFAULT '0',
  attribute int(11) unsigned DEFAULT '0',
  inverse_conjunction tinyint(4) unsigned DEFAULT '0' NOT NULL

);

#
# Table structure for table 'tx_pxaproductmanager_product_accessories_product_mm'
#
CREATE TABLE tx_pxaproductmanager_product_accessories_product_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_pxaproductmanager_domain_model_order'
#
CREATE TABLE tx_pxaproductmanager_domain_model_order (

	products int(11) unsigned DEFAULT '0' NOT NULL,
	fe_user int(11) unsigned DEFAULT '0' NOT NULL,
	complete tinyint(4) unsigned DEFAULT '0' NOT NULL,
    serialized_order_fields blob,
    serialized_products_quantity blob,
    external_id varchar(255) DEFAULT '' NOT NULL,
    checkout_type varchar(255) DEFAULT 'default' NOT NULL

);

#
# Table structure for table 'tx_pxaproductmanager_order_product_mm'
#
CREATE TABLE tx_pxaproductmanager_order_product_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);
