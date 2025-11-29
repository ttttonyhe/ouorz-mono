/// <reference types="cypress" />

describe("Kbar", function () {
        this.beforeEach(() => {
                cy.viewport("macbook-13")
                cy.visit("/")
                cy.get("body").type("{esc}{esc}")
                // wait for kbar component button to appear
                cy.get('[data-cy="cmdkbutton"]').should('be.visible')
        })

	context("enter key combination cmd + k", () => {
		it("should display kbar background and panel", function () {
			cy.get("body").type("{ctrl}k", { release: false })
			cy.wait(500)
			cy.get('[data-cy="kbar-bg"]').should("exist")
			cy.get('[data-cy="kbar-panel"]').should("exist")
		})

		context("search kbar list items", () => {
			it("should display only list items match input value", function () {
				cy.get("body").type("{ctrl}k", { release: false })
				cy.wait(500)
				cy.get('[data-cy="kbar-input"]').type("search blog posts")
				cy.get('[data-cy="tabs-list"]')
					.last()
					.children()
					.should("have.length", 1)
			})
		})

		context("use kbar to search articles", () => {
			it("should list all articles", function () {
				cy.get("body").type("{ctrl}k", { release: false })
				cy.wait(500)
				// use kbar to search articles
				cy.get('[data-cy="kbar-input"]').type("search blog posts")
				// start intercepting the request to get search results
				cy.intercept("POST", "**/api/search", {
					fixture: "searchIndex.json",
				}).as("requestSearchIndex")
				cy.get('[data-cy="tabs-list"]').last().click()
				// wait for request to be finished
				cy.wait("@requestSearchIndex")
				// empty input value
				cy.get('[data-cy="kbar-input"]').clear()
				// display search results
				cy.get('[data-cy="tabs-list"]')
					.last()
					.children()
					.first()
					.contains("Title 1")
			})
		})
	})

	context("click on cmd + k button", () => {
		it("should display kbar background and panel", function () {
			cy.get('[data-cy="cmdkbutton"]').focus()
			cy.wait(500)
			cy.get('[data-cy="kbar-bg"]').should("exist")
			cy.get('[data-cy="kbar-panel"]').should("exist")
		})
	})
})
