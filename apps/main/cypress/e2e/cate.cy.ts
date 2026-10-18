/// <reference types="cypress" />

describe("Cate", function () {
	it("should display some posts", function () {
		cy.visit("/cate/6")
		cy.get('[data-cy="cateName"]').should("not.be.empty")
	})
})
