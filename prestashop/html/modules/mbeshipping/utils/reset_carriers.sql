----------------------------------------------------------------
-- PS 1.4
----------------------------------------------------------------
DELETE FROM `ps_mbe_ps_14_carrier` WHERE `id_carrier` > 2;

ALTER TABLE `ps_mbe_ps_14_carrier` AUTO_INCREMENT = 1;

DELETE FROM `ps_mbe_ps_14_range_weight` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_14_range_price` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_14_carrier_tax_rules_group_shop` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_14_carrier_shop` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_14_carrier_lang` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_14_carrier_group` WHERE `id_carrier` > 2;



----------------------------------------------------------------
-- PS 1.6
----------------------------------------------------------------
DELETE FROM `ps_mbe_ps_16_carrier` WHERE `id_carrier` > 2;

ALTER TABLE `ps_mbe_ps_16_carrier` AUTO_INCREMENT = 1;

DELETE FROM `ps_mbe_ps_16_range_weight` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_16_range_price` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_16_carrier_tax_rules_group_shop` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_16_carrier_shop` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_16_carrier_lang` WHERE `id_carrier` > 2;

DELETE FROM `ps_mbe_ps_16_carrier_group` WHERE `id_carrier` > 2;