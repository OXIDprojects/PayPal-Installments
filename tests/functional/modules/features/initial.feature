Feature: Payment Method
  In order to pay using pay pal installments
  As a website user
  I need to be able to choose pay pal installments on the payment methods selection screen

  Scenario: The right order cost
    Given I am logged in as user "management@management.com" with password "management"
    And I have some articles with total value over 2000 euro in my cart
    When I go to the checkout step 3 page
    Then I want to see the paypal installments payment option

  Scenario: Too cheap order
    Given I am logged in as user "management@management.com" with password "management"
    And I have some some articles in cart, whose total value is too low for pay pal installments
    When I go to the checkout step 3 page
    Then I do not want to see the paypal installments payment option

  Scenario: Outside DE
    Given I am logged in as user "test@testing.com" with password "testing"
    And I have some articles with total value over 2000 euro in my cart
    When I go to the checkout step 3 page
    Then I do not want to see the paypal installments payment option