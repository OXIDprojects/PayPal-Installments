DROP TABLE IF EXISTS `paypinstallmentspayments`;

DROP TABLE IF EXISTS `paypinstallmentsrefunds`;

DELETE FROM oxconfig WHERE OXMODULE = 'module:paypinstallments';

DELETE FROM oxconfigdisplay WHERE OXCFGMODULE = 'module:paypinstallments';

DELETE FROM oxpayments WHERE OXID = 'paypinstallments';

DELETE FROM oxobject2payment WHERE OXPAYMENTID = 'oxpaymentid';

DELETE FROM oxobject2group WHERE OXOBJECTID = 'oxpaymentid';

DELETE FROM oxobject2payment WHERE OXPAYMENTID = 'oxpaymentid';

DELETE FROM oxcontents WHERE OXLOADID LIKE 'paypinstallments%';

ALTER TABLE oxorder DROP COLUMN `PAYPINSTALLMENTS_FINANCINGFEE`;