<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @category      module
 * @package       paypinstallmentsgetexpresscheckoutdetailsparserTest.php
 * @author        OXID Professional services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 */

/**
 * Class paypInstallmentsGetExpressCheckoutDetailsParserTest
 *
 * @desc
 */
class paypInstallmentsGetExpressCheckoutDetailsParserTest extends OxidTestCase
{

    /**
     * @expectedException    paypInstallmentsGetExpressCheckoutDetailsParseException
     * @expectedExceptionMessage MISSING_PAYERID
     *
     * @dataProvider testGetValueByClassAndProperty_exception_dataProvider
     */
    public function testGetValueByClassAndProperty_exception($oResponse)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsGetExpressCheckoutDetailsParser $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsGetExpressCheckoutDetailsParser')
            ->disableOriginalConstructor()
            ->setMethods(array('getResponse'))
            ->getMock();

        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getResponse')
            ->will($this->returnValue($oResponse));
        $oSubjectUnderTest->getPayerId();
    }

    /**
     * @return array array(array($oResponse), ...)
     */
    public function testGetValueByClassAndProperty_exception_dataProvider()
    {
        return array(
            array(
                (object) array(
                    'GetExpressCheckoutDetailsResponseDetails' => (object)array(
                        'PayerInfo' => 'test-pa-not-an-object',
                    ),
                ),
                //'Check for expected object',

            ),

            array(
                (object) array(
                    'GetExpressCheckoutDetailsResponseDetails' => (object)array(
                        'PayerInfo' => (object) array(
                            'PayerIdFake' => 'test-pa-payer-id',
                        ),
                    ),
                ),
                //'Check for expected attribute.'
            ),
            array(
                (object) array(
                    'GetExpressCheckoutDetailsResponseDetails' => (object)array(
                        'PayerInfo' => (object) array(
                            'PayerID' => null,
                        ),
                    ),
                ),
                //'Check expected attribute is set.'
            ),
        );
    }
}
