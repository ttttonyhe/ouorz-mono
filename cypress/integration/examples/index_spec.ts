/// <reference types="cypress" />
/// <reference types="@testing-library/cypress" />

describe('Index', function () {
  it('should fetch some posts', function () {
    cy.visit('/')
    cy.get('[data-cy="postList"]').should('be.visible')
  })
})
