/// <reference types="cypress" />

describe('Cate', function () {
  it('should display some posts', function () {
    cy.visit('/category/7')
    cy.get('[data-cy="cateName"]').should('not.be.empty')
  })
})
