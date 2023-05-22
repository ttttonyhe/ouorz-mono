// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.18;

import { DataTypes } from "../libraries/DataTypes.sol";

interface IBlog {
	function initialize(string memory uri_) external;

	function updateBlogUri(string memory uri_) external;

	function createCategory(uint256 id_, string memory name_, string memory description_) external;

	function addEntryToCategory(uint256 categoryId, uint256 entryId) external;

	function author() external view returns (address);

	function categories() external view returns (DataTypes.CategoryDetail[] memory);

	function entries(uint256 categoryId) external view returns (uint256[] memory);

	function getCategoryIds() external view returns (uint256[] memory);

	function getCategoryDetail(
		uint256 categoryId
	) external view returns (DataTypes.CategoryDetail memory);

	function getCategoryEntryIds(uint256 categoryId) external view returns (uint256[] memory);
}
