// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.30;

/// @title Constants
/// @author Tony (Lipeng) He <lipeng.he@uwaterloo.ca>
/// @notice The category every blog starts with, so an entry always has somewhere to live.
library Constants {
	/// @notice Identifier of the category created during initialization.
	uint256 public constant DEFAULT_CATEGORY_ID = 1;

	/// @notice Display name of the default category.
	string public constant DEFAULT_CATEGORY_NAME = "Uncategorized";

	/// @notice Description of the default category.
	string public constant DEFAULT_CATEGORY_DESCRIPTION = "Default category";
}
