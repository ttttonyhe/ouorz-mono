// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity ^0.8.18;

import { DataTypes } from "../libraries/DataTypes.sol";

interface IBlog {
	function updateBlogTitle(string memory title_) external;

	function updateBlogDescription(string memory description_) external;

	function createCategory(uint256 id_, string memory name_, string memory description_) external;

	function addEntryToCategory(uint256 categoryId, uint256 entryId) external;

	function listCategories() external view returns (DataTypes.CategoryDetail[] memory);

	function getCategoryIds() external view returns (uint256[] memory);

	function getCategoryDetail(
		uint256 categoryId
	) external view returns (DataTypes.CategoryDetail memory);

	function getCategoryEntryIds(uint256 categoryId) external view returns (uint256[] memory);
}
