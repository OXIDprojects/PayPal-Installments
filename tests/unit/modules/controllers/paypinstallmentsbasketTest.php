<?php
/**
 * Created by PhpStorm.
 * User: Robert Blank
 * Date: 2015-09-29
 * Time: 12:03
 */
class paypInstallments_basketTest extends OxidTestCase
{

    /**
     * System under test
     *
     * @var $_SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsBasket
     */
    protected $_SUT;

    protected function setUp()
    {
        parent::setUp();

        $this->_SUT = $this->getMockBuilder('paypInstallmentsBasket')
            ->setMethods(
                array(
                      '__construct',
                      '_paypInstallments_CallInitParent',
                )
            )
            ->getMock();
    }

    public function testInit_callsParentMethod()
    {
        $this->_SUT
            ->expects($this->once())
            ->method('_paypInstallments_CallInitParent');

        $this->_SUT->init();
    }


    public function testInit_deletesSessionRegistry()
    {
        /** Arrange: Set the registry and check that it is not null */
        $sRegistryKey = paypInstallmentsOxSession::aPayPalInstallmentsRegistryKey;
        $this->setSessionParam($sRegistryKey, array('test'));
        $aRegistry = $this->getSessionParam($sRegistryKey);
        $this->assertNotNull($aRegistry);

        /** Act */
        $this->_SUT->init();

        /** Assert that registry is deleted */
        $aRegistry = $this->getSessionParam($sRegistryKey);
        $this->assertNull($aRegistry);
    }


    public function testInit_recalculatesBasket()
    {
        /** Arrange  */
        /** @var $SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsBasket */
        $SUT = $this->getMockBuilder('paypInstallmentsBasket')
            ->setMethods(
                array('__call',
                      '__construct',
                      '_paypInstallments_CallInitParent',
                      '_paypInstallments_recalculateBasket',
                )
            )
            ->getMock();

        /** Assert */
        $SUT->expects($this->once())->method('_paypInstallments_recalculateBasket');

        /** Act */
        $SUT->init();
    }
}
