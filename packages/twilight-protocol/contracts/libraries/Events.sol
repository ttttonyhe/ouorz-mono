// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.30;

/// @title Events
/// @author Tony (Lipeng) He <lipeng.he@uwaterloo.ca>
/// @notice Log signatures emitted by the Twilight Blog contracts.
library Events {
	/// @notice Emitted when the author adds a category to the blog.
	/// @param id Identifier the author assigned to the new category.
	event CategoryCreated(uint256 indexed id);

	/// @notice Emitted when the author points the blog at a new metadata URI.
	/// @param uri The URI the blog now resolves to.
	event BlogUriUpdated(string uri);

	/// @notice Emitted when a category's metadata changes.
	/// @param id Identifier of the category that changed.
	event CategoryUpdated(uint256 indexed id);

	/// @notice Emitted when the author removes a category and its entry list.
	/// @param id Identifier of the removed category.
	event CategoryDeleted(uint256 indexed id);
}
