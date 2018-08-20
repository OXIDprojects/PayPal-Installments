<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

require_once dirname(__FILE__) . "/../../../../../vendor/phpunit/phpunit/PHPUnit/Framework/Assert/Functions.php";

/**
 * Defines application features from the specific context.
 */
class DefaultContext extends Behat\MinkExtension\Context\MinkContext implements SnippetAcceptingContext
{

    protected $_sShopBaseUrl;
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct($parameters)
    {
        $this->_sShopBaseUrl = $parameters['shop_base_url'];
        // TODO setup fixtures before the suite
    }

    /** @BeforeScenario */
    public function beforeScenario()
    {
        $this->iAmLoggedInAsUserWithPassword("management@management.com", "management");
        $this->clearCart();
        $this->logOut();
        $this->iAmLoggedInAsUserWithPassword("test@testing.com", "testing");
        $this->clearCart();
        $this->logOut();
    }

    /**
     * @Given I am logged in as user :user with password :password
     * @When  I log in as user :user with password :password
     */
    public function iAmLoggedInAsUserWithPassword($sUser, $sPassword)
    {
        $oSession = $this->getSession();
        $oSession->visit("{$this->_sShopBaseUrl}");

        $oPage = $oSession->getPage();
        $oLoginButton = $oPage->find("css", "a#loginBoxOpener");
        $oLoginButton->click();

        $oLoginEmailField = $oPage->find("css", "#loginEmail");
        $oLoginEmailField->setValue($sUser);

        $oPasswordField = $oPage->find("css", "#loginPasword");
        $oPasswordField->setValue($sPassword);

        $oFormSubmitButton = $oPage->find("css", ".loginForm .submitButton");
        $oFormSubmitButton->click();
    }

    /**
     * @When I go to the checkout step :arg1 page
     */
    public function iGoToTheCheckoutStepPage($sCheckoutStep)
    {
        $sPageUrl = $this->getCheckoutStepUrl($sCheckoutStep);
        $oSession = $this->getSession();
        $oSession->visit($sPageUrl);
    }

    private function getCheckoutStepUrl($sCheckoutStep)
    {
        $sPageUrl = "{$this->_sShopBaseUrl}/index.php?";
        switch ($sCheckoutStep) {
            case "1":
                $sPageUrl .= "cl=basket";
                break;
            case "2":
                $sPageUrl .= "cl=user";
                break;
            case "3":
                $sPageUrl .= "cl=payment";
                break;
        }

        return $sPageUrl;
    }

    /**
     * @Given I have some articles with total value over :amount euro in my cart
     */
    public function iHaveSomeArticlesWithTotalValueOverEuroInMyCart($amount)
    {
        $oSession = $this->getSession();
        /** Open category page here. This way the SEO URL used below gets created on a virgin demo shop installation */
        $oSession->visit("{$this->_sShopBaseUrl}/en/Kiteboarding/Kites");
        $oPage = $oSession->getPage();
        $oSession->visit("{$this->_sShopBaseUrl}/en/Kiteboarding/Kites/Kite-CORE-GTS.html");
        $oPage = $oSession->getPage();

        //calculate how many times we need to add this article to cart to be above $amount in total value
        $sPrice = $oPage->find('css', '#productPrice strong span')->getText();
        $sPrice = substr($sPrice, 0, -4);
        $fPrice = floatval(str_replace(',', '.', $sPrice));

        if ($fPrice >= $amount)
            $iQuantity = 1;
        else
            $iQuantity = intval($amount / $fPrice) + 1;

        $oPage->find('css', '#amountToBasket')->setValue($iQuantity);

        $oAddToCardButton = $oPage->findById('toBasket');
        $oAddToCardButton->click();
    }

    /**
     * @Then I want to see the paypal installments payment option
     */
    public function iWantToSeeThePaypalInstallmentsPaymentOption()
    {
        $this->assertElementOnPage('#payment_paPayPal_installments');
    }

    /**
     * @Given I have some some articles in cart, whose total value is too low for pay pal installments
     */
    public function iHaveSomeSomeArticlesInCartWhoseTotalValueIsTooLowForPayPalInstallments()
    {
        $oSession = $this->getSession();
        /** Open category page here. This way the SEO URL used below gets created on a virgin demo shop installation */
        $oSession->visit("{$this->_sShopBaseUrl}/en/Special-Offers/");
        $oPage = $oSession->getPage();
        $oSession->visit("{$this->_sShopBaseUrl}/en/Special-Offers/Sticker-set-MIX.html");
        $oPage = $oSession->getPage();
        $oAddToCardButton = $oPage->findById('toBasket');
        $oAddToCardButton->click();
    }

    /**
     * @Then I do not want to see the paypal installments payment option
     */
    public function iDoNotWantToSeeThePaypalInstallmentsPaymentOption()
    {
        $this->assertElementNotOnPage('#payment_paPayPal_installments');
    }

    /**
     * clears the cart of all previous articles
     */
    private function clearCart()
    {
        $oSession = $this->getSession();
        $oSession->visit("{$this->_sShopBaseUrl}/en/cart/");
        if (!$oSession->getPage()->has("css", "div.status.corners.error")) {
            $oCartPage = $oSession->getPage();
            $aQuantityInputs = $oCartPage->findAll("css", "td.quantity p input");
            foreach ($aQuantityInputs as $oQuantityInput) {
                $oQuantityInput->setValue("0");
            }
            $oCartPage->find("css", "button#basketUpdate")->click();
        }
    }

    /**
     * log out the current user
     */
    private function logOut()
    {
        $oSession = $this->getSession();
        if ($oSession->getPage()->has("css", "a#logoutLink"))
            $oSession->getPage()->find("css", "a#logoutLink")->click();
    }

}
