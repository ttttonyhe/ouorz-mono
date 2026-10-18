// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.30;

/// @title Constants
/// @author Tony (Lipeng) He <lipeng.he@uwaterloo.ca>
/// @notice The category every blog starts with.
library Constants {
	/// @notice Maximum entries removed or identifiers returned per call.
	uint256 internal constant MAX_BATCH_SIZE = 256;

	/// @notice Identifier of the category created during initialization.
	uint256 public constant DEFAULT_CATEGORY_ID = 1;

	/// @notice Display name of the default category.
	string public constant DEFAULT_CATEGORY_NAME = "Uncategorized";

	/// @notice Description of the default category.
	string public constant DEFAULT_CATEGORY_DESCRIPTION = "Default category";
}
