-- ; nasty comment
# ; nasty comment
/*
this is a nasty ;
multiple-line comment
*/

CREATE TABLE IF NOT EXISTS `paypinstallmentspayments` (
  `OXID` char(32) /* this is ;a nasty in-line comment */ CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT
    'Primary key',
  `OXORDERID` CHAR(32) CHARACTER SET latin1 COLLATE latin1_general_ci COMMENT 'Order id',
  `STATUS` varchar(32) NOT NULL DEFAULT '' COMMENT 'PayPal transaction status',
  `RESPONSE` BLOB NOT NULL COMMENT 'Serialized response',
  `DATETIME_CREATED` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The datetime this entry is created in the  database.',
  PRIMARY KEY (`OXID`),
  UNIQUE INDEX `UNIQ_OXORDERID` (`OXORDERID`)
)
COMMENT='Holds PayPal installments payment data'
ENGINE=InnoDB
;

CREATE /*!57302 TEMPORARY */ TABLE IF NOT EXISTS `paypinstallmentsrefunds` (
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
ENGINE=InnoDB
;