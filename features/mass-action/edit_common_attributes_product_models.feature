@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Correctly counts the number of products impacted by the mass edit action.
    # Select one product
    Given I select rows apollon
    When I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "13 products"
    # Select one product model
    When I am on the products grid
    And I select rows Bag
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "1 product"
    # Select one product model and one product
    When I am on the products grid
    And I select rows apollon, aphrodite
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "18 products"
    # Select two product models and two products
    When I am on the products grid
    And I select rows apollon, aphrodite, Hat, Scarf
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "20 products"
    # Select all
    When I am on the products grid
    And I select rows aphrodite
    And I select all entities
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "242 products"
    # Select all minus one product model and one product
    When I am on the products grid
    And I select rows aphrodite
    And I select all entities
    And I unselect rows apollon, Bag
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see the text "228 products"

