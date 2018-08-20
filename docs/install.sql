CREATE TABLE IF NOT EXISTS `paypinstallmentspayments` (
  `OXID` CHAR(32) NOT NULL COMMENT 'Primary key' COLLATE 'latin1_general_ci',
  `OXORDERID` CHAR(32) NOT NULL COMMENT 'OrderID as set in oxorder.OXID' COLLATE 'latin1_general_ci',
  `TRANSACTIONID` VARCHAR(255) NOT NULL COMMENT 'PayPal Transaction ID',
  `STATUS` VARCHAR(255) NOT NULL COMMENT 'PayPal transaction status',
  `FINANCINGFEEAMOUNT` DOUBLE UNSIGNED NOT NULL COMMENT 'Financing fee amount',
  `FINANCINGFEECURRENCY` CHAR(3) NOT NULL COMMENT 'Financing fee currency' COLLATE 'latin1_general_ci',
  `FINANCINGTOTALCOSTAMOUNT` DOUBLE UNSIGNED NOT NULL COMMENT 'Financing total costs amount',
  `FINANCINGTOTALCOSTCURRENCY` CHAR(3) NOT NULL COMMENT 'Financing total costs currency' COLLATE 'latin1_general_ci',
  `FINANCINGTERM` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Financing term. This is the number of monthly payments',
  `FINANCINGMONTHLYPAYMENTAMOUNT` DOUBLE UNSIGNED NOT NULL COMMENT 'Financing monthly payments amount',
  `FINANCINGMONTHLYPAYMENTCURRENCY` CHAR(3) NOT NULL COMMENT 'Financing monthly payments currency' COLLATE 'latin1_general_ci',
  `RESPONSE` TEXT NOT NULL COMMENT 'Serialized response',
  `DATETIME_CREATED` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The datetime this entry is created in the database.',
  PRIMARY KEY (`OXID`),
  UNIQUE INDEX `UNIQ_OXORDERID` (`OXORDERID`),
  UNIQUE INDEX `UNIQ_TRANSACTIONID` (`TRANSACTIONID`)
)
COMMENT='Holds PayPal installments payment data'
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `paypinstallmentsrefunds` (
  `OXID` CHAR(32) NOT NULL COMMENT 'Primary key' COLLATE 'latin1_general_ci',
  `TRANSACTIONID` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'PayPal Transaction ID. The transaction which is related to this refund' COLLATE 'latin1_general_ci',
  `REFUNDID` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'PayPal Refund ID. Unique transaction ID of the refund',
  `MEMO` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'PayPal refund memo',
  `AMOUNT` DOUBLE NOT NULL DEFAULT '0' COMMENT 'Refunded amount of THIS refund',
  `CURRENCY` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'Refund currency',
  `STATUS` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'PayPal refund status',
  `RESPONSE` BLOB NOT NULL COMMENT 'Serialized response',
  `DATETIME_CREATED` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT  'The datetime this entry is created in the  database.',
  PRIMARY KEY (`OXID`),
  UNIQUE INDEX `UNIQ_TRANSACTION_REFUND` (`REFUNDID`, `TRANSACTIONID`),
  INDEX `IDX_TRANSACTIONID` (`TRANSACTIONID`)
)
COMMENT='Holds PayPal installments refund data'
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB;

ALTER TABLE `oxorder`	ADD COLUMN `PAYPINSTALLMENTS_FINANCINGFEE` DOUBLE NOT NULL DEFAULT '0' COMMENT 'PayPal Installments Financing Fee';
