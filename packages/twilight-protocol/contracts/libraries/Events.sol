// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.30;

/// @title Events
/// @author Tony (Lipeng) He <lipeng.he@uwaterloo.ca>
/// @notice Log signatures emitted by the Twilight Blog contracts.
library Events {
	// These events are already deployed. Indexing an argument moves it from the log
	// data into topic 1, so existing consumers could no longer decode new logs and
	// new consumers could not decode historical ones.
	// solhint-disable gas-indexed-events

	/// @notice Emitted when the author adds a category to the blog.
	/// @param id Identifier the author assigned to the new category.
	event CategoryCreated(uint256 id);

	/// @notice Emitted when a blog entry is minted as an NFT.
	/// @param tokenId Token identifier of the minted entry.
	event EntryMinted(uint256 tokenId);

	/// @notice Emitted when the author points the blog at a new metadata URI.
	/// @param uri The URI the blog now resolves to.
	event BlogUriUpdated(string uri);

	/// @notice Emitted when a category's metadata changes.
	/// @param id Identifier of the category that changed.
	event CategoryUpdated(uint256 id);

	/// @notice Emitted when the author removes a category and its entry list.
	/// @param id Identifier of the removed category.
	event CategoryDeleted(uint256 id);
}
