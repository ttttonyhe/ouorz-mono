// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.18;

import { DataTypes } from "../libraries/DataTypes.sol";

interface ITwilightBlog {
	function initialize(string memory uri_) external;

	// CREATE
	function createCategory(uint256 id_, string memory name_, string memory description_) external;

	function addEntryToCategory(uint256 categoryId, uint256 entryId) external;

	// UPDATE
	function updateBlogUri(string memory uri_) external;

	function updateCategoryName(uint256 categoryId, string memory name_) external;

	// DELETE
	function deleteCategory(uint256 categoryId) external;

	// READ
	function author() external view returns (address);

	function categories() external view returns (DataTypes.CategoryMetadata[] memory);

	function entries(uint256 categoryId) external view returns (uint256[] memory);

	// Public functions
	function getCategoryIds() external view returns (uint256[] memory);

	function getCategoryDetail(
		uint256 categoryId
	) external view returns (DataTypes.CategoryMetadata memory);

	function getCategoryEntryIds(uint256 categoryId) external view returns (uint256[] memory);
}
