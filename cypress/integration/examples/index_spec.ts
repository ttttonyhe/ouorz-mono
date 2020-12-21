/// <reference types="cypress" />
/// <reference types="@testing-library/cypress" />

describe('A sample test', function () {
  it('contains the content "Next"', function () {
    cy.visit('/')
    cy.contains('Next')
  })
})
