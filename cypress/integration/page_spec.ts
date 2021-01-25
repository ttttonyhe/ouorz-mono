/// <reference types="cypress" />
/// <reference types="@testing-library/cypress" />

describe('Page', function () {
  it('should display page content', function () {
    cy.visit('/page/765')
    cy.get('[data-cy="postContent"]').should(($e) => {
      expect($e.first()).to.contain('AMA')
    })
  })
})
