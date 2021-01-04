/// <reference types="cypress" />
/// <reference types="@testing-library/cypress" />

describe('Friends', function () {
  it("should display some friend's sites", function () {
    cy.visit('/friends')
    cy.get('[data-cy="friendsItems"]').children().should('not.be.empty')
  })
})
