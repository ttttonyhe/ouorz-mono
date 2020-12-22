/// <reference types="cypress" />
/// <reference types="@testing-library/cypress" />

describe('Index', function () {
  it('should have top section', function () {
    cy.visit('/')
    cy.contains('developer, blogger and undergraduate student')
  })
})
