@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I sort by "ID" value descending

  Scenario: Correctly counts the number of products impacted by the mass edit action when a user selects one product.
    Given I select rows Bag
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "1 product"

  Scenario: Correctly counts the number of products impacted by the mass edit action when a user selects one product model.
    Given I select rows apollon
    When I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "13 products"

  Scenario: Correctly counts the number of products impacted by the mass edit action when a user selects one product model and one product.
    Given I select rows apollon, aphrodite
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "18 products"

  Scenario: Correctly counts the number of products impacted by the mass edit action when a user selects two product models and two products.
    Given I select rows apollon, aphrodite, Hat, Scarf
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "20 products"

  Scenario: Correctly counts the number of products impacted by the mass edit action when a user selects all products
    Given I select rows aphrodite
    And I select all entities
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "242 products"

  Scenario: Correctly counts the number of products impacted by the mass edit action when a user selects all products except one product and model and one product
    Given I select rows aphrodite
    And I select all entities
    And I unselect rows Bag, aphrodite
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "236 products"

  Scenario: Correctly counts the number of products impacted by the mass edit action when a user selects all visible products
    Given I select rows aphrodite
    And I select all visible entities
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "117 products"

  Scenario: Correctly counts the number of products impacted by the mass edit action when a user selects all visible and one filter
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    And I select rows model-tshirt-divided-crimson-red
    And I select all visible entities
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "12 products"
