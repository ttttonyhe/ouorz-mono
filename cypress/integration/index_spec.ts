/// <reference types="cypress" />
/// <reference types="@testing-library/cypress" />

describe('Index', function () {
  it('should display some posts', function () {
    cy.visit('/')
    cy.get('[data-cy="indexPosts"]').children().should('not.be.empty')
  })
})
