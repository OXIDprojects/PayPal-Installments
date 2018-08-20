SET @PAYMENT_ID = 'paypinstallments';

DELETE FROM oxpayments WHERE oxid = @PAYMENT_ID COLLATE 'latin1_swedish_ci';
DELETE FROM oxobject2group WHERE oxobjectid = @PAYMENT_ID COLLATE  'latin1_swedish_ci';
DELETE FROM oxobject2payment where oxpaymentid = @PAYMENT_ID COLLATE  'latin1_swedish_ci';
