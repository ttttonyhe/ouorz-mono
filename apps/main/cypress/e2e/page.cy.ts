/// <reference types="cypress" />

describe("Page", function () {
	it("should display page content", function () {
		cy.visit("/page/765")
		cy.get('[data-cy="pageContent"]').should(($e) => {
			expect($e.first()).to.contain("AMA")
		})
	})
})
