// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.30;

import "@openzeppelin/contracts/utils/structs/EnumerableSet.sol";
import "@openzeppelin/contracts-upgradeable/proxy/utils/Initializable.sol";
import "@openzeppelin/contracts-upgradeable/access/OwnableUpgradeable.sol";

import { ITwilightBlog } from "./interfaces/ITwilightBlog.sol";
import { Events } from "./libraries/Events.sol";
import { DataTypes } from "./libraries/DataTypes.sol";
import { Constants } from "./libraries/Constants.sol";
import { CategoryAlreadyExists, CategoryDoesNotExist, InvalidRange } from "./libraries/Errors.sol";

/// @title TwilightBlog
/// @author Tony (Lipeng) He <lipeng.he@uwaterloo.ca>
/// @notice Blog metadata and the categories its entries are filed under.
/// @dev Deployed behind a transparent proxy; the author is the proxy's owner.
contract TwilightBlog is ITwilightBlog, Initializable, OwnableUpgradeable {
	using EnumerableSet for EnumerableSet.UintSet;

	/// @notice Location of the blog's metadata.
	string public blogUri;

	EnumerableSet.UintSet private _categoryIds;
	mapping(uint256 => DataTypes.CategoryMetadata) private _categoryIdToMetadata;
	mapping(uint256 => EnumerableSet.UintSet) private _categoryIdToEntryIds;

	modifier categoryExists(uint256 categoryId) {
		if (!_categoryIds.contains(categoryId)) revert CategoryDoesNotExist();
		_;
	}

	modifier categoryDoesNotExist(uint256 categoryId) {
		if (_categoryIds.contains(categoryId)) revert CategoryAlreadyExists();
		_;
	}

	modifier validRange(uint256 start, uint256 end) {
		if (end < start || end - start > Constants.MAX_BATCH_SIZE) revert InvalidRange();
		_;
	}

	/// @dev Locks the implementation so it can only ever be initialized through a proxy.
	/// @custom:oz-upgrades-unsafe-allow constructor
	constructor() {
		_disableInitializers();
	}

	/// @inheritdoc ITwilightBlog
	function createCategory(
		uint256 id_,
		string calldata name_,
		string calldata description_
	) external override onlyOwner categoryDoesNotExist(id_) {
		_categoryIds.add(id_);
		_categoryIdToMetadata[id_] = DataTypes.CategoryMetadata({
			id: id_,
			name: name_,
			description: description_
		});

		emit Events.CategoryCreated(id_);
	}

	/// @inheritdoc ITwilightBlog
	function addEntryToCategory(
		uint256 categoryId,
		uint256 entryId
	) external override onlyOwner categoryExists(categoryId) {
		_categoryIdToEntryIds[categoryId].add(entryId);
	}

	/// @inheritdoc ITwilightBlog
	function updateBlogUri(string calldata uri_) external override onlyOwner {
		blogUri = uri_;

		emit Events.BlogUriUpdated(uri_);
	}

	/// @inheritdoc ITwilightBlog
	function updateCategoryName(
		uint256 categoryId,
		string calldata name_
	) external override onlyOwner categoryExists(categoryId) {
		_categoryIdToMetadata[categoryId].name = name_;

		emit Events.CategoryUpdated(categoryId);
	}

	/// @inheritdoc ITwilightBlog
	function deleteCategory(
		uint256 categoryId
	) external override onlyOwner categoryExists(categoryId) returns (bool) {
		EnumerableSet.UintSet storage entryIds = _categoryIdToEntryIds[categoryId];
		uint256 length = entryIds.length();
		if (length > Constants.MAX_BATCH_SIZE) {
			for (uint256 i = 0; i < Constants.MAX_BATCH_SIZE; ++i) {
				entryIds.remove(entryIds.at(length - i - 1));
			}
			return false;
		}

		entryIds.clear();
		_categoryIds.remove(categoryId);
		delete _categoryIdToMetadata[categoryId];

		emit Events.CategoryDeleted(categoryId);
		return true;
	}

	/// @inheritdoc ITwilightBlog
	function author() external view override returns (address) {
		return owner();
	}

	/// @inheritdoc ITwilightBlog
	function categories(
		uint256 start,
		uint256 end
	) external view override returns (DataTypes.CategoryMetadata[] memory) {
		uint256[] memory categoryIds = getCategoryIds(start, end);
		uint256 categoryCount = categoryIds.length;

		DataTypes.CategoryMetadata[] memory categoryDetails = new DataTypes.CategoryMetadata[](
			categoryCount
		);
		for (uint256 i = 0; i < categoryCount; ++i) {
			categoryDetails[i] = getCategoryDetail(categoryIds[i]);
		}

		return categoryDetails;
	}

	/// @inheritdoc ITwilightBlog
	function entries(
		uint256 categoryId,
		uint256 start,
		uint256 end
	) external view override returns (uint256[] memory) {
		return getCategoryEntryIds(categoryId, start, end);
	}

	/// @inheritdoc ITwilightBlog
	function initialize(string memory uri_) public override initializer {
		__Ownable_init(msg.sender);

		blogUri = uri_;
		_categoryIds.add(Constants.DEFAULT_CATEGORY_ID);
		_categoryIdToMetadata[Constants.DEFAULT_CATEGORY_ID] = DataTypes.CategoryMetadata({
			id: Constants.DEFAULT_CATEGORY_ID,
			name: Constants.DEFAULT_CATEGORY_NAME,
			description: Constants.DEFAULT_CATEGORY_DESCRIPTION
		});
	}

	/// @inheritdoc ITwilightBlog
	function getCategoryIds(
		uint256 start,
		uint256 end
	) public view override validRange(start, end) returns (uint256[] memory) {
		return _categoryIds.values(start, end);
	}

	/// @inheritdoc ITwilightBlog
	function getCategoryDetail(
		uint256 categoryId
	) public view override categoryExists(categoryId) returns (DataTypes.CategoryMetadata memory) {
		return _categoryIdToMetadata[categoryId];
	}

	/// @inheritdoc ITwilightBlog
	function getCategoryEntryIds(
		uint256 categoryId,
		uint256 start,
		uint256 end
	)
		public
		view
		override
		categoryExists(categoryId)
		validRange(start, end)
		returns (uint256[] memory)
	{
		return _categoryIdToEntryIds[categoryId].values(start, end);
	}
}
