/// <reference types="cypress" />

describe('Dashboard', function () {
  it('should display some blocks', function () {
    cy.visit('/dashboard')
    cy.get('[data-cy="metricCards"]').children().should('not.be.empty')
  })
})
