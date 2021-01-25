/// <reference types="cypress" />
/// <reference types="@testing-library/cypress" />

describe('Sponsor', function () {
  it('should display some sponsors', function () {
    cy.visit('/sponsor')
    cy.get('[data-cy="sponsorsItems"]').children().should('not.be.empty')
  })
})
